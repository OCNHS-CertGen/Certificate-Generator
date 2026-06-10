<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $student_name = $conn->real_escape_string($_POST['student_name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $contact_number = $conn->real_escape_string($_POST['contact_number'] ?? '');
    $lrn = $conn->real_escape_string($_POST['lrn'] ?? '');
    $certificate_type = $conn->real_escape_string($_POST['certificate_type'] ?? '');
    $grade_level = $conn->real_escape_string($_POST['grade_level'] ?? '');
    $school_year = $conn->real_escape_string($_POST['school_year'] ?? '');
    $section_track = $conn->real_escape_string($_POST['section_track'] ?? '');
    $curriculum = $conn->real_escape_string($_POST['curriculum'] ?? '');
    $purpose = $conn->real_escape_string($_POST['purpose'] ?? '');
    $place_of_birth = $conn->real_escape_string($_POST['Placeofbirth'] ?? '');
    $birth_date = $conn->real_escape_string($_POST['DateofBirth'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');

    // Handle File Uploads
    $upload_dir = 'assets/uploads/requests/';
    $id_image_path = '';
    $selfie_image_path = '';

    // Generate unique reference number (OCNHS-YYYY-XXXX)
    $year = date('Y');
    $random_str = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    $ref_number = "OCNHS-{$year}-{$random_str}";

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

    // Helper function for upload
    function uploadRequirement($file, $type, $ref, $dir, $allowed) {
        if (!isset($file) || $file['error'] !== 0) return '';
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) return '';
        
        // Ensure upload directory exists
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $new_name = $type . "_" . $ref . "." . $ext;
        $target = $dir . $new_name;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $target;
        }
        return '';
    }

    $id_image_path = uploadRequirement($_FILES['id_image'] ?? null, 'ID', $ref_number, $upload_dir, $allowed_ext);
    $selfie_image_path = uploadRequirement($_FILES['selfie_image'] ?? null, 'SELFIE', $ref_number, $upload_dir, $allowed_ext);

    if (empty($id_image_path) || empty($selfie_image_path)) {
        die("Error: Personal identification requirements are required and must be valid images.");
    }

    $sql = "INSERT INTO certificate_requests 
            (ref_number, student_name, email, contact_number, lrn, certificate_type, grade_level, school_year, section_track, curriculum, purpose, id_image, selfie_image, place_of_birth, birth_date, address, status) 
            VALUES 
            ('$ref_number', '$student_name', '$email', '$contact_number', '$lrn', '$certificate_type', '$grade_level', '$school_year', '$section_track', '$curriculum', '$purpose', '$id_image_path', '$selfie_image_path', '$place_of_birth', '$birth_date', '$address', 'Pending')";

    if ($conn->query($sql)) {
        // Send Confirmation Email to Student
        require_once 'includes/mailer.php';
        
        $to = $email;
        $subject = "Request Received: OCNHS Certificate Request (" . $ref_number . ")";
        
        // Special message for Form 137
        $form137_note = "";
        if (strpos(strtoupper($certificate_type), 'FORM 137') !== false || strpos(strtoupper($certificate_type), 'SF10') !== false) {
            $form137_note = "\n\nNote for Form 137 / SF10: Ang Form 137 ay kinakailangan ng manual processing ng aming EMIS staff. Mangyaring maghintay ng 3 hanggang 5 working days bago ito maging handa para sa pickup.";
        }

        $message = "Hello " . $student_name . ",\n\n" .
                   "This is to confirm that Olongapo City National High School EMIS Office has received your request for: " . $certificate_type . ".\n\n" .
                   "Reference Number: " . $ref_number . "\n" .
                   "Current Status: Pending Verification" . $form137_note . "\n\n" .
                   "What to expect:\n" .
                   "1. Our EMIS staff will verify your requirements (ID and Selfie).\n" .
                   "2. You will receive another email once your request is approved or if it needs corrections.\n" .
                   "3. Once processed, you will be notified when it is ready for pickup at the EMIS Office.\n\n" .
                   "You can use your reference number to track your status at our website.\n\n" .
                   "Thank you,\nOCNHS EMIS Office";
        
        // Send using PHPMailer
        sendEmail($to, $subject, $message);

        // Success: Redirect to success page with ref number
        header("Location: request_success.php?ref=" . $ref_number);
        exit();
    } else {
        die("System Error: Unable to process request. Please contact the administrator. " . $conn->error);
    }
} else {
    header("Location: apply.php");
    exit();
}
?>
