<?php
session_start();
$conn = new mysqli("localhost", "root", "jone", "login ice");


session_start();
date_default_timezone_set('Asia/Colombo');

// Admin check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "Only Admin can access POS";
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "jone", "login ice");
// ... rest of your code
?>
$flavors = $conn->query("SELECT * FROM flavors WHERE stock > 0");

if(isset($_POST['place_pos_order'])){
    $customer_name = $_POST['customer_name'];
    $items_json = $_POST['cart_items'];
    $total = floatval($_POST['total']);
    $cart = json_decode($items_json, true);
    
    $conn->begin_transaction();
    try {
        // Order create
        $stmt = $conn->prepare("INSERT INTO orders (user_id, username, subtotal, gst, packaging, total, status, payment_method, payment_status, order_type) VALUES (0,?,0,0,0,?,'confirmed','cod','Paid','walkin')");
        $stmt->bind_param("sd", $customer_name, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Items insert
        foreach($cart as $item){
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, flavor_id, flavor_name, quantity, price) VALUES (?,?,?,?,?)");
            $item_stmt->bind_param("iisid", $order_id, $item['id'], $item['name'], $item['qty'], $item['price']);
            $item_stmt->execute();
            
            // Stock reduce
            $conn->query("UPDATE flavors SET stock = stock - ".$item['qty']." WHERE id=".$item['id']);
        }
        $conn->commit();
        
        echo "<script>alert('Bill Generated! Order ID: $order_id'); window.location='admin_pos.php';</script>";
        
    } catch(Exception $e){
        $conn->rollback();
        echo "Error: ".$e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin POS - Niyo Ice Cream</title>
<style>
body{font-family:'Segoe UI'; background:#f0f0f0; padding:20px; display:flex; gap:20px;}
.left{width:60%; background:#fff; padding:20px; border-radius:10px;}
.right{width:35%; background:#fff; padding:20px; border-radius:10px; position:sticky; top:20px;}
.flavor-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:15px;}
.flavor-card{border:1px solid #ddd; padding:10px; text-align:center; border-radius:8px; cursor:pointer;}
.flavor-card:hover{background:#e3f2fd;}
.cart-item{display:flex; justify-content:space-between; margin-bottom:10px;}
.btn{background:#22c55e; color:#fff; border:none; padding:15px; width:100%; font-size:16px; font-weight:700; border-radius:8px; cursor:pointer;}
.total{font-size:24px; font-weight:800;}
</style>
</head>
<body>

<div class="left">
    <h2>🍦 Select Flavors</h2>
    <input type="text" id="customer_name" placeholder="Customer Name" style="width:100%; padding:10px; margin-bottom:15px;">
    <div class="flavor-grid">
        <?php while($f = $flavors->fetch_assoc()): ?>
        <div class="flavor-card" onclick="addToCart(<?php echo $f['id']; ?>, '<?php echo $f['name']; ?>', <?php echo $f['price']; ?>)">
            <b><?php echo $f['name']; ?></b><br>
            Rs. <?php echo $f['price']; ?><br>
            <small>Stock: <?php echo $f['stock']; ?></small>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="right">
    <h2>🧾 Current Bill</h2>
    <div id="cart"></div>
    <h3>Total: <span class="total" id="total">Rs. 0.00</span></h3>
    
    <form method="POST" id="posForm">
        <input type="hidden" name="cart_items" id="cart_items">
        <input type="hidden" name="total" id="total_input">
        <input type="hidden" name="customer_name" id="customer_input">
        <button type="submit" name="place_pos_order" class="btn">💰 Generate Bill & Take Payment</button>
    </form>
</div>

<script>
let cart = [];
function addToCart(id, name, price){
    let item = cart.find(x => x.id == id);
    if(item){ item.qty++; } 
    else{ cart.push({id:id, name:name, price:price, qty:1}); }
    renderCart();
}

function renderCart(){
    let html = ''; let total = 0;
    cart.forEach(item => {
        total += item.price * item.qty;
        html += `<div class="cart-item">
            <span>${item.name} x ${item.qty}</span>
            <span>Rs. ${item.price * item.qty}</span>
        </div>`;
    });
    document.getElementById('cart').innerHTML = html;
    document.getElementById('total').innerText = 'Rs. ' + total.toFixed(2);
    document.getElementById('cart_items').value = JSON.stringify(cart);
    document.getElementById('total_input').value = total;
    document.getElementById('customer_input').value = document.getElementById('customer_name').value;
}
</script>
</body>
</html>