<?php

namespace App\Models;

use App\Database;
use Exception;
use Slim\Exception\HttpBadRequestException;

/**
 * The User class.
 * 
 * This class handles user operations.
 */
class User
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
    private string $table = 'users';

    /**
     * Creates a new User instance.
     * 
     * @param Database|null $db The database instance for handling database operations.
     */
    public function __construct(Database $db = null)
    {
        $this->db = $db ?? new Database();

        // Create the users table if it doesn't exist.
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

    /**
     * Creates a new user.
     * 
     * @param array $data The user data.
     * 
     * @return array The created user data.
     */
    public function create(array $data): array
    {
        // Hash the password.
        $password = password_hash($data["password"], PASSWORD_DEFAULT);

        try {
            $this->db->prepare("INSERT INTO $this->table (name, email, password) VALUES (:name, :email, :password)");
            $this->db->bindParam(':name', $data['name']);
            $this->db->bindParam(':email', $data['email']);
            $this->db->bindParam(':password', $password);

            $this->db->execute();

            $id = $this->db->lastInsertId();

            return [
                'id' => (int)$id,
                'name' => $data['name'],
            ];
        } catch (\PDOException $th) {
            // Check if the email address already exists.
            if ($th->getCode() == 23000) {
                throw new Exception('Email address already exists.', 409);
            } else {
                throw new Exception('Internal server error.');
            }
        }
    }

    /**
     * Authenticates a user.
     * 
     * @param array $data The user data.
     * 
     * @return array The authenticated user data.
     */
    public function authenticate(array $data): array
    {
        try {
            $this->db->prepare("SELECT id, name, email, password FROM {$this->table} WHERE BINARY email = :email");
            $this->db->bindParam(':email', $data['email']);

            $user = $this->db->fetch();

            // Check if the user exists and the password is correct.
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
}
