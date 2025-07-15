<?php
session_start();
require_once '../../process/connection.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$userEmail = $_SESSION['email'];

$total_amount = isset($_POST['total_price']) ? (float)filter_var($_POST['total_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

if ($total_amount <= 0) {
    header("Location: cart.php");
    exit();
}
$delivery_address = '';
$error_message = '';


$current_page = '/user/cart/payment.php';
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $current_page && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // $delivery_address = trim($_POST['delivery_address']);
    $delivery_address = $_POST['delivery_address'];
    // echo $total_amount , $delivery_address;
    
    if (empty($delivery_address)) {
        $error_message = "Delivery address is required.";
    } else {
        $delivery_address = mysqli_real_escape_string($connection, $delivery_address);
        $order_id = 'ODR_' . rand(1000000, 9999999);

        $query = "INSERT INTO orders (user_id, total_price, order_id, delivery_address, status) 
                  VALUES ('$user_id', '$total_amount', '$order_id', '$delivery_address', 'Pending')";
        $result = mysqli_query($connection, $query);

        if (!$result) {
            error_log("MySQL Error: " . mysqli_error($connection));
            $error_message = "Error while processing your order. Please try again later.";
        } else {
            $_SESSION['order_id'] = $order_id;
        }
    }
}


$paystackPublicKey = "pk_test_0cbff431d626142b409c5602cfc51e2c19d90e40";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>

<body>
    <?php include './cartnav.php'; ?>

    <div class="payment-container">
        <h1>Complete Your Payment</h1>
        <p>Total Amount: <strong>₦<?php echo number_format($total_amount, 2); ?></strong></p>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" id="paymentForm" action="payment.php">
            <label for="delivery_address">Delivery Address:</label>
            <textarea id="delivery_address" name="delivery_address" required><?php echo htmlspecialchars($delivery_address); ?></textarea>
            <input type="hidden" name="total_price" value="<?php echo $total_amount; ?>">
            <button type="submit" class="pay-button">Proceed to Payment</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('paymentForm'); 
        form.addEventListener('submit', function (e) {
            const addressField = document.getElementById('delivery_address').value.trim();
            if (!addressField) {
                e.preventDefault();
                alert('Please enter your delivery address.');
                return;
            }else{
                e.preventDefault(); 
                const handler = PaystackPop.setup({
                    key: '<?php echo $paystackPublicKey; ?>',
                    email: '<?php echo $userEmail; ?>',
                    amount: <?php echo $total_amount * 100; ?>,
                    currency: 'NGN',
                    ref: '<?php echo isset($_SESSION['order_id']) ? $_SESSION['order_id'] : "PSK_" . rand(1000000, 9999999); ?>',
                    callback: function (response) {
                        // Payment success
                        window.location.href = 'order_status.php?status=success&order_id=<?php echo $_SESSION['order_id']; ?>&reference=' + response.reference;
                    },
                    onClose: function () {
                        // Payment cancelled
                        window.location.href = 'order_status.php?status=cancelled&order_id=<?php echo $_SESSION['order_id']; ?>';
                    }
                });
                handler.openIframe();
            }
        });
    </script>

    <?php include '../../footer.php'; ?>
</body>

</html>
