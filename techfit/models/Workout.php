<?php
// models/Workout.php

class Workout {
    private $conn;
    private $table_name = "workout_plans";

    public $id;
    public $user_id;
    public $name;
    public $exercises;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserWorkoutPlans($user_id) {
        $query = "SELECT wp.id, wp.name, 
                  GROUP_CONCAT(we.exercise_name, ' - ', we.sets, 'x', we.reps 
                  ORDER BY we.order_number SEPARATOR '||') as exercises
                  FROM " . $this->table_name . " wp
                  LEFT JOIN workout_exercises we ON wp.id = we.workout_plan_id
                  WHERE wp.user_id = :user_id
                  GROUP BY wp.id, wp.name
                  ORDER BY wp.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar exercícios como array
        foreach ($results as &$plan) {
            if ($plan['exercises']) {
                $plan['exercises'] = explode('||', $plan['exercises']);
            } else {
                $plan['exercises'] = [];
            }
        }

        return $results;
    }

    public function createWorkoutPlan($user_id, $name) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, name) 
                  VALUES (:user_id, :name)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":name", $name);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addExercise($workout_plan_id, $exercise_name, $sets, $reps, $order) {
        $query = "INSERT INTO workout_exercises 
                  (workout_plan_id, exercise_name, sets, reps, order_number) 
                  VALUES (:workout_plan_id, :exercise_name, :sets, :reps, :order_number)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":workout_plan_id", $workout_plan_id);
        $stmt->bindParam(":exercise_name", $exercise_name);
        $stmt->bindParam(":sets", $sets);
        $stmt->bindParam(":reps", $reps);
        $stmt->bindParam(":order_number", $order);

        return $stmt->execute();
    }

    public function deleteWorkoutPlan($id) {
        // Deletar exercícios primeiro (integridade referencial)
        $query = "DELETE FROM workout_exercises WHERE workout_plan_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        // Deletar plano
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
?>