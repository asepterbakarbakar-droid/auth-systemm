<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id, name, password 
                  FROM " . $this->table_name . " 
                  WHERE email = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->password = $row['password'];
            
            return true;
        }
        return false;
    }

    // Create new user
    public function register() {
        // Check if email already exists
        if($this->emailExists()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, password=:password, created_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            return true;
        }
        
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    public function login() {
        if($this->emailExists() && password_verify($this->password, $this->password)) {
            return true;
        }
        return false;
    }
}
?>