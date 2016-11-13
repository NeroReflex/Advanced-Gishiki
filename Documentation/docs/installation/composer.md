# Composer
As promised Gishiki is not meant to replace your favourite tools: you can still use all of them!

Doctrine? Propel? Zend framework components? Symfony components? No problem!

You have to install them and you do that using composer! If you don't have composer run:

```shell
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

Remember that composer is essential to run Gishiki: composer is what loads the
entire framework and manages the framework version!


## Bootstrapping an application
You like digging immediatly into development? No problem!

I have setup a [small application](https://github.com/NeroReflex/gishiki_startup) example you can fork to have something you can
instantly develop on!


## Fresh start
You like reading documentation or you have great experience with Gishiki? Another time... No problem!

You can use composer to start up your new project!
```shell
php composer.phar create-project neroreflex/gishiki path
```

nice and easy! Good work!