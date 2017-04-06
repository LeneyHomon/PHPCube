<?php
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', dirname(__FILE__));
}

if (!defined('PATH_CONF')) {
    define('PATH_CONF', PATH_ROOT . DS . 'config');
}

if (!defined('PATH_LIB')) {
    define('PATH_LIB', PATH_ROOT . DS . 'lib');
}

if (!defined('PATH_SERVICE')) {
    define('PATH_SERVICE', PATH_ROOT . DS . 'service');
}

session_start();

require_once(PATH_CONF . DS . 'config.php');
require_once(PATH_LIB . DS . 'common.php');

//自动加载
spl_autoload_register(
    function ($class) {
        static $libPaths = array();
        //获取使用service的子目录
        if (empty($libPaths)) {
            $libPaths = array(
                PATH_LIB . DS . 'system',
                PATH_LIB . DS . 'extra',
            );
            if (defined('SERVICE_PATH')) {
                $services = explode(';', SERVICE_PATH);
                foreach ($services as $k => $service) {
                    array_push($libPaths, PATH_SERVICE . DS . $service);
                }
            }
        }

        $paths    = explode('_', $class);
        $fileName = array_pop($paths);
        $segments = array(
            '',                     //用于指定库文件所在目录
            implode(DS, $paths),    //需要载入的文件目录
            $fileName . PHP_EXT      //载入的文件名
        );

        foreach ($libPaths as $libPath) {
            $segments[0] = $libPath;

            $path = implode(DS, $segments);
            if (file_exists($path)) {
                require_once($path);

                return;
            }

        }
    }
);

//路由
Route_Base::on();