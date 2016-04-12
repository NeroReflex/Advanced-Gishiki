<?php
/****************************************************************************
  Copyright 2015 Benato Denis

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
********************************************************************************/

namespace Gishiki\Logging {
    
    /**
     * A collection of all possible log priorities
     *
     * Benato Denis <benato.denis96@gmail.com>
     */
    abstract class Priority {
        const EMERGENCY = LOG_EMERG;
        const ALERT = LOG_ALERT;
        const CRITICAL = LOG_CRIT;
        const ERROR = LOG_ERR;
        const WARNING = LOG_WARNING;
        const NOTICE = LOG_NOTICE;
        const INFO = LOG_INFO;
        const DEBUG = LOG_DEBUG;
    }
}