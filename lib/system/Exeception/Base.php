<?php

//基础错误处理
class Exception_Base
{
    public function __construct()
    {
    }

    //todo:优化错误处理
    public static function error($info)
    {
        echo '<pre>';
        echo "PHPCube Error: {$info}";
        exit;
    }
}