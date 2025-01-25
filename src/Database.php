<?php

namespace App;

/**
 * The Database class.
 * 
 * This class handles database operations.
 */
class Database
{
    /**
     * The database connection instance.
     * 
     * @var \PDO
     */
    private \PDO $dbh;

    /**
     * The statement instance.
     * 
     * @var \PDOStatement
     */
    private \PDOStatement $sth;

    /**
     * Creates a new Database instance.
     */
    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];

        $dsn = "mysql:host=$host;";
        $options = [\PDO::ATTR_PERSISTENT => true];

        // Create the database connection.
        try {
            $this->dbh = new \PDO($dsn, $username, $password, $options);
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }

        // Create the database if it doesn't exist.
        try {
            $this->dbh->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            $this->dbh->exec("USE `$dbname`");
        } catch (\PDOException $e) {
            throw new \Exception('Create database failed: ' . $e->getMessage());
        }
    }

    /**
     * Executes a query.
     * 
     * @param string $query The query to execute.
     * 
     * @return int|false The number of affected rows or false on failure.
     */
    public function exec(string $query): int|false
    {
        return $this->dbh->exec($query);
    }

    /**
     * Prepares a query.
     * 
     * @param string $query The query to prepare.
     * 
     * @return \PDOStatement The prepared statement.
     */
    public function prepare(string $query): \PDOStatement
    {
        $this->sth = $this->dbh->prepare($query);

        return $this->sth;
    }

    /**
     * Binds a parameter to a prepared statement.
     * 
     * @param int|string $param The parameter identifier.
     * @param mixed $value The value to bind to the parameter.
     * @param int $type The data type of the parameter.
     * 
     * @return bool True on success, false on failure.
     */
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

    /**
     * Executes a prepared statement.
     * 
     * @return bool True on success, false on failure.
     */
    public function execute(): bool
    {
        return $this->sth->execute();
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     * 
     * @return string|false The ID of the last inserted row or sequence value, or false on failure.
     */
    public function lastInsertId(): string|false
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * Fetches a row from a result set.
     * 
     * @return mixed The fetched row.
     */
    public function fetch(): mixed
    {
        $this->execute();

        return $this->sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all rows from a result set.
     * 
     * @return array The fetched rows.
     */
    public function fetchAll(): array
    {
        $this->execute();

        return $this->sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns the number of rows affected by the last SQL statement.
     * 
     * @return int The number of rows.
     */
    public function rowCount(): int
    {
        return $this->sth->rowCount();
    }
}
