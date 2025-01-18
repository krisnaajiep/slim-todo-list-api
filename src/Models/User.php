<?php

namespace App\Models;

use App\Database;
use Exception;
use Slim\Exception\HttpBadRequestException;

class User
{
    private $db;
    private string $table = 'users';

    public function __construct(Database $db = null)
    {
        $this->db = $db ?? new Database();

        $this->db->exec("CREATE TABLE IF NOT EXISTS $this->table (
                         id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                         name VARCHAR(50) NOT NULL,
                         email VARCHAR(100) NOT NULL,
                         password VARCHAR(255) NOT NULL,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         UNIQUE (email)
                        )");
    }

    public function create(array $data): array
    {
        $password = password_hash($data["password"], PASSWORD_DEFAULT);

        try {
            $this->db->prepare("INSERT INTO $this->table (name, email, password) VALUES (:name, :email, :password)");
            $this->db->bindParam(':name', $data['name']);
            $this->db->bindParam(':email', $data['email']);
            $this->db->bindParam(':password', $password);

            $this->db->execute();

            $id = $this->db->lastInsertId();

            return [
                'id' => $id,
                'name' => $data['name'],
            ];
        } catch (\PDOException $th) {
            if ($th->getCode() == 23000) {
                throw new Exception('Email address already exists.', 409);
            } else {
                throw new Exception('Internal server error.');
            }
        }
    }

    public function authenticate(array $data): array
    {
        try {
            $this->db->prepare("SELECT id, name, email, password FROM {$this->table} WHERE BINARY email = :email");
            $this->db->bindParam(':email', $data['email']);

            $this->db->execute();

            $user = $this->db->fetch();

            if ($this->db->rowCount() === 0 || !password_verify($data['password'], $user['password'])) {
                throw new \PDOException("Invalid email or password.", 401);
            }

            return [
                'id' => $user['id'],
                'name' => $user['name']
            ];
        } catch (\PDOException $th) {
            if ($th->getCode() == 401) {
                throw new Exception($th->getMessage(), $th->getCode());
            } else {
                throw new Exception('Internal server error.');
            }
        }
    }

    public function get(): array
    {
        // Write Code

        return [];
    }

    public function getAll(): array
    {
        // Write Code

        return [];
    }

    public function update(array $data): array
    {
        // Write Code

        return [];
    }

    public function delete(int $id): bool
    {
        // Write Code

        return true;
    }
}
