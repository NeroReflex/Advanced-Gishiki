<?php
/**************************************************************************
Copyright 2017 Benato Denis

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 *****************************************************************************/

namespace Gishiki\Core\Router;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Services\ErrorHandling;
use Gishiki\Core\Application;

/**
 * This component represents the application as a set of HTTP rules.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Router
{
    /**
     * @var array a list of registered Gishiki\Core\Route ordered my method to allow faster search
     */
    private $routes = [
        RouteInterface::GET => [],
        RouteInterface::POST => [],
        RouteInterface::PUT => [],
        RouteInterface::DELETE => [],
        RouteInterface::HEAD => [],
        RouteInterface::OPTIONS => [],
        RouteInterface::PATCH => []
    ];

    /**
     * Equals to call register, accepts a value.
     *
     * @see Router::register
     *
     * @param RouteInterface $route the route to be added
     */
    public function add(RouteInterface $route)
    {
        return $this->register($route);
    }

    /**
     * Register a route within this router.
     *
     * @param RouteInterface $route the route to be registered
     */
    public function register(RouteInterface &$route)
    {
        //put a reference to the object inside allowed methods for a faster search
        foreach ($route->getMethods() as $method) {
            if ((strcmp($method, RouteInterface::GET) == 0) ||
                (strcmp($method, RouteInterface::POST) == 0) ||
                (strcmp($method, RouteInterface::PUT) == 0) ||
                (strcmp($method, RouteInterface::DELETE) == 0) ||
                (strcmp($method, RouteInterface::HEAD) == 0) ||
                (strcmp($method, RouteInterface::OPTIONS) == 0) ||
                (strcmp($method, RouteInterface::PATCH) == 0)) {
                $this->routes[$method][] = &$route;
            }
        }
    }

    /**
     * Check if the given url and method match a route (even a non-200 OK route is allowed).
     *
     * @param string $method the HTTP used verb
     * @param string $url    the url decoded string of the called url
     * @param array  $params will contains matched url slices
     * @param array  $get    will contains matched url get options
     * @return null|Route the matched route or null
     */
    protected function search($method, $url, array &$params, array &$get)
    {
        foreach ($this->routes[$method] as $currentRoute) {

            //if the current URL matches the current URI
            if ($currentRoute->matches($method, $url, $params, $get)) {

                //this will hold the parameters passed on the URL
                return $currentRoute;
            }
        }

        return null;
    }

    /**
     * Check if the given URL matches a rule in a HTTP method different
     * from the one used to perform the request.
     *
     * @param  string $requestURL    the HTTP used address
     * @param  string $requestMethod the HTTP method used to query the resource
     * @return bool true if the given url is matched in some other methods
     */
    protected function checkNotAllowed($requestURL, $requestMethod) : bool
    {
        $params = [];
        $get = [];

        foreach (array_keys($this->routes) as $method) {
            $matchedRoute = (strcmp($method, $requestMethod) != 0) ?
                $this->search($method, $requestURL, $params, $get) : null;

            if (!is_null($matchedRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load error-handling routes to be used when
     * a bad request is sent to the server.
     *
     * @param string $method the HTTP verb of the request
     * @return array the list of error-handling routes
     */
    protected function loadErrorHandlers($method) : array
    {
        $errorHandlers = [];

        foreach ($this->routes[$method] as &$currentRoute) {
            if (($currentRoute->getStatus() == RouteInterface::NOT_ALLOWED) && (strcmp($currentRoute->getURI(), "") == 0)) {
                $errorHandlers[RouteInterface::NOT_ALLOWED] = $currentRoute;
            }

            if (($currentRoute->getStatus() == RouteInterface::NOT_FOUND) && (strcmp($currentRoute->getURI(), "") == 0)) {
                $errorHandlers[RouteInterface::NOT_FOUND] = $currentRoute;
            }
        }

        //if any error handler was not loaded use the default one
        if (!in_array(RouteInterface::NOT_FOUND, array_keys($errorHandlers))) {
            //load the default 404 NOT FOUND handler
            $errorHandlers[RouteInterface::NOT_FOUND] = new Route([
                "verbs" => [
                    RouteInterface::GET,
                    RouteInterface::DELETE,
                    RouteInterface::PATCH,
                    RouteInterface::OPTIONS,
                    RouteInterface::HEAD,
                    RouteInterface::GET,
                    RouteInterface::PUT,
                    RouteInterface::POST
                ],
                "uri" => "",
                "status" => RouteInterface::NOT_FOUND,
                "controller" => ErrorHandling::class,
                "action" => "notFound",
            ]);
        }

        //if any error handler was not loaded use the default one
        if (!in_array(RouteInterface::NOT_ALLOWED, array_keys($errorHandlers))) {
            //load the default 405 NOT ALLOWED handler
            $errorHandlers[RouteInterface::NOT_ALLOWED] = new Route([
                "verbs" => [
                    RouteInterface::GET,
                    RouteInterface::DELETE,
                    RouteInterface::PATCH,
                    RouteInterface::OPTIONS,
                    RouteInterface::HEAD,
                    RouteInterface::GET,
                    RouteInterface::PUT,
                    RouteInterface::POST
                ],
                "uri" => "",
                "status" => RouteInterface::NOT_ALLOWED,
                "controller" => ErrorHandling::class,
                "action" => "notAllowed",
            ]);
        }

        return $errorHandlers;
    }

    /**
     * Run the router and serve the current request.
     *
     * This function is __CALLED INTERNALLY__ and, therefore
     * it __MUST NOT__ be called by the user!
     *
     * @param RequestInterface  $requestToFulfill the request to be served/fulfilled
     * @param ResponseInterface $response         the response to be filled
     * @param array             $controllerArgs   an associative array with more parameters to be passed to the called controller
     * @param Application|null  $app      the current application instance
     */
    public function run(RequestInterface &$requestToFulfill, ResponseInterface &$response, array $controllerArgs = [], Application $app = null)
    {
        //clone the request
        $request = clone $requestToFulfill;

        $params = [];
        $get = [];

        $matchedRoute = $this->search($request->getMethod(), urldecode($request->getUri()->getPath()), $params, $get);

        if (!is_null($matchedRoute)) {
            //this will hold the parameters passed on the URL
            $deductedParams = new GenericCollection([
                "uri" => $params,
                "get" => $get,
            ]);

            $matchedRoute($request, $response, $deductedParams, $controllerArgs, $app);

            return;
        }

        $errorHandlers = $this->loadErrorHandlers($request->getMethod());

        $emptyDeductedParam = new GenericCollection();

        //check if this is a 404 or a 405
        if ($this->checkNotAllowed(urldecode($request->getUri()->getPath()), $request->getMethod())) {
            //this is a 405 error and the notAllowed route must be followed
            $errorHandlers[RouteInterface::NOT_ALLOWED]($request, $response, $emptyDeductedParam, $controllerArgs);

            return;
        }

        //this is a 404 error and the notFound route must be followed
        $errorHandlers[RouteInterface::NOT_FOUND]($request, $response, $emptyDeductedParam, $controllerArgs);
    }
}
