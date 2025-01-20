<?php

namespace App\Models;

use App\Database;

class Todo
{
    private $db;
    private string $table = 'todos';

    public function __construct(Database $db = null)
    {
        $this->db = $db ?? new Database();

        $this->db->exec("CREATE TABLE IF NOT EXISTS $this->table (
                         id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                         user_id INT(11) UNSIGNED NOT NULL,
                         title VARCHAR(100) NOT NULL,
                         description TEXT NOT NULL,
                         status ENUM('todo', 'in progress', 'done') DEFAULT 'todo',
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                       )");
    }

    public function create(array $data): array
    {
        $this->db->prepare("INSERT INTO $this->table (user_id, title, description) VALUES (:user_id, :title, :description)");
        $this->db->bindParam(':user_id', $data['user_id']);
        $this->db->bindParam(':title', $data['title']);
        $this->db->bindParam(':description', $data['description']);

        $this->db->execute();

        $id = $this->db->lastInsertId();

        return [
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description']
        ];
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

    public function update(int $id, array $data): array
    {
        try {
            $this->db->prepare("SELECT user_id FROM $this->table WHERE id = :id");
            $this->db->bindParam(':id', $id);
            $this->db->execute();

            if ($this->db->rowCount() === 0) {
                throw new \PDOException('Todo not found.', 404);
            }

            $todo = $this->db->fetch();

            if ($data['user_id'] !== $todo['user_id']) {
                throw new \PDOException("Forbidden", 403);
            }

            $this->db->prepare("UPDATE $this->table SET title = :title, description = :description WHERE id = :id");
            $this->db->bindParam(':title', $data['title']);
            $this->db->bindParam(':description', $data['description']);
            $this->db->bindParam(':id', $id);

            $this->db->execute();

            return [
                'id' => $id,
                'title' => $data['title'],
                'description' => $data['description']
            ];
        } catch (\PDOException $th) {
            if ($th->getCode() == 404 || $th->getCode() == 403) {
                throw new \Exception($th->getMessage(), $th->getCode());
            } else {
                throw new \Exception('Internal server error', 500);
            }
        }
    }

    public function delete(int $id): bool
    {
        // Write Code

        return true;
    }
}
