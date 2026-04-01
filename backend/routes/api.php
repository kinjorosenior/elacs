<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/Device.php';

$database = new Database();
$db = $database->connect();

$request = $_GET['request'] ?? '';


switch ($request) {

    // =============================
    // CHECKIN / CHECKOUTt
    // =============================
    case "checkin":
        require_once __DIR__ . '/../controllers/CheckinController.php';
        (new CheckinController($db))->checkIn();
        break;

    case "checkout":
        require_once __DIR__ . '/../controllers/CheckinController.php';
        (new CheckinController($db))->checkOut();
        break;

    // =============================
    // ANALYTICS
    // =============================
    case "analytics":
        require_once __DIR__ . '/../controllers/AnalyticsController.php';
        (new AnalyticsController($db))->dashboard();
        break;

    case "checkinCheckoutStats":
        require_once __DIR__ . '/../controllers/AnalyticsController.php';
        (new AnalyticsController($db))->checkinCheckoutStats();
        break;

    // =============================
    // DEVICES
    // =============================
    case "devices_inside":
        require_once __DIR__ . '/../controllers/DeviceController.php';
        (new DeviceController($db))->devicesInside();
        break;

    case "devices_outside":
        require_once __DIR__ . '/../controllers/DeviceController.php';
        (new DeviceController($db))->devicesOutside();
        break;

    case "get_devices":
        require_once __DIR__ . '/../controllers/DeviceController.php';
        (new DeviceController($db))->getAll();
        break;

    case "deactivate_device":
        require_once __DIR__ . '/../controllers/DeviceController.php';
        (new DeviceController($db))->deactivate();
        break;

    case "studentReport":
        require_once __DIR__ . '/../controllers/AnalyticsController.php';
        (new AnalyticsController($db))->studentReport();
        break;

    // =============================
    // STUDENTS
    // =============================
    case "get_students":
        require_once __DIR__ . '/../controllers/StudentController.php';
        (new StudentController($db))->getAll();
        break;

    case "create_student":
        require_once __DIR__ . '/../controllers/StudentController.php';
        (new StudentController($db))->create();
        break;

    case "deactivate_student":
        require_once __DIR__ . '/../controllers/StudentController.php';
        (new StudentController($db))->deactivate();
        break;

case "library_settings":
        require_once __DIR__ . '/../api/library/read.php';
        break;

    case "update_library":
        require_once __DIR__ . '/../api/library/update.php';
        break;


    case "activity":
        require_once __DIR__ . '/../controllers/ActivityController.php';
        (new ActivityController($db))->getRecent();
        break;


    default:
        echo json_encode(["error" => "Invalid route"]);
        break;
}