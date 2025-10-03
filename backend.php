<?php
session_start();
header('Content-Type: application/json');

$DB_FILE = "emails.db";

// Create a PDO connection
function getDb() {
    global $DB_FILE;
    return new PDO("sqlite:" . $DB_FILE);
}

// Handle requests
$action = $_GET['action'] ?? '';

if ($action == 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $db = getDb();
    $stmt = $db->prepare("SELECT id FROM users WHERE username=? AND password=?");
    $stmt->execute([$username, $password]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $_SESSION['user_id'] = $row['id'];
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }

} else if ($action == 'emails') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([]);
        exit;
    }
    $user_id = $_SESSION['user_id'];

    $db = getDb();
    $stmt = $db->prepare("SELECT id, sender, subject FROM emails WHERE user_id=?");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} else if ($action == 'read_email') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(["error" => "Not logged in"]);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $msg_id = $_GET['id'] ?? 0;

    $db = getDb();
    $stmt = $db->prepare("SELECT sender, subject, body FROM emails WHERE id=? AND user_id=?");
    $stmt->execute([$msg_id, $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Delete after reading
        $stmt = $db->prepare("DELETE FROM emails WHERE id=? AND user_id=?");
        $stmt->execute([$msg_id, $user_id]);

        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Email not found"]);
    }

} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid action"]);
}
?>
