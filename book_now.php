<?php 
include 'db_connect.php';
extract($_POST);

// Fetch price from schedule
$schedule = $conn->query("SELECT price FROM schedule_list WHERE id = $sid")->fetch_assoc();
$price = $schedule['price'];
$total_amount = $price * $qty;

$data = ' schedule_id = '.$sid.' ';
$data .= ', name = "'.$name.'" ';
$data .= ', qty = "'.$qty.'" ';
$data .= ', amount = "'.$total_amount.'" ';
$data .= ', status = 0 '; // unpaid

if (!empty($bid)) {
    $data .= ', status = "'.$status.'" ';
    $update = $conn->query("UPDATE booked SET ".$data." WHERE id = ".$bid);
    if ($update) {
        echo json_encode(array('status'=> 1));
    }
    exit;
}

// Generate unique reference number
$i = 1;
$ref = '';
while ($i == 1) {
    $ref = date('Ymd') . mt_rand(1000, 9999);
    $chk = $conn->query("SELECT * FROM booked WHERE ref_no = '$ref'")->num_rows;
    if ($chk <= 0) {
        $i = 0;
    }
}
$data .= ', ref_no = "'.$ref.'" ';

$insert = $conn->query("INSERT INTO booked SET ".$data);
if ($insert) {
    echo json_encode([
        'status' => 1,
        'ref' => $ref,
        'booking_id' => $conn->insert_id,
        'amount' => $total_amount
    ]);
}
?>
