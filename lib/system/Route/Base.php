<?php

//路由基层
class Route_Base
{
    CONST PATH_CONTROLLER = DS . 'application' . DS . 'controller';
    CONST DEFAULT_INDEX = 'index';

    //路由启动
    public static function on()
    {
        if(!isset($_SERVER['REQUEST_URI'])) {
            return;
        }
        $uri = $_SERVER['REQUEST_URI'];
        //去除get串
        $uri = explode('?', $uri)[0];
        //删除尾部斜杠
        $uri = rtrim($uri, '/');

        //控制器目录
        $directory = APP_PATH . self::PATH_CONTROLLER;

        if(empty($uri)) {
            self::load($directory . DS . self::DEFAULT_INDEX . PHP_EXT, self::DEFAULT_INDEX, self::DEFAULT_INDEX);

            return;
        }

        $path_array = explode('/', $uri);

        $floor = count($path_array);

        //获得方法名，类名
        if($floor == 1) {
            $function = self::DEFAULT_INDEX;
            $class    = $path_array[0];
        } else {
            $function = explode('.', $path_array[$floor - 1]);
            $function = $function[0];
            unset($path_array[$floor - 1]);

            $class = $path_array[$floor - 2];
        }

        //文件名
        $file = $directory . DS . implode(DS, $path_array) . PHP_EXT;

        self::load($file, $class, $function);
    }

    /**
     * 载入控制器并访问方法
     *
     * @param $file
     * @param $class
     * @param $function
     */
    public function load($file, $class, $function)
    {
        if(file_exists($file)) {
            require_once($file);
        } else {
            Exception_Base::error("载入控制器 {$class} 失败：未找到该文件");
        }

        //实例化控制器，访问方法
        $controller = new $class;
        $controller->$function();
    }
}