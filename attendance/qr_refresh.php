<?php
require '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

header('Content-Type: image/png');

$qrData = "http://localhost/eduplusai/attendance/mark.php?session_id=123";

$qrCode = new QrCode($qrData);
$qrCode->setSize(250);

$writer = new PngWriter();
$result = $writer->write($qrCode);

echo $result->getString();
