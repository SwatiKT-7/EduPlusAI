<?php
require '../vendor/autoload.php';
require_once "../config/db.php";

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

$session_id = $_GET['session_id'] ?? 0;

header('Content-Type: image/png');

$qrData = "http://localhost/eduplusai/attendance/mark.php?session_id=$session_id";

// ✅ Use Builder (v4+ recommended way)
$result = Builder::create()
    ->writer(new PngWriter())
    ->data($qrData)
    ->size(250) // size in px
    ->build();

// ✅ Output PNG directly
echo $result->getString();
