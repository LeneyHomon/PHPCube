<?php

//Model基层，组织SQL语句传入到DB_Base中执行
class Model_Base
{
    //数据库连接实例
    /**
     * @var Db_Base
     */
    private $_db = null;

    //数据库语句构造器
    private $_sqlHelper = null;

    //测试模式
    private $_debug = false;

    //最后执行的sql语句
    private $_lastSql = '';

    //插入、更新数据
    private $_data = array();
    //需要查询的表
    private $_table = '';

    //是否只查询单条记录
    private $_one = false;

    //最后查询id
    private static $_lastId = 0;
    //影响行数
    private static $_rowCount = 0;

    //错误
    private static $_error = '';

    public function __construct($dbName, $table)
    {
        $this->_table     = $table;
        $this->_db        = Db_Base::getInstance($dbName);
        $this->_sqlHelper = Db_SQL::getInstance();
    }

    /**
     * 开启测试模式，结果只输出sql语句而不执行
     *
     * @return $this
     */
    public function debug()
    {
        $this->_debug = true;

        return $this;
    }

    /**
     * 在需要多表操作时定义表
     *
     * @param $table
     *
     * @return $this
     */
    protected function table($table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * 存入需要查询的字段
     *
     * @param $fields
     *
     * @return $this
     */
    protected function field($fields)
    {
        $this->_sqlHelper->field($fields);

        return $this;
    }

    /**
     * 存入数据
     *
     * @param $data
     *
     * @return $this
     */
    protected function data($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * 存入查询条件
     *
     * @param $where array|string 查询条件
     *
     * @return $this
     */
    protected function where($where)
    {
        $this->_sqlHelper->where($where);

        return $this;
    }

    /**
     * 存入需要查询的数据条数
     *
     * @param $count int 总条数
     * @param $start int 开始条数
     *
     * @return $this
     */
    protected function limit($count, $start = 0)
    {
        $this->_sqlHelper->limit($count, $start);

        return $this;
    }

    /**
     * 排序方式，默认升序
     *
     * @param $order
     *
     * @return $this
     */
    protected function order($order)
    {
        $this->_sqlHelper->order($order);

        return $this;
    }

    /**
     * 执行多次INSERT
     *
     * @return string
     */
    protected function multinsert()
    {
        $data = $this->_data;
        $max = 0;
        //确定批量插入的总条数
        foreach($data as $value) {
            if(count($value) > $max) {
                $max = count($value);
            }
        }
        $this->_sqlHelper->setTable($this->_table);

        $this->_lastSql = $this->_sqlHelper->insert($this->_data, $max);

        if($this->_debug) {
            return $this->_lastSql;
        }

        if($this->_execute()) {
            return (int)self::$_lastId;
        } else {
            return false;
        }
    }

    /**
     * 执行INSERT
     *
     * @return string
     */
    protected function insert()
    {
        $this->_sqlHelper->setTable($this->_table);
        $this->_lastSql = $this->_sqlHelper->insert($this->_data);

        if($this->_debug) {
            return $this->_lastSql;
        }

        if($this->_execute()) {
            return (int)self::$_lastId;
        } else {
            return false;
        }
    }

    /**
     * 执行SELECT查询
     *
     * @return string
     */
    protected function select()
    {
        $this->_sqlHelper->setTable($this->_table);
        $this->_lastSql = $this->_sqlHelper->select();

        return $this->_query();
    }

    protected function find()
    {
        $this->_sqlHelper->setTable($this->_table);
        $this->_lastSql = $this->_sqlHelper->select();
        $this->_one = true;

        return $this->_query();
    }

    /**
     * 执行UPDATE查询
     *
     * @return string
     */
    protected function update()
    {
        $this->_sqlHelper->setTable($this->_table);
        $this->_lastSql = $this->_sqlHelper->update($this->_data);

        return $this->_execute();
    }

    /**
     * 执行DELETE查询
     *
     * @return string
     */
    protected function delete()
    {
        $this->_sqlHelper->setTable($this->_table);
        $this->_lastSql = $this->_sqlHelper->delete();

        return $this->_execute();
    }

    protected function count()
    {
        $this->_sqlHelper->field("COUNT(1) AS C");
        $this->_sqlHelper->setTable($this->_table);
        $this->_lastSql = $this->_sqlHelper->select();
        $this->_one = true;

        if($this->_debug) {
            return $this->_lastSql;
        } else {
            return (int)$this->_query()['C'];
        }
    }

    protected function execute($sql)
    {
        $this->_lastSql = $sql;

        return $this->_execute();
    }

    protected function query($sql)
    {
        $this->_lastSql = $sql;

        return $this->_query();
    }


    /**
     * 执行sql语句
     *
     * @return bool
     */
    private function _execute()
    {
        if($this->_debug) {
            return $this->_lastSql;
        } else {
            $result = $this->_db->execute($this->_lastSql);

            self::$_lastId   = $this->_db->getLastId();
            self::$_rowCount = $this->_db->getRowCount();
            self::$_error    = $this->_db->getError();

            return $result;
        }
    }

    /**
     * 执行sql语句
     *
     * @return array|bool|mixed
     */
    private function _query()
    {
        if($this->_debug) {
            return $this->_lastSql;
        } else {
            if($this->_one) {
                $data = $this->_db->find($this->_lastSql);
            } else {
                $data = $this->_db->select($this->_lastSql);
            }

            self::$_error = $this->_db->getError();

            return $data;
        }
    }

    public function __destruct()
    {
        if($this->_db) {
            $this->_db->close();
            $this->_db = null;
        }
    }
}