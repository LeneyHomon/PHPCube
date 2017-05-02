<?php

//Db基层，与数据库直接交互
class Db_Base
{
    /**
     * @var PDO
     */
    private $_db = null;

    private $_dbName;
    private $_dbHost;
    private $_dbPort;
    private $_dbUser;
    private $_dbPwd;

    private $_lastId = 0;
    private $_rowCount = 0;

    /**
     * @var PDOStatement
     */
    private $_stmt;
    private $_error;

    //数据库连接池
    private static $_instances = null;

    private function __construct($dbName, $server)
    {
        $this->_dbName = $dbName;
        $this->_dbHost = $server['host'];
        $this->_dbPort = $server['port'];
        $this->_dbUser = $server['user'];
        $this->_dbPwd  = $server['pwd'];
    }

    //todo:多个数据库终端连接
    public static function getInstance($dbName)
    {
        $server = Config::get('db.mysql');

        if(empty(self::$_instances[$dbName])) {
            self::$_instances[$dbName] = new self($dbName, $server);
        }

        return self::$_instances[$dbName];
    }

    protected function getDb()
    {
        if(empty($this->_db)) {
            $dsn     = "mysql:host={$this->_dbHost};port={$this->_dbPort};dbname={$this->_dbName}";
            $options = array(
                PDO::ATTR_TIMEOUT            => 10,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                PDO::ATTR_AUTOCOMMIT         => 1,
            );
            try {
                $this->_db = new PDO($dsn, $this->_dbUser, $this->_dbPwd, $options);
            } catch(PDOException $e) {
                Exception_Base::error('DB error ' . $e->getMessage());
            }
        }

        return $this->_db;
    }

    private function _prepare($sql)
    {
        $db           = $this->getDb();
        $this->_error = '';
        $stmt         = $db->prepare($sql);
        if($stmt === false) {
            return false;
        }
        $this->_stmt = $stmt;

        return true;
    }

    private function _getStmtError()
    {
        $error = $this->_stmt->errorInfo();
        if(!is_null($error[1])) {
            $this->_error = implode('\t', $error);
        }
    }

    public function bind($bind)
    {
        if(!is_array($bind)) {
            $bind = array();
        }
        foreach($bind as $k => $v) {
            if(is_array($v)) {
                $this->_stmt->bindValue($k, $v['value'], $v['type']);
            } else {
                $this->_stmt->bindValue($k, $v);
            }
        }

        return $this->_stmt->execute();
    }

    public function find($sql, $bind = array(), $fetchStyle = PDO::FETCH_ASSOC)
    {
        if(!$this->_prepare($sql)) {
            return false;
        }
        if(!$this->bind($bind)) {
            $this->_getStmtError();

            return false;
        }
        $data = $this->_stmt->fetch($fetchStyle);
        $this->_getStmtError();

        return $data;
    }

    public function select($sql, $bind = array(), $fetchStyle = PDO::FETCH_ASSOC)
    {
        if(!$this->_prepare($sql)) {
            return false;
        }
        if(!$this->bind($bind)) {
            $this->_getStmtError();

            return false;
        }
        $data = $this->_stmt->fetchAll($fetchStyle);
        $this->_getStmtError();

        return $data;
    }

    public function execute($sql, $bind = array())
    {
        if(!$this->_prepare($sql)) {
            return false;
        }
        if(!$this->bind($bind)) {
            $this->_getStmtError();

            return false;
        }
        $this->_lastId   = (int)$this->_db->lastInsertId();
        $this->_rowCount = (int)$this->_stmt->rowCount();

        return true;
    }

    public function getLastId()
    {
        return $this->_lastId;
    }

    public function getRowCount()
    {
        return $this->_rowCount;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function beginTransaction()
    {
        return $this->_db->beginTransaction();
    }

    public function commit()
    {
        return $this->_db->commit();
    }

    public function rollBack()
    {
        return $this->_db->rollBack();
    }

    public function close()
    {
        if($this->_db) {
            $this->_db = null;
        }
        if($this->_stmt) {
            $this->_stmt = null;
        }
    }
}