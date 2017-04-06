<?php
define('APP_PATH', dirname(__FILE__));
define('APP_VIEW_PATH', APP_PATH . DS . 'application' . DS . 'view');
define('APP_LIB_PATH', APP_PATH . DS . 'application' . DS . 'lib');
define('APP_CONF_PATH', APP_PATH . DS . 'application' . DS . 'config');

//使用到的服务层文件所在目录，多个服务层文件使用分号分隔
define('SERVICE_PATH', 'default');

//载入总引导页
require_once(dirname(__FILE__) . '/../../bootstrap.php');
