<?php
require_once "../config/db.php";
require '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'];
    $faculty_id = $_POST['faculty_id']; // from session in real case
    $session_date = $_POST['session_date'];

    // Insert new session into DB
    $stmt = $conn->prepare("INSERT INTO sessions (subject_id, faculty_id, session_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $subject_id, $faculty_id, $session_date);
    $stmt->execute();
    $session_id = $stmt->insert_id;

    // Generate QR link
    $qrData = "http://localhost/eduplusai/attendance/mark.php?session_id=" . $session_id;

    $qrCode = new QrCode($qrData);

    $writer = new PngWriter();

    // Generate result with size
    $result = $writer->write($qrCode, null, null, [
        'size' => 300
    ]);

    $qrFile = __DIR__ . "/scans/session_$session_id.png";
    $result->saveToFile($qrFile);

    echo json_encode([
        "success" => true,
        "qr_url" => "scans/session_$session_id.png"
    ]);
}
