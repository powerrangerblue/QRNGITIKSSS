<?php
// qr_image.php

include 'db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Missing id');
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT student_id, student_name FROM qr_requests WHERE id = ? AND status = 'approved'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(404);
    exit('QR Code not found or not approved.');
}

$row = $result->fetch_assoc();
$data = "StudentID:" . $row['student_id'] . ";Name:" . $row['student_name'];

// Very simple manual "QR" generator (not real QR code, but a pattern based on hash)

$size = 150;
$image = imagecreate($size, $size);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
imagefill($image, 0, 0, $white);

// Hash data to binary string
$hash = md5($data);
$binary = '';
for ($i = 0; $i < strlen($hash); $i++) {
    $binary .= str_pad(base_convert($hash[$i], 16, 2), 4, '0', STR_PAD_LEFT);
}

// Draw squares for each bit (black = 1, white = 0)
$cols = 25;
$rows = 25;
$blockSize = $size / $cols;

for ($y = 0; $y < $rows; $y++) {
    for ($x = 0; $x < $cols; $x++) {
        $index = $y * $cols + $x;
        if (isset($binary[$index]) && $binary[$index] === '1') {
            imagefilledrectangle(
                $image,
                $x * $blockSize,
                $y * $blockSize,
                ($x + 1) * $blockSize - 1,
                ($y + 1) * $blockSize - 1,
                $black
            );
        }
    }
}

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
