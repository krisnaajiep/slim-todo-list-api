<?php

namespace App\Models;

use App\Database;

class User
{
    private $db;
    private string $table = 'users';

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function create(array $data): array
    {
        $password = password_hash($data["password"], PASSWORD_DEFAULT);

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
