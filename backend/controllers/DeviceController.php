<?php

require_once __DIR__ . "/../models/Device.php";
require_once __DIR__ . "/../models/SuspiciousLog.php";

class DeviceController {

    private $device;

    public function __construct($db) {
        $this->device = new Device($db);
    }


    // =============================
    // Register device
    // =============================
    public function createDevice()
    {

        $data = json_decode(file_get_contents("php://input"), true);

        if(!$data){
            echo json_encode(["error" => "Invalid request"]);
            return;
        }

        $result = $this->device->create($data);

        if($result['success']){

            echo json_encode([
                "success" => true,
                "message" => "Device registered successfully"
            ]);

        }else{

            if(isset($result['duplicate'])){

                require_once __DIR__ . "/../models/User.php";

                $userModel = new User($this->device->conn);

                $newScore = $userModel->adjustTrustScore(
                    $data['user_id'],
                    "high"
                );

                $logger = new SuspiciousLog($this->device->conn);

                $logger->log(
                    $data['library_id'],
                    $data['user_id'],
                    null,
                    "DUPLICATE_SERIAL_ATTEMPT",
                    "high",
                    "Attempt to register duplicate serial number"
                );

                echo json_encode([
                    "success" => false,
                    "error" => "Duplicate serial number detected",
                    "risk" => "HIGH",
                    "new_trust_score" => $newScore
                ]);

            }else{

                echo json_encode([
                    "success" => false,
                    "error" => "Device registration failed"
                ]);

            }

        }
    }


    // =============================
    // Devices currently inside
    // =============================
    public function devicesInside()
    {
        $devices = $this->device->getDevicesInside();
        echo json_encode($devices);
    }

    // =============================
    // Devices currently outside
    // =============================
    public function devicesOutside()
    {
        $devices = $this->device->getDevicesOutside();
        echo json_encode($devices);
    }

    // =============================
    // Get all devices
    // =============================
    public function getAll()
    {
        $devices = $this->device->getAll();
        echo json_encode($devices);
    }

}
