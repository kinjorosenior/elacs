<?php

class Student
{
    private $conn;
    private $table = "students";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ==============================
    // CREATE STUDENT (FIXED)
    // ==============================
    public function create($data)
    {
        $query = "INSERT INTO {$this->table}
                  (library_id, student_id, full_name, user_type, department, year_of_study, phone, email, trust_score, status)
                  VALUES
                  (:library_id, :student_id, :full_name, :user_type, :department, :year_of_study, :phone, :email, :trust_score, :status)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':library_id' => $data['library_id'] ?? null,
            ':student_id' => $data['student_id'],
            ':full_name' => $data['full_name'],
            ':user_type' => $data['user_type'] ?? 'student',
            ':department' => $data['department'] ?? null,
            ':year_of_study' => $data['year_of_study'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':trust_score' => $data['trust_score'] ?? 100,
            ':status' => $data['status'] ?? 'active'
        ]);
    }

    // ==============================
    // FIND BY ID
    // ==============================
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==============================
    // FIND BY STUDENT ID (NEW - IMPORTANT)
    // ==============================
    public function findByStudentId($student_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE student_id = :student_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':student_id' => $student_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==============================
    // FIND BY EMAIL
    // ==============================
    public function findByEmail($email)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==============================
    // UPDATE TRUST SCORE
    // ==============================
    public function updateTrustScore($id, $newScore)
    {
        $query = "UPDATE {$this->table}
                  SET trust_score = :score
                  WHERE id = :student_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':score' => $newScore,
            ':student_id' => $id
        ]);
    }

     // ==============================
    // UPDATE STUDENTS (NEW - FOR FRONTEND)
    // ==============================
    public function updateStatus($id, $status)
{
    $stmt = $this->conn->prepare("
        UPDATE students SET status = :status WHERE id = :student_id
    ");

    return $stmt->execute([
        ':status' => $status,
        ':student_id' => $id
    ]);
}
    // ==============================
    // GET ALL STUDENTS (NEW - FOR FRONTEND)
    // ==============================
    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}