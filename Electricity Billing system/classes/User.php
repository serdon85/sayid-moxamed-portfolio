<?php
// classes/User.php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Find user by username
    public function findUserByUsername($username) {
        $this->db->query('SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE username = :username');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    // Login user
    public function login($username, $password) {
        $row = $this->findUserByUsername($username);

        if ($row) {
            $hashed_password = $row->password;
            if (password_verify($password, $hashed_password)) {
                return $row;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // Register user (for initial setup)
    public function register($data) {
        $this->db->query('INSERT INTO users (username, password, full_name, role_id) VALUES (:username, :password, :full_name, :role_id)');
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':role_id', $data['role_id']);
        return $this->db->execute();
    }

    // Get all users with their roles
    public function getAll() {
        $this->db->query('SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id ORDER BY users.created_at DESC');
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query('SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE users.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function update($data) {
        if (!empty($data['password'])) {
            $this->db->query('UPDATE users SET full_name = :full_name, username = :username, password = :password, role_id = :role_id WHERE id = :id');
            $this->db->bind(':password', $data['password']);
        } else {
            $this->db->query('UPDATE users SET full_name = :full_name, username = :username, role_id = :role_id WHERE id = :id');
        }
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':role_id', $data['role_id']);
        $this->db->bind(':id', $data['id']);

        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
