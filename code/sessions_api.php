<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

//API for sessions management
$conn = getDBConnection();
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['sessionId'])) {
            $stmt = $conn->prepare("SELECT s.*, c.courseName, c.courseCode 
                                   FROM sessions s 
                                   JOIN courses c ON s.courseId = c.courseId 
                                   WHERE s.sessionId = ?");
            $stmt->bind_param("i", $_GET['sessionId']);
        } elseif (isset($_GET['courseId'])) {
            $stmt = $conn->prepare("SELECT s.*, c.courseName, c.courseCode 
                                   FROM sessions s 
                                   JOIN courses c ON s.courseId = c.courseId 
                                   WHERE s.courseId = ? 
                                   ORDER BY s.sessionDate, s.sessionTime");
            $stmt->bind_param("i", $_GET['courseId']);
        } else {
            $stmt = $conn->prepare("SELECT s.*, c.courseName, c.courseCode 
                                   FROM sessions s 
                                   JOIN courses c ON s.courseId = c.courseId 
                                   ORDER BY s.sessionDate DESC, s.sessionTime DESC");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode($rows);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['courseId'], $data['sessionTitle'], $data['sessionDate'], $data['sessionTime'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required parameters"]);
            exit;
        }
        
        $duration = $data['duration'] ?? null;
        $location = $data['location'] ?? null;
        
        $stmt = $conn->prepare("INSERT INTO sessions (courseId, sessionTitle, sessionDate, sessionTime, duration, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssis", $data['courseId'], $data['sessionTitle'], $data['sessionDate'], $data['sessionTime'], $duration, $location);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "sessionId" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Session creation failed: " . $stmt->error]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['sessionId'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing session ID"]);
            exit;
        }
        
        $updates = [];
        $params = [];
        $types = "";
        
        if (isset($data['courseId'])) {
            $updates[] = "courseId = ?";
            $params[] = $data['courseId'];
            $types .= "i";
        }
        if (isset($data['sessionTitle'])) {
            $updates[] = "sessionTitle = ?";
            $params[] = $data['sessionTitle'];
            $types .= "s";
        }
        if (isset($data['sessionDate'])) {
            $updates[] = "sessionDate = ?";
            $params[] = $data['sessionDate'];
            $types .= "s";
        }
        if (isset($data['sessionTime'])) {
            $updates[] = "sessionTime = ?";
            $params[] = $data['sessionTime'];
            $types .= "s";
        }
        if (isset($data['duration'])) {
            $updates[] = "duration = ?";
            $params[] = $data['duration'];
            $types .= "i";
        }
        if (isset($data['location'])) {
            $updates[] = "location = ?";
            $params[] = $data['location'];
            $types .= "s";
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(["error" => "No fields to update"]);
            exit;
        }
        
        $params[] = $data['sessionId'];
        $types .= "i";
        
        $sql = "UPDATE sessions SET " . implode(", ", $updates) . " WHERE sessionId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Update failed: " . $stmt->error]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['sessionId'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing session ID"]);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM sessions WHERE sessionId = ?");
        $stmt->bind_param("i", $data['sessionId']);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Delete failed: " . $stmt->error]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

$conn->close();
?>