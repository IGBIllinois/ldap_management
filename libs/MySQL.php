<?php

class MySQL
{
    const FAIL_SILENTLY = true;

    /** @var PDO $resource */
    private $resource;

    private static $instance = null;

    public function __construct($host, $database, $user, $pass)
    {
        $this->open($host, $database, $user, $pass);
    }

    private function open($host, $database, $user, $pass)
    {
        try {
            $this->resource = new PDO(
                "mysql:host=$host;dbname=$database", $user, $pass, [PDO::ATTR_PERSISTENT => true]
            );
        } catch (PDOException $e) {
            $this->resource = null;
            return false;
        }
        return true;
    }

    public static function init($host, $database, $user, $pass)
    {
        if (self::$instance == null) {
            self::$instance = new self($host, $database, $user, $pass);
        }
        if (self::$instance->resource == null) {
            return false;
        }
        return true;
    }

    public function insert($sql, $args = null)
    {
        if (self::FAIL_SILENTLY && $this->resource == null) {
            return false;
        }
        if ($args == null) {
            $this->resource->exec($sql);
        } else {
            $stmt = $this->resource->prepare($sql);
            $stmt->execute($args);
        }
        return $this->resource->lastInsertId();
    }

    public function select($sql, $args = null)
    {
        if (self::FAIL_SILENTLY && $this->resource == null) {
            return false;
        }
        if ($args == null) {
            $stmt = $this->resource->query($sql);
        } else {
            $stmt = $this->resource->prepare($sql);
            $stmt->execute($args);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectOne($sql, $args = null)
    {
        $results = $this->select($sql, $args);
        if ($results) {
            return $results[0];
        }
        return false;
    }

    public function query($sql, $args = null)
    {
        if (self::FAIL_SILENTLY && $this->resource == null) {
            return false;
        }
        if ($args == null) {
            $stmt = $this->resource->exec($sql);
        } else {
            $stmt = $this->resource->prepare($sql);
            $stmt->execute($args);
        }

        return $stmt;
    }

    public function errorInfo()
    {
        return $this->resource->errorInfo();
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return MySQL|null
     */
    public static function getInstance()
    {
        return self::$instance;
    }


}