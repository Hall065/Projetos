<?php
// models/User.php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $phone;
    public $plan;
    public $member_since;
    public $notifications;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserById($id) {
        $query = "SELECT id, name, email, phone, plan, 
                  DATE_FORMAT(member_since, '%M %Y') as member_since, 
                  notifications 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $name, $email, $phone) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, email = :email, phone = :phone 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);

        return $stmt->execute();
    }

    public function getUserStats($id) {
        $query = "SELECT 
                  (SELECT COUNT(*) FROM workout_logs WHERE user_id = :id 
                   AND MONTH(workout_date) = MONTH(CURRENT_DATE())) as monthly_workouts,
                  (SELECT COUNT(*) FROM workout_logs WHERE user_id = :id) as total_workouts,
                  (SELECT AVG(workouts_per_week) FROM 
                   (SELECT WEEK(workout_date) as week, COUNT(*) as workouts_per_week 
                    FROM workout_logs WHERE user_id = :id 
                    GROUP BY WEEK(workout_date)) as weekly) as weekly_frequency";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>