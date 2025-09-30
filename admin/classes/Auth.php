<?php
require_once 'Database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($email, $password, $first_name, $last_name, $phone = '') {
        // Check if user exists
        $this->db->query('SELECT id FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        if($this->db->single()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $this->db->query('INSERT INTO users (email, password, first_name, last_name, phone) VALUES (:email, :password, :first_name, :last_name, :phone)');
        $this->db->bind(':email', $email);
        $this->db->bind(':password', $hashed_password);
        $this->db->bind(':first_name', $first_name);
        $this->db->bind(':last_name', $last_name);
        $this->db->bind(':phone', $phone);
        
        if($this->db->execute()) {
            return ['success' => true, 'message' => 'Registration successful'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    public function login($email, $password) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $user = $this->db->single();
        
        if($user && password_verify($password, $user['password'])) {
            if($user['status'] === 'rejected') {
                return ['success' => false, 'message' => 'Your account has been rejected'];
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_status'] = $user['status'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            return ['success' => true, 'user_type' => $user['user_type']];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireAuth() {
        if(!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit();
        }
    }
    
    public function requireAdmin() {
        $this->requireAuth();
        if($_SESSION['user_type'] !== 'admin') {
            header('Location: dashboard.php');
            exit();
        }
    }
    
    public function getCurrentUser() {
        if(!$this->isLoggedIn()) {
            return null;
        }
        
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $_SESSION['user_id']);
        return $this->db->single();
    }
}
?>