<?php

require_once __DIR__ . '/../models/Student.php';

class StudentController {

    private $model;

    public function __construct($db) {
        $this->model = new Student($db);
    }

    // GET ALL STUDENTS
    public function getAll() {
        $data = $this->model->getAll();
        echo json_encode($data);
    }

    // CREATE STUDENT
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        $result = $this->model->create($data);

        echo json_encode([
            "success" => $result
        ]);
    }

    // DEACTIVATE
    public function deactivate() {
        $data = json_decode(file_get_contents("php://input"), true);

        $this->model->updateStatus($data['id'], 'blocked');

        echo json_encode([
            "message" => "Student deactivated"
        ]);
    }
}