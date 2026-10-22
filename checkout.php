<?php
session_start();
date_default_timezone_set('Asia/Colombo');

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

$conn = new mysqli("localhost", "root", "jone", "login ice");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_data = $_POST['cart_data'] ?? '';
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $gst = floatval($_POST['gst'] ?? 0);
    $packaging = floatval($_POST['packaging'] ?? 0);
    $total = floatval($_POST['total'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    $cart = json_decode($cart_data, true);
    if(empty($cart)) {
        $_SESSION['error'] = "Cart is empty";
        header("Location: flavors.php");
        exit();
    }

    $status = 'pending';
    $payment_method = 'cod';
    $payment_status = 'Pending';
    $order_type = 'pickup';

    $conn->begin_transaction();
    
    try {
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, username, subtotal, gst, packaging, total, status, payment_method, payment_status, order_type) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isddddssss", $user_id, $username, $subtotal, $gst, $packaging, $total, $status, $payment_method, $payment_status, $order_type);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        foreach($cart as $item) {
            $flavor_id = isset($item['id']) ? intval($item['id']) : 0;
            $image_path = !empty($item['img']) ? $item['img'] : 'images/default.jpg';
            $quantity = isset($item['qty']) ? intval($item['qty']) : 1; 
            
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, flavor_id, flavor_name, image_path, quantity, price) VALUES (?,?,?,?,?,?)");
            $item_stmt->bind_param("iissid", $order_id, $flavor_id, $item['name'], $image_path, $quantity, $item['price']);
            $item_stmt->execute(); 
            $item_stmt->close();
        }

        $conn->commit();
        
        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $order_id;
        header("Location: address.php?order_id=$order_id&amount=$total");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Order failed: " . $e->getMessage();
        header("Location: flavors.php");
        exit();
    }
}
$conn->close();
?>