<?php

namespace App\Models;

use App\Database;

/**
 * The Todo class.
 * 
 * This class handles todo operations.
 */
class Todo
{
    /**
     * The database instance for handling database operations.
     * 
     * @var Database
     */
    private Database $db;

    /**
     * The table name.
     * 
     * @var string
     */
    private string $table = 'todos';

    /**
     * Creates a new Todo instance.
     * 
     * @param Database|null $db The database instance for handling database operations.
     */
    public function __construct(Database $db = null)
    {
        $this->db = $db ?? new Database();

        // Create the todos table if it doesn't exist.
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

    /**
     * Creates a new todo.
     * 
     * @param array $data The todo data.
     * 
     * @return array The created todo data.
     */
    public function create(array $data): array
    {
        $this->db->prepare("INSERT INTO $this->table (user_id, title, description) VALUES (:user_id, :title, :description)");
        $this->db->bindParam(':user_id', $data['user_id']);
        $this->db->bindParam(':title', $data['title']);
        $this->db->bindParam(':description', $data['description']);

        $this->db->execute();

        $id = $this->db->lastInsertId();

        return [
            'id' => (int)$id,
            'title' => $data['title'],
            'description' => $data['description']
        ];
    }

    /**
     * Gets a todo by id.
     * 
     * @param int $id The todo id.
     * @param int $user_id The user id.
     * 
     * @return array|false The todo data or false if not found.
     */
    public function get(int $id, int $user_id): array|false
    {
        $this->db->prepare("SELECT id, title, description, status, created_at, updated_at FROM $this->table WHERE id = :id AND user_id = :user_id");
        $this->db->bindParam(':id', $id);
        $this->db->bindParam(':user_id', $user_id);

        return $this->db->fetch();
    }

    /**
     * Gets all todos.
     * 
     * @param int $user_id The user id.
     * @param int $start The start index.
     * @param int $limit The limit.
     * @param array $filters The filters.
     * 
     * @return array The todos data.
     */
    public function getAll(int $user_id, int $start = 0, int $limit = 0, array $filters = []): array
    {
        $query = "SELECT id, title, description, status, created_at, updated_at FROM $this->table WHERE user_id = :user_id ";

        // Add status filter if not empty.
        if (!empty($filters['status'])) {
            $query .= "AND status = '{$filters['status']}' ";
        }

        // Add sort filter if not empty.
        if (!empty($filters['sort'])) {
            $query .= "ORDER BY {$filters['sort']} DESC ";
        }

        // Add limit and offset.
        $query .= "LIMIT :start, :limit";

        $this->db->prepare($query);
        $this->db->bindParam(':user_id', $user_id);
        $this->db->bindParam(':start', $start);
        $this->db->bindParam(':limit', $limit);

        return $this->db->fetchAll();
    }

    /**
     * Updates a todo.
     * 
     * @param int $id The todo id.
     * @param array $data The todo data.
     * 
     * @return array The updated todo data.
     */
    public function update(int $id, array $data): array
    {
        try {
            // Get the user id by todo id.
            $todo = $this->getUserIdbyId($id);

            // Check if the user is the owner of the todo.
            if ($data['user_id'] !== $todo['user_id']) {
                throw new \PDOException("Forbidden", 403);
            }

            // Get the status by todo id.
            $todo = $this->getStatusById($id);

            // Set the status.
            $status = $data['status'] ?? $todo['status'];

            $this->db->prepare("UPDATE $this->table SET title = :title, description = :description, status = :status WHERE id = :id");
            $this->db->bindParam(':title', $data['title']);
            $this->db->bindParam(':description', $data['description']);
            $this->db->bindParam(':status', $status);
            $this->db->bindParam(':id', $id);

            $this->db->execute();

            return [
                'id' => $id,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $status
            ];
        } catch (\PDOException $th) {
            if ($th->getCode() == 404 || $th->getCode() == 403) {
                throw new \Exception($th->getMessage(), $th->getCode());
            } else {
                throw new \Exception('Internal server error', 500);
            }
        }
    }

    /**
     * Deletes a todo.
     * 
     * @param int $id The todo id.
     * @param int $user_id The user id.
     * 
     * @return bool True if the todo was deleted, false otherwise.
     */
    public function delete(int $id, int $user_id): bool
    {
        try {
            // Get the user id by todo id.
            $todo = $this->getUserIdbyId($id);

            // Check if the user is the owner of the todo.
            if ($user_id !== $todo['user_id']) {
                throw new \PDOException("Forbidden", 403);
            }

            $this->db->prepare("DELETE FROM $this->table WHERE id = :id");
            $this->db->bindParam(':id', $id);

            $result = $this->db->execute();

            return $result;
        } catch (\PDOException $th) {
            if ($th->getCode() == 404 || $th->getCode() == 403) {
                throw new \Exception($th->getMessage(), $th->getCode());
            } else {
                throw new \Exception("Internal server error", 500);
            }
        }
    }

    /**
     * Gets the user id by todo id.
     * 
     * @param int $id The todo id.
     * 
     * @return array The user id.
     */
    private function getUserIdbyId(int $id): array
    {
        $this->db->prepare("SELECT user_id FROM $this->table WHERE id = :id");
        $this->db->bindParam(':id', $id);
        $this->db->execute();

        // Check if the todo exists.
        if ($this->db->rowCount() === 0) {
            throw new \PDOException('Todo not found.', 404);
        }

        return $this->db->fetch();
    }

    /**
     * Gets the status by todo id.
     * 
     * @param int $id The todo id.
     * 
     * @return array The status.
     */
    private function getStatusById(int $id): array
    {
        $this->db->prepare("SELECT status FROM $this->table WHERE id = :id");
        $this->db->bindParam(':id', $id);
        $this->db->execute();

        return $this->db->fetch();
    }

    /**
     * Counts the number of todos.
     * 
     * @param int $user_id The user id.
     * 
     * @return array The count.
     */
    public function count(int $user_id): array
    {
        $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE user_id = :user_id");
        $this->db->bindParam(':user_id', $user_id);

        return $this->db->fetch();
    }
}
