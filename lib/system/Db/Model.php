<?php

//Model基层，组织SQL语句传入到DB_Base中执行
class Db_Model
{
    const INSERT = 1;
    const SELECT = 2;
    const UPDATE = 3;
    const DELETE = 4;

    //比较运算符
    const WHERE_STR = false;
    const WHERE_CO_EQ = '<=>';
    const WHERE_CO_NEQ = '<>';
    const WHERE_CO_GT = '>';
    const WHERE_CO_EGT = '>=';
    const WHERE_CO_LT = '<';
    const WHERE_CO_ELT = '<';
    const WHERE_CO_LIKE = 'LIKE';
    const WHERE_CO_BETWEEN = 'BETWEEN';
    const WHERE_CO_NOT_BETWEEN = 'NOT BETWEEN';
    const WHERE_CO_IN = 'IN';
    const WHERE_CO_NOT_IN = 'NOT IN';
    const WHERE_CO_REGEXP = 'REGEXP';

    //排序
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    //数据库连接实例
    /**
     * @var Db_Base
     */
    private $_db = null;

    //测试模式
    private $_debug = false;

    //最后执行的sql语句
    private $_lastSql = '';

    //插入、更新数据
    private $_data = array();
    //需要查询的数据段
    private $_field = '*';
    //需要查询的表
    private $_table = '';
    //查询条件
    private $_where = '';
    //排序方式
    private $_order = '';
    //数据查询条数
    private $_limit = '';

    //是否只查询单条记录
    private $_one = false;
    //内置的比较运算符
    private static $_whereCo = array(self::WHERE_CO_EQ, self::WHERE_CO_NEQ, self::WHERE_CO_GT, self::WHERE_CO_EGT, self::WHERE_CO_LT, self::WHERE_CO_ELT,
        self::WHERE_CO_LIKE, self::WHERE_CO_BETWEEN, self::WHERE_CO_NOT_BETWEEN, self::WHERE_CO_IN, self::WHERE_CO_NOT_IN, self::WHERE_CO_REGEXP);

    //最后查询id
    private static $_lastId = 0;
    //影响行数
    private static $_rowCount = 0;

    //错误
    private static $_error = '';

    public function __construct($dbName, $table)
    {
        $this->_table = $table;
        $this->_db    = Db_Base::getInstance($dbName);
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
     * 存入需要查询的字段
     *
     * @param $field
     *
     * @return $this
     */
    protected function field($field)
    {
        if(is_array($field)) {
            $this->_field = implode(',', $field);
        } elseif(is_string($field)) {
            $this->_field = $field;
        } else {
            $this->_field = '*';
        }

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
        $this->_parseWhere($where);

        return $this;
    }

    /**
     * 存入需要查询的数据条数
     *
     * @param $start int 开始条数
     * @param $count int 结束条数
     *
     * @return $this
     */
    protected function limit($start, $count)
    {
        $this->_limit = "LIMIT {$start},{$count}";

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
        if($order == self::ORDER_ASC || $order == self::ORDER_DESC) {
            $this->_order = $order;
        } else {
            $this->_order = self::ORDER_ASC;
        }

        return $this;
    }

    /**
     * 执行多次INSERT
     *
     * @return string
     */
    protected function multinsert()
    {
        $datas = $this->_data;
        foreach($datas as $data) {
            $this->_data = $data;
            $this->insert();
        }
    }

    /**
     * 执行INSERT
     *
     * @return string
     */
    protected function insert()
    {
        $this->_parseSql(self::INSERT);

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
        $this->_parseSql(self::SELECT);

        return $this->_query();
    }

    protected function find()
    {
        $this->_parseSql(self::SELECT);
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
        $this->_parseSql(self::UPDATE);

        return $this->_execute();
    }

    /**
     * 执行DELETE查询
     *
     * @return string
     */
    protected function delete()
    {
        $this->_parseSql(self::DELETE);

        return $this->_execute();
    }

    protected function count()
    {
        $this->_field = "COUNT(1) AS C";
        $this->_parseSql(self::SELECT);
        $this->_one = true;

        return (int)$this->_query()['C'];
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
     * 解析操作
     *
     * @param $operate
     */
    private function _parseSql($operate)
    {
        $sql = '';
        switch($operate) {
            case self::INSERT:
                $insert_sql = $this->_parseInsert($this->_data);
                $sql        = "INSERT INTO `{$this->_table}` {$insert_sql}";
                break;
            case self::SELECT:
                $sql = "SELECT {$this->_field} FROM `{$this->_table}` {$this->_where} {$this->_order} {$this->_limit}";
                break;
            case self::UPDATE:
                $update_sql = $this->_parseUpdate($this->_data);
                $sql        = "UPDATE `{$this->_table}` SET {$update_sql} {$this->_where}";
                break;
            case self::DELETE:
                $sql = "DELETE FROM `{$this->_table}` {$this->_where}";
                break;
        }
        $this->_lastSql = $sql;
    }

    /**
     * 解析插入数据，返回SQL语句
     *
     * @param $data
     *
     * @return bool|string
     */
    private function _parseInsert($data)
    {
        if(!$data) {
            return false;
        }
        $tmp_key   = array();
        $tmp_value = array();

        foreach($data as $key => $value) {
            $tmp_key[]   = "`{$key}`";
            $tmp_value[] = "'{$value}'";
        }

        $key_str   = implode(',', $tmp_key);
        $value_str = implode(',', $tmp_value);

        $insert_sql = "({$key_str}) VALUES ({$value_str})";

        return $insert_sql;
    }

    /**
     * @example
     * where(
     */
    /**
     * 解析where，直接赋值到成员变量
     *
     * @param $where
     */
    private function _parseWhere($where)
    {
        if(!$where) {
            return;
        }
        $this->_where = 'WHERE ';
        if(is_array($where)) {
            foreach($where as $field => $value) {
                //解析field是否有表名关联
                if(strpos($field, '.') > 0) {
                    list($tableAlias, $bindField) = explode('.', $field);
                    $tableAlias .= '.';
                } else {
                    $tableAlias = '';
                    $bindField  = $field;
                }

                //解析value
                if(is_array($value)) {
                    list($exp, $bindValue) = $value;
                    if(in_array($exp, self::$_whereCo)) {

                    } elseif($exp === self::WHERE_STR) {
                        $exp = '';
                    } else {
                        $exp = '<=>';
                    }
                    $this->_where .= "{$tableAlias}`{$bindField}` {$exp} '{$bindValue}'";
                } else {
                    $this->_where .= "{$tableAlias}`{$bindField}` <=> '{$value}'";
                }

                $this->_where .= ' AND ';
            }
            $this->_where = substr($this->_where, 0, -5);

        } elseif(is_string($where)) {
            $this->_where .= $where;
        }
    }

    /**
     * 解析更新数据，返回SQL语句
     *
     * @param $data
     *
     * @return bool|string
     */
    private function _parseUpdate($data)
    {
        if(!$data) {
            return false;
        }

        $update_sql = '';

        foreach($data as $key => $value) {
            $update_sql .= "`{$key}`='{$value}',";
        }
        $update_sql = substr($update_sql, 0, -1);

        return $update_sql;
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