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
            $this->dbh->exec("set names utf8");
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

    /**
     * 批量插入
     * @param array $fields 属性名
     * @param array $multiValues 值
     */
    public function batchInsert($table, $fields, $multiValues)
    {
        $fieldsStr = implode(",", $fields);
        $fieldsPlaceholder = implode(',', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO $table (" . $fieldsStr . ") VALUES (" . $fieldsPlaceholder . ")";

        $sth = $this->dbh->prepare($sql);

        try {
            $this->beginTransaction();
            foreach ($multiValues as $values) {
                // 失败时，返回false
                $sthExecute = $sth->execute($values);
                if ($sthExecute == false) {
                    throw new Exception('batchInsert fail');
                }
            }

            $this->commit();
        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->rollBack();
        }

        return $this;
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * beginTransaction 事务开始
     */
    public function beginTransaction()
    {
        $this->dbh->beginTransaction();
    }

    /**
     * commit 事务提交
     */
    public function commit()
    {
        $this->dbh->commit();
    }

    /**
     * rollback 事务回滚
     */
    public function rollback()
    {
        $this->dbh->rollback();
    }

    /**
     * fetch
     */
    public function fetch($table, $fields = '*')
    {
        // 非数组转换
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        $sth = $this->dbh->prepare("SELECT $fields FROM $table");
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * fetchAll
     */
    public function fetchAll($table, $fields = '*')
    {
        // 非数组转换
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        $sth = $this->dbh->prepare("SELECT $fields FROM $table");
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function __destruct()
    {
        // 释放连接
        unset($this->dbh);
    }

}
