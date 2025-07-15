<?php
include 'db.php';

$ticket_id = $_GET['ticket_id'];
$stmt = $pdo->prepare("SELECT r.*, u.email, u.name FROM reservations r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$ticket_id]);
$res = $stmt->fetch();

if (!$res) {
    die("Invalid Ticket");
}

$amount = $res['amount'] * 100; // Kobo
?>
<html>
<head><title>Pay for Ticket</title></head>
<body>
    <h3>Pay for Ticket ID #<?= $ticket_id ?></h3>
    <p>Name: <?= htmlspecialchars($res['name']) ?></p>
    <p>Amount: ₦<?= number_format($res['amount'], 2) ?></p>

    <button onclick="payWithPaystack()">Pay Now</button>

    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        function payWithPaystack() {
            var handler = PaystackPop.setup({
                key: 'YOUR_PUBLIC_KEY',
                email: '<?= $res['email'] ?>',
                amount: <?= $amount ?>,
                currency: 'NGN',
                ref: 'TSK_' + Math.floor((Math.random() * 1000000000) + 1),
                callback: function(response) {
                    window.location.href = "verify_payment.php?reference=" + response.reference + "&ticket_id=<?= $ticket_id ?>";
                },
                onClose: function() {
                    alert('Transaction was not completed.');
                }
            });
            handler.openIframe();
        }
    </script>
</body>
</html>
