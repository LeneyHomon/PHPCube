<?php
class Cache_Redis
{
    private $_redis;

    private static $_instance = null;


    private function __construct($host, $port)
    {
        $this->_redis = new Redis();
        $this->_redis->connect($host, $port);
    }

    public static function getInstance()
    {
        $config = Config::get('redis');
        if(empty(self::$_instance)) {
            self::$_instance = new self($config['host'], $config['port']);
        }
        return self::$_instance;
    }

    /**
     * 外部设置缓存接口
     *
     * @param     $key
     * @param     $value
     * @param int $timeout
     *
     * @return bool
     */
    public static function sSet($key, $value, $timeout = 0)
    {
        $instance = self::getInstance();
        return $instance->_set($key, $value, $timeout);
    }

    /**
     * 外部获得缓存接口
     *
     * @param $key
     *
     * @return bool|string
     */
    public static function sGet($key)
    {
        $instance = self::getInstance();
        return $instance->_get($key);
    }

    /**
     * 外部删除缓存接口
     *
     * @param $key
     *
     * @return bool|int
     */
    public static function sDelete($key)
    {
        $instance = self::getInstance();
        return $instance->_delete($key);
    }

    private function _set($key, $value, $timeout)
    {
        if(empty($this->_redis)) {
            return false;
        }

        $value = json_encode($value);
        $result = $this->_redis->set(md5($key), $value, $timeout);
        return $result;
    }

    private function _get($key)
    {
        if(empty($this->_redis)) {
            return false;
        }

        $data = $this->_redis->get(md5($key));
        return $data;
    }

    private function _delete($key)
    {

        if(empty($this->_redis)) {
            return false;
        }

        $result = $this->_redis->del(md5($key));
        return $result;
    }
}