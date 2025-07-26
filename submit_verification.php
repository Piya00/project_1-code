<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Connect to the database
$conn = new mysqli("localhost", "root", "", "helpdesk");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Collect POST data safely
$fullname   = $_POST['fullname'];
$phone      = $_POST['phone'];
$email      = $_POST['email'];
$address    = $_POST['address'];
$service    = $_POST['service'];
$experience = $_POST['experience'];

// 3. Upload directory
$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 4. Function to upload file and return path or empty string
function uploadFile($inputName, $uploadDir) {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . "_" . basename($_FILES[$inputName]['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
            return $targetPath;
        }
    }
    return "";
}

// 5. Upload files
$idProofPath        = uploadFile('id_proof', $uploadDir);
$certificationPath  = uploadFile('certification', $uploadDir);
$profilePhotoPath   = uploadFile('profile_photo', $uploadDir);

// 6. Insert data into database
$sql = "INSERT INTO employee_verifications 
    (fullname, phone, email, address, service, experience, id_proof_path, certification_path, profile_photo_path) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "sssssssss",
    $fullname,
    $phone,
    $email,
    $address,
    $service,
    $experience,
    $idProofPath,
    $certificationPath,
    $profilePhotoPath
);

if ($stmt->execute()) {
    echo "<script>alert('Verification submitted successfully!'); window.location.href='employeeverificationpage.html';</script>";
} else {
    echo "Error inserting record: " . $stmt->error;
}

$stmt->close();
$conn->close();
