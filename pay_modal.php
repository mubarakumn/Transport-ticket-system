<?php
include('db_connect.php');

if (!isset($_GET['booking_id'])) {
    die("Missing booking ID");
}

$booking_id = $_GET['booking_id'];
$qry = $conn->query("SELECT * FROM booked WHERE id = $booking_id");
if (!$qry || $qry->num_rows == 0) {
    die("Invalid Booking");
}

$book = $qry->fetch_assoc();
$email = 'test@example.com'; // Replace with real user email if available
$amount = $book['amount'] * 100; // Kobo
?>

<div class="text-center p-3">
    <h5>Pay ₦<?= number_format($book['amount'], 2) ?> for Ticket #<?= $book['ref_no'] ?></h5>
    <button class="btn btn-success mt-2" onclick="payNow()">Pay Now</button>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    function payNow() {
        $paystackPublicKey = "pk_test_0cbff431d626142b409c5602cfc51e2c19d90e40";

        var handler = PaystackPop.setup({
            key: "pk_test_0cbff431d626142b409c5602cfc51e2c19d90e40",
            email: '<?= $email ?>',
            amount: <?= $amount ?>,
            currency: 'NGN',
            ref: 'TKT_' + Math.floor((Math.random() * 1000000000) + 1),

            callback: function (response) {
                $.ajax({
                    url: 'verify_payment.php',
                    method: 'POST',
                    data: {
                        reference: response.reference,
                        booking_id: <?= $booking_id ?>
                    },
                    success: function (res) {
                        $('.modal').modal('hide');
                        alert_toast("✅ Payment successful!", 'success');
                    },
                    error: function () {
                        alert_toast("❌ Payment verification failed.", 'danger');
                    }
                });
            },

            onClose: function () {
                alert("❌ Payment was cancelled.");
            }
        });
        handler.openIframe();
    }
</script>