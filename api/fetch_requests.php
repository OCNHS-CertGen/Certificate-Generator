<?php
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$status = isset($_GET['status']) ? $_GET['status'] : '';

$where = "";
if ($status) {
    $where = " WHERE cr.status = '" . $conn->real_escape_string($status) . "'";
}

$sql = "SELECT cr.*, u.full_name as processed_by_name 
        FROM certificate_requests cr 
        LEFT JOIN users u ON cr.processed_by = u.id 
        $where
        ORDER BY cr.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

header('Content-Type: application/json');
echo json_encode($requests);
?>
