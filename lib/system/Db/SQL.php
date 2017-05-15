<?php

//用于构造SQL语句
class Db_SQL implements Db_SQLInterface
{
    //数据库操作
    const INSERT = 1;
    const SELECT = 2;
    const UPDATE = 3;
    const DELETE = 4;

    //逻辑符
    const LOGIC_AND = 'AND';
    const LOGIC_OR = 'OR';

    //运算符
    const CO_EQ = '<=>';
    const CO_NEQ = '<>';
    const CO_GT = '>';
    const CO_EGT = '>=';
    const CO_LT = '<';
    const CO_ELT = '<';
    const CO_LIKE = 'LIKE';
    const CO_BETWEEN = 'BETWEEN';
    const CO_NOT_BETWEEN = 'NOT BETWEEN';
    const CO_IN = 'IN';
    const CO_NOT_IN = 'NOT IN';
    const CO_REGEXP = 'REGEXP';

    //排序
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    //单一实例
    private static $_instance = null;

    //表
    private $_table = '';
    //数据库属性
    private $_attribute = array();

    //内置的比较运算符
    private $_whereCo = array(self::CO_EQ, self::CO_NEQ, self::CO_GT, self::CO_EGT, self::CO_LT, self::CO_ELT,
        self::CO_LIKE, self::CO_BETWEEN, self::CO_NOT_BETWEEN, self::CO_IN, self::CO_NOT_IN, self::CO_REGEXP);

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if(self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function setTable($table)
    {
        $this->_table = $table;
    }

    //清除目前保存的所有值
    public function clean()
    {
        $this->_attribute = array();
    }

    public function insert($data, $multiple = false)
    {
        $tmpKey   = array();
        $tmpValue = array();

        foreach($data as $key => $value) {
            $tmpKey[] = "`{$key}`";
            if($multiple) {
                $tmpValue[] = $value;
            } else {
                $tmpValue[] = "'{$value}'";
            }
        }

        $keyStr = '(' . implode(',', $tmpKey) . ')';

        if($multiple) {
            $tmpValueStr = array();
            for($i=0; $i<$multiple; $i++) {
                $partValue = array();
                foreach($tmpValue as $value) {
                    if(isset($value[$i])) {
                        $partValue[] = "'{$value[$i]}'";
                    } else {
                        $partValue[] = '';
                    }
                }
                $tmpValueStr[] = '(' . implode(',', $partValue) . ')';
            }
            $valueStr = implode(',', $tmpValueStr);
        } else {
            $valueStr = '(' . implode(',', $tmpValue) . ')';
        }

        $insertSql = "{$keyStr} VALUES {$valueStr}";

        $sql = "INSERT INTO `{$this->_table}` {$insertSql} ";

        return $sql;
    }

    public function select()
    {
        $attribute = $this->_attribute;
        if(!isset($attribute['field'])) {
            $attribute['field'] = '*';
        }
        if(!isset($attribute['where'])) {
            $attribute['where'] = '';
        }

        $sql = "SELECT {$attribute['field']} FROM `{$this->_table}` {$attribute['where']} ";

        //todo:查看其他属性，order limit group 等

        return $sql;
    }

    public function update($data)
    {
        $updateSql = '';

        foreach($data as $key => $value) {
            $updateSql .= "`{$key}`='{$value}',";
        }
        $updateSql = substr($updateSql, 0, -1);

        $sql = "UPDATE `{$this->_table}` SET {$updateSql} {$this->_attribute['where']} ";

        return $sql;
    }

    public function delete()
    {
        $sql = "DELETE FROM `{$this->_table}` {$this->_attribute['where']} ";

        return $sql;
    }

    /*
    example 1: `id` <=> '1' AND `name` <=> 'Mr.One'
        $where_1 = array(
            'id' => 1,
            'name' => 'Mr.One',
        );

    example 2: `id` > '1' AND `name` <=> 'Mr.One'
        $where_2 = array(
            'id' => array('>', 1),
            'name' => 'Mr.One'
        );

    example 3: （`id` > '1' OR `name` <=> 'Mr.One'） AND `age` > '25'
        $where_3 = array(
            array(
                'id' => array(Db_SQL::CO_GT, 1),
                'name' => 'Mr.One'
            ),
            array(
                'age' => array(Db_SQL::CO_GT, 25)
            )
        );

    example 4: ('id' > '1' OR (`sex` <=> '1' AND `age` > '25')) AND 'name' LIKE 'Mr.%'
        $where_4 = array(
            array(
                '__logic' => Db_SQL:LOGIC_OR,
                'id' => array(Db_SQL::CO_GT, '1'),
                array(
                    'sex' => '1',
                    'age' => array(Db_SQL::CO_GT, 25)
                ),
            ),
            'name' => array(Db_SQL::CO_LIKE, 'Mr.%')
        );
    */
    /**
     * where条件解析
     *
     * @param $where
     *
     * @return bool|string
     */
    public function where($where)
    {
        if(empty($where)) {
            return false;
        }
        $whereSql = 'WHERE ';

        if(is_array($where)) {
            $whereSql .= $this->_parseWhere($where);
        } else {
            $whereSql .= $where;
        }

        $this->_attribute['where'] = $whereSql;
    }

    private function _parseWhere($where)
    {
        $sql = '';

        $logic = self::LOGIC_AND;
        if(isset($where['__logic']) && $where['__logic'] === self::LOGIC_OR) {
            $logic = self::LOGIC_OR;
            unset($where['__logic']);
        }

        foreach($where as $key => $value) {
            if(is_string($key)) {
                //情况 1: 以数据库列名作为索引
                if(strpos($key, '.')) {
                    list($tableAlias, $bindField) = explode('.', $key);
                    $tableAlias .= '.';
                } else {
                    $tableAlias = '';
                    $bindField  = $key;
                }

                if(is_array($value)) {
                    list($exp, $bindValue) = $value;
                    if(in_array($exp, $this->_whereCo)) {

                    } else {
                        $exp = self::CO_EQ;
                    }

                    $tmpSql = "{$tableAlias}`{$bindField}` {$exp} '{$bindValue}'";
                } else {
                    $tmpSql = "{$tableAlias}`{$bindField}` <=> '{$value}'";
                }
            } else {
                //情况 2: 数字索引
                if(is_array($value)) {
                    $tmpSql = '(' . $this->_parseWhere($value) . ')';
                } else {
                    continue;
                }
            }

            $sql .= "{$tmpSql} ";
            if(current($where) !== end($where)) {
                $sql .= "{$logic} ";
            }
        }

        return $sql;
    }

    public function field($fields)
    {
        if(is_array($fields)) {
            $this->_attribute['field'] = implode(',', $fields);
        } elseif(is_string($fields)) {
            $this->_attribute['field'] = $fields;
        } else {
            $this->_attribute['field'] = '*';
        }
    }

    public function order($order)
    {
        if($order == self::ORDER_ASC || $order == self::ORDER_DESC) {
            $this->_attribute['order'] = $order;
        } else {
            $this->_attribute['order'] = self::ORDER_ASC;
        }
    }

    public function group($group)
    {
        $this->_attribute['group'] = $group;
    }

    public function limit($count, $start)
    {
        $this->_attribute['limit'] = "LIMIT {$start},{$count}";
    }
}