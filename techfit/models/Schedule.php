<?php
// models/Schedule.php

class Schedule {
    private $conn;
    private $table_name = "schedules";

    public $id;
    public $time;
    public $available;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllSchedules() {
        $query = "SELECT id, time, available 
                  FROM " . $this->table_name . " 
                  ORDER BY time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkAvailability($schedule_id, $date) {
        $query = "SELECT COUNT(*) as count 
                  FROM appointments 
                  WHERE schedule_id = :schedule_id 
                  AND appointment_date = :date 
                  AND status != 'cancelled'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":schedule_id", $schedule_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] == 0;
    }

    public function updateAvailability($id, $available) {
        $query = "UPDATE " . $this->table_name . " 
                  SET available = :available 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":available", $available);

        return $stmt->execute();
    }
}
?>