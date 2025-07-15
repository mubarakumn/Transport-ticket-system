<?php
include('db_connect.php');

$reference = $_POST['reference'];
$booking_id = $_POST['booking_id'];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer YOUR_PAYSTACK_SECRET_KEY_HERE", // 🔁 replace this
        "Cache-Control: no-cache"
    ],
]);

$response = curl_exec($curl);
curl_close($curl);
$result = json_decode($response, true);

if ($result['data']['status'] === 'success') {
    $conn->query("UPDATE booked SET status = 1 WHERE id = $booking_id");
    echo 'success';
} else {
    http_response_code(400);
    echo 'failed';
}
?>