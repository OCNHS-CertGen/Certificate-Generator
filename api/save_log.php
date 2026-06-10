<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/database.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['certificate_type']) || empty($data['student_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data', 'received' => $data]);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO certificate_logs (certificate_type, student_name, lrn, grade_level, section_track, curriculum, school_year, purpose, date_issued, principal_name, generated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssssssssi",
        $data['certificate_type'],
        $data['student_name'],
        $data['lrn'],
        $data['grade_level'],
        $data['section_track'],
        $data['curriculum'],
        $data['school_year'],
        $data['purpose'],
        $data['date_issued'],
        $data['principal_name'],
        $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Update request status if request_id is provided
    if (!empty($data['request_id'])) {
        $requestId = (int)$data['request_id'];
        $processedBy = $_SESSION['user_id'];
        $conn->query("UPDATE certificate_requests SET status = 'Approved', processed_by = $processedBy WHERE id = $requestId");
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
