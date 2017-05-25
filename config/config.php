<?php

class Config
{
    private static $_param = array(
        'db' => array(
            'mysql' => array(
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'pwd'  => '',
            ),
        ),
    );

    private function __construct()
    {
    }

    /**
     * 获得配置参数
     *
     * @param $var
     *
     * @return array|bool|mixed
     *
     * 例：Config::get('db.mysql');
     *      将传回$_param['db']['mysql']的值
     */
    public static function get($var)
    {
        if(empty($var)) {
            return false;
        }

        $param = self::$_param;
        $keys  = explode('.', $var);
        foreach($keys as $key) {
            if(isset($param[$key])) {
                $param = $param[$key];
            } else {
                return false;
            }
        }

        return $param;
    }

    /**
     * 增加配置参数
     *
     * @param $array
     */
    public static function add($array)
    {
        if(empty($array)) {
            return ;
        }
        self::$_param = array_merge(self::$_param, $array);
    }
}
