<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

$conn = new mysqli("localhost", "root", "jone", "login ice");
if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

$order_id = 0;
$amount = 0;
$user_id = $_SESSION['user_id']?? 0;

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
} elseif (isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
}

if ($order_id == 0 || $user_id == 0) {
    die("STOPPED: Please login first. <a href='flavors.php'>Go to Shop</a>");
}

$check = $conn->prepare("SELECT id, total FROM orders WHERE id =? AND user_id =?");
$check->bind_param("ii", $order_id, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    die("STOPPED: Order #$order_id not found");
}

$order_data = $result->fetch_assoc();
$amount = $order_data['total'];
$subtotal = $amount;
$check->close();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_type = 'pickup';
    $recipient_name = trim($_POST['recipient_name']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $delivery_date = $_POST['delivery_date'];
    $payment_method = 'cod';

    $stmt = $conn->prepare("INSERT INTO address (order_id, amount, recipient_name, phone_number, address, delivery_date) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("idssss", $order_id, $amount, $recipient_name, $phone_number, $address, $delivery_date);

    if ($stmt->execute()) {
        $stmt->close();

        $update = $conn->prepare("UPDATE orders SET payment_method =?, payment_status = 'Pending', status = 'confirmed', subtotal =?, total =?, order_type =? WHERE id =?");
        $update->bind_param("sddsi", $payment_method, $subtotal, $amount, $order_type, $order_id);
        $update->execute();
        $update->close();

        $conn->close();

        echo "<script>
                alert('Order Confirmed Successfully! 🎉\\nPlease collect from store on ". htmlspecialchars($delivery_date). " 🏪');
                window.location.href='contact.php';
              </script>";
        exit();

    } else {
        $error = "Error saving data: ". $stmt->error;
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - Niyo Ice Cream</title>
    <style>
        * { margin: 0;
         padding: 0;
         box-sizing: border-box; 
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        body {
            background: #a5a2ba;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            padding-top: 90px;
        }

       .navbar {
            position: fixed; 
            top: 0;
             left: 0;
              right: 0; 
              background: white;
            padding: 15px 20px;
             box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
             justify-content: space-between;
              align-items: center; 
              z-index: 999;
        }
.logo { font-size: 24px; 
  font-weight: 800; 
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   -webkit-background-clip: text; 
   -webkit-text-fill-color: transparent;
    display: flex; 
    align-items: center; 
    gap: 8px;
   }


       .nav-links a {
            margin-left: 20px;
             text-decoration: none;
              color: #555; 
              font-weight: 600;
            font-size: 14px; 
            transition: color 0.2s;
        }
       .nav-links a:hover,.nav-links a.active { color: #667eea;
     }

       .container {
            background: #ffffff;
             padding: 30px; 
             border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 480px;
        }
        h2 {
            text-align: center;
             color: #333; 
             margin-bottom: 20px;
            font-size: 22px;
             font-weight: 700;
        }

       .order-info {
            background: #f0f4ff; 
            padding: 18px; 
            border-radius: 10px;
            margin-bottom: 20px; 
            border: 1px solid #d1d9ff;
        }
       .order-row {
            display: flex;
             justify-content: space-between;
            font-size: 14px; 
            margin-bottom: 10px; 
            color: #333;
        }
       .order-row.dashed { border-bottom: 1px dashed #bbb;
        padding-bottom: 10px; 
    }
       .order-row.total { font-size: 16px; 
       font-weight: 800; 
       padding-top: 5px; 
    }
       .order-row.total span:last-child { color: #000; }

        
       .store-box { margin: 25px 0; }
       .store-box h3 {
            text-align: center;
             color: #2e7d32; 
             margin-bottom: 12px;
            font-size: 16px;
             font-weight: 700;
        }
       .store-table {
            width: 100%; 
            border-collapse: collapse;
             font-size: 13px;
            background: #f9fff9;
             border-radius: 8px;
              overflow: hidden;
        }
       .store-table th,.store-table td {
            padding: 10px;
             border: 1px solid #dcedc8;
              text-align: left;
        }
       .store-table th {
            background: #c8e6c9;
             color: #2e7d32;
              font-weight: 700;
               width: 35%;
        }

        label { display: block;
         margin-bottom: 6px; 
         color: #555; 
         font-weight: 600;
          font-size: 13px;
         }
        input[type="text"], input[type="date"], textarea {
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ccc; 
            border-radius: 8px;
            font-size: 14px;
             margin-bottom: 18px;
              background: #fff; 
              outline: none;
        }
        input:focus, textarea:focus { border-color: #3b82f6;
         box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); 
        }
        textarea { resize: vertical; 
        min-height: 70px;
     }
       .payment-box {
            background: #e3f2fd;
             padding: 15px;
              border-radius: 10px;
            margin-bottom: 20px; 
            border: 1px solid #bbdefb;
        }
       .payment-box b { color: #1565c0;
        font-size: 14px; 
    }
       .payment-box.sub { font-size: 12px; 
       color: #555;
        margin-top: 3px; 
    }
        button {
            width: 100%;
             padding: 15px;
              background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
            color: white; 
            border: none; 
            border-radius: 10px;
             font-size: 15px;
            font-weight: 700; 
            cursor: pointer;
             margin-top: 10px; 
             transition: opacity 0.2s;
        }
        button:hover { opacity: 0.95; }
       .error { background: #ffebee; 
       color: #c62828; 
       padding: 12px;
        border-radius: 8px; 
        margin-bottom: 15px;
         text-align: center;
          font-weight: 600;
           font-size: 13px; 
        }
    </style>
</head>
<body>
<div class="navbar">
    <div class="logo">🍦 Niyo Ice Cream Shop</div>
    <div class="nav-links">
        <a href="about.php">About</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="flavors.php">Shop</a>
        <a href="stock.php">Stock</a>
        <a href="flavors add.php">Add Flavors</a>
        <a href="team workers details.php">employees</a>
        <a href="contact.php">Contact</a>
    </div>
</div>

<div class="container">
    <form method="POST" id="checkoutForm">
        <h2>🏪 Store Pickup Order</h2>

        <?php if(!empty($error)):?><div class="error"><?php echo htmlspecialchars($error);?></div><?php endif;?>

        <div class="order-info">
            <div class="order-row dashed"><span>Order ID:</span> <span><?php echo htmlspecialchars($order_id);?></span></div>
            <div class="order-row total"><span>Total Amount:</span> <span>Rs. <?php echo number_format($amount, 2);?></span></div>
        </div>

       
        <div class="store-box">
            <h3>📍 Store Pickup Details</h3>
            <table class="store-table">
               
                
                
                
                <tr>
                    <th>Pickup Hours</th>
                    <td>10:00 AM - 8:30 PM (Daily)</td>
               
            </table>
        </div>

        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id);?>">
        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount);?>">

        <label for="recipient_name">Full Name</label>
        <input type="text" id="recipient_name" name="recipient_name" required>

        <label for="phone_number">Phone Number</label>
        <input type="text" id="phone_number" name="phone_number" required>

        <label for="address">Address</label>
        <textarea id="address" name="address" required></textarea>

        <label for="delivery_date">Select Pickup Date</label>
        <input type="date" id="delivery_date" name="delivery_date" required>

        <div class="payment-box">
            <b>Pay at Store</b>
            <div class="sub">Pay when you collect your order</div>
        </div>

        <button type="submit">Confirm Pickup Order Rs. <?php echo number_format($amount, 2);?></button>
    </form>
</div>

<script>
const today = new Date().toISOString().split('T')[0];
const nextWeek = new Date();
nextWeek.setDate(nextWeek.getDate() + 7);
const maxDate = nextWeek.toISOString().split('T')[0];
document.getElementById('delivery_date').setAttribute('min', today);
document.getElementById('delivery_date').setAttribute('max', maxDate);
</script>
</body>
</html>