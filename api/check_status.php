<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';

$ref = $_GET['ref'] ?? '';

if (empty($ref)) {
    echo json_encode(['status' => 'error', 'message' => 'Reference number is required']);
    exit();
}

$ref = $conn->real_escape_string($ref);
$sql = "SELECT status FROM certificate_requests WHERE ref_number = '$ref'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'request_status' => $row['status']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Not found']);
}
?>
