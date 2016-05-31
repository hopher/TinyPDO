<?php

/**
 * 简单PDO操作类
 * @author hongjh <565983236@qq.com>
 */
class TinyPDO
{

    protected static $_instance = null;
    public $dsn;
    public $dbh;

    private function __construct($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset, $dbPort)
    {
        try {
            $this->dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName;
            $this->dbh = new PDO($this->dsn, $dbUser, $dbPasswd);
            // 禁用php模拟预处理
            $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->dbh->exec("set names $dbCharset");
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function getInstance($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset = 'utf8', $dbPort = '3306')
    {
        if (self::$_instance === null) {
            self::$_instance = new self($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset, $dbPort);
        }
        return self::$_instance;
    }

    public function insert($table, $data)
    {

        $fields = array_keys($data);
        $fieldsValues = array_values($data);

        $fieldsStr = implode(",", $fields);
        $fieldsPlaceholder = implode(',', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO $table (" . $fieldsStr . ") VALUES (" . $fieldsPlaceholder . ")";

        $sth = $this->dbh->prepare($sql);
        return $sth->execute($fieldsValues);
    }

    public function __destruct()
    {
        // 释放连接
        unset($this->dbh);
    }

    public function __call($functionName, $args)
    {
        return call_user_func_array(array($this->dbh, $functionName), $args);
    }

    public function getOne($sql, $args = array())
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($args);
        $sth->setFetchMode(PDO::FETCH_NUM);
        $rs = $sth->fetch();
        return $rs[0];
    }
    
    public function getRow($sql, $args = array())
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($args);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $rs = $sth->fetch();
        return $rs;        
    }
    
    public function getAll($sql, $args = array())
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($args);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $rs = $sth->fetchAll();
        return $rs;
    }

}
