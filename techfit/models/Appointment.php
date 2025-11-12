<?php
// models/Appointment.php

class Appointment {
    private $conn;
    private $table_name = "appointments";

    public $id;
    public $user_id;
    public $schedule_id;
    public $title;
    public $appointment_date;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserAppointments($user_id) {
        $query = "SELECT a.id, a.title, 
                  CONCAT(DATE_FORMAT(a.appointment_date, '%d/%m'), ', ', s.time) as date,
                  a.status
                  FROM " . $this->table_name . " a
                  JOIN schedules s ON a.schedule_id = s.id
                  WHERE a.user_id = :user_id 
                  AND a.status != 'cancelled'
                  ORDER BY a.appointment_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAppointment($user_id, $schedule_id, $title, $date) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, schedule_id, title, appointment_date, status) 
                  VALUES (:user_id, :schedule_id, :title, :date, 'pending')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":schedule_id", $schedule_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":date", $date);

        return $stmt->execute();
    }

    public function confirmAppointment($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'confirmed' 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function cancelAppointment($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'cancelled' 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function getNextAppointment($user_id) {
        $query = "SELECT a.title, 
                  CONCAT(DATE_FORMAT(a.appointment_date, '%d/%m'), ', ', s.time) as datetime,
                  s.time
                  FROM " . $this->table_name . " a
                  JOIN schedules s ON a.schedule_id = s.id
                  WHERE a.user_id = :user_id 
                  AND a.appointment_date >= CURDATE()
                  AND a.status != 'cancelled'
                  ORDER BY a.appointment_date ASC, s.time ASC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>