<?php

namespace App;

class Database
{
    private \PDO $dbh;
    private \PDOStatement $sth;

    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];

        $dsn = "mysql:host=$host;";
        $options = [\PDO::ATTR_PERSISTENT => true];

        try {
            $this->dbh = new \PDO($dsn, $username, $password, $options);
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }

        try {
            $this->dbh->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            $this->dbh->exec("USE `$dbname`");
        } catch (\PDOException $e) {
            throw new \Exception('Create database failed: ' . $e->getMessage());
        }
    }

    public function prepare(string $query): \PDOStatement
    {
        $this->sth = $this->dbh->prepare($query);

        return $this->sth;
    }

    public function bindParam(int|string $param, mixed $value, int $type = null): bool
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;

                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;

                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;

                default:
                    $type = \PDO::PARAM_STR;
                    break;
            }
        }

        return $this->sth->bindParam($param, $value, $type);
    }

    public function execute(): bool
    {
        return $this->sth->execute();
    }

    public function lastInsertId(): string|false
    {
        return $this->dbh->lastInsertId();
    }

    public function fetch(): mixed
    {
        $this->execute();

        return $this->sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function rowCount(): int
    {
        return $this->sth->rowCount();
    }
}
