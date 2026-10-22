<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();



$conn = new mysqli("localhost", "root", "jone", "login ice");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Reply submit aana
if(isset($_POST['send_reply'])){
    $msg_id = intval($_POST['msg_id']);
    $reply = trim($_POST['reply_text']);
    $stmt = $conn->prepare("UPDATE contact_messages SET reply=?, replied_at=NOW() WHERE id=?");
    $stmt->bind_param("si", $reply, $msg_id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Reply Sent!'); window.location.href='dashboard.php?tab=contact';</script>";
    exit();
}

// Order mark delivered
if(isset($_GET['mark_delivered'])){
    $order_id = intval($_GET['mark_delivered']);
    $update_status = $conn->prepare("UPDATE orders SET status='delivered', payment_status='Paid' WHERE id=?");
    $update_status->bind_param("i", $order_id);
    $update_status->execute();
    $update_status->close();
    echo "<script>alert('Order $order_id Delivered & Paid! ✅'); window.location.href='dashboard.php';</script>";
    exit();
}

// Delete order
if(isset($_GET['delete_order'])){
    $order_id = intval($_GET['delete_order']);
    $del_addr = $conn->prepare("DELETE FROM address WHERE order_id=?");
    $del_addr->bind_param("i", $order_id);
    $del_addr->execute();
    $del_addr->close();

    $del_order = $conn->prepare("DELETE FROM orders WHERE id=?");
    $del_order->bind_param("i", $order_id);
    $del_order->execute();
    $del_order->close();
    echo "<script>alert('Order $order_id Deleted! 🗑️'); window.location.href='dashboard.php';</script>";
    exit();
}

$search = $_GET['search'] ?? '';
$active_tab = $_GET['tab'] ?? 'orders';
$where = "WHERE 1=1";
if($search) {
    $search_safe = $conn->real_escape_string($search);
    $where .= " AND (a.recipient_name LIKE '%$search_safe%' OR a.phone_number LIKE '%$search_safe%' OR o.id = '$search_safe')";
}

// Metrics
$sales_query = $conn->query("SELECT SUM(total) as total_sales FROM orders WHERE status='confirmed' OR status='delivered'");
$total_sales = $sales_query->fetch_assoc()['total_sales'] ?? 0.00;

$product_query = $conn->query("SELECT COUNT(*) as total_products FROM flavors");
$total_products = $product_query->fetch_assoc()['total_products'] ?? 0;

$order_query = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $order_query->fetch_assoc()['total_orders'] ?? 0;

$customer_query = $conn->query("SELECT COUNT(DISTINCT user_id) as total_customers FROM orders");
$total_customers = $customer_query->fetch_assoc()['total_customers'] ?? 0;

$today_date = date('Y-m-d');
$today_query = $conn->query("SELECT SUM(total) as today_sales FROM orders WHERE status='confirmed' AND DATE(created_at) = '$today_date'");
$today_sales = $today_query->fetch_assoc()['today_sales'] ?? 0.00;

// Orders data
$address_details = $conn->query("SELECT o.id, o.total, o.status, o.payment_status, o.created_at, 
                                        a.recipient_name, a.phone_number, a.address, a.delivery_date
                                 FROM orders o 
                                 LEFT JOIN address a ON o.id = a.order_id 
                                 $where
                                 ORDER BY o.id DESC LIMIT 50");

// Contact messages data - NEW
$contact_msgs = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard - Niyo Ice Cream</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: #bcc2cd; min-height: 100vh; padding-top: 75px; color: #333; background-image: url('1.jpg'); backdrop-filter: blur(2px); }
.navbar { position: fixed; top: 0; left: 0; right: 0; background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 999; }
.logo { font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.nav-links a { margin-left: 20px; text-decoration: none; color: #555; font-weight: 600; font-size: 14px; }
.container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
.metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px; }
.card { background: white; padding: 22px; border-radius: 12px; box-shadow: 0 4px 12px rgba(125, 93, 93, 0.03); border-left: 5px solid #65788f; }
.card-details h3 { font-size: 13px; text-transform: uppercase; color: #7f8c8d; margin-bottom: 6px; }
.card-details p { font-size: 22px; font-weight: 800; color: #2d3748; }
.filter-bar { background: white; padding: 16px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 12px; }
.filter-bar input { flex: 1; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; }
.filter-bar button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
.panel { background: white; border-radius: 12px; padding: 25px; margin-bottom:20px; }
.address-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.address-table th { text-align: left; padding: 12px 10px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
.address-table td { padding: 12px 10px; border-bottom: 1px solid #edf2f7; vertical-align: top; }
.status-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
.status-delivered { background: #d1ecf1; color: #0c5460; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-pending { background: #fff3cd; color: #856404; }
.btn-deliver { padding: 6px 12px; background: #10b981; color: white; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; }
.btn-delete { padding: 6px 12px; background: #ef4444; color: white; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; margin-left:5px; }
.action-cell { display: flex; gap: 8px; }
.tabs { display:flex; gap:10px; margin-bottom:20px; }
.tab-btn { padding:10px 20px; background:#e2e8f0; border:none; border-radius:8px; font-weight:700; cursor:pointer; }
.tab-btn.active { background:#667eea; color:#fff; }
.reply-box { margin-top:10px; }
.reply-box textarea { width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; margin-bottom:8px; }
.reply-box button { padding:8px 15px; background:#22c55e; color:#fff; border:none; border-radius:6px; font-weight:700; cursor:pointer; }
.replied { background:#d4edda; padding:10px; border-radius:6px; margin-top:10px; }
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
    <div class="metrics-grid">
        <div class="card"><div class="card-details"><h3>Total Sales</h3><p>Rs. <?php echo number_format($total_sales, 2); ?></p></div></div>
        <div class="card"><div class="card-details"><h3>Today's Sales</h3><p>Rs. <?php echo number_format($today_sales, 2); ?></p></div></div>
        <div class="card"><div class="card-details"><h3>Total Products</h3><p><?php echo $total_products; ?></p></div></div>
        <div class="card"><div class="card-details"><h3>Total Orders</h3><p><?php echo $total_orders; ?></p></div></div>
        <div class="card"><div class="card-details"><h3>Total Customers</h3><p><?php echo $total_customers; ?></p></div></div>
    </div>

    <div class="tabs">
        <button class="tab-btn <?php echo $active_tab=='orders'?'active':''; ?>" onclick="window.location='dashboard.php?tab=orders'">📦 Orders</button>
        <button class="tab-btn <?php echo $active_tab=='contact'?'active':''; ?>" onclick="window.location='dashboard.php?tab=contact'">📩 Contact Messages</button>
    </div>

    <?php if($active_tab == 'orders'): ?>
    <form class="filter-bar" method="GET">
        <input type="hidden" name="tab" value="orders">
        <input type="text" name="search" placeholder="Search Order ID / Name / Phone" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="panel">
        <h4>Order Address Details <?php echo $search ? "(Search: $search)" : ""; ?></h4>
        <table class="address-table">
            <thead>
                <tr>
                    <th>Order ID</th><th>Customer</th><th>Phone</th><th>Address</th><th>Pickup Date</th>
                    <th>Amount</th><th>Payment</th><th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($address_details && $address_details->num_rows > 0): ?>
                    <?php while($row = $address_details->fetch_assoc()): ?>
                    <tr>
                        <td><b><?php echo $row['id']; ?></b><br><small><?php echo date('d M, h:i A', strtotime($row['created_at'])); ?></small></td>
                        <td><b><?php echo $row['recipient_name'] ?? '-'; ?></b></td>
                        <td><?php echo $row['phone_number'] ?? '-'; ?></td>
                        <td><?php echo $row['address'] ?? '-'; ?></td>
                        <td><?php echo $row['delivery_date'] ? date('d M Y', strtotime($row['delivery_date'])) : '-'; ?></td>
                        <td><b>Rs. <?php echo number_format($row['total'], 2); ?></b></td>
                        <td><b><?php echo $row['payment_status'] ?? 'Pending'; ?></b></td>
                        <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td class="action-cell">
                            <?php if($row['status'] != 'delivered'): ?>
                                <a href="dashboard.php?mark_delivered=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Mark Order <?php echo $row['id']; ?> as Delivered & Paid?')" 
                                   class="btn-deliver">Delivered</a>
                            <?php endif; ?>
                            <a href="dashboard.php?delete_order=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Delete Order <?php echo $row['id']; ?>?')" 
                               class="btn-delete">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align: center; padding: 30px;">No orders found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php elseif($active_tab == 'contact'): ?>
    <div class="panel">
        <h4>📩 Customer Contact Messages</h4>
        <table class="address-table">
            <thead>
                <tr>
                    <th>Date</th><th>Name</th><th>Subject</th><th>Message</th><th>IP</th><th>Reply</th>
                </tr>
            </thead>
            <tbody>
                <?php if($contact_msgs && $contact_msgs->num_rows > 0): ?>
                    <?php while($msg = $contact_msgs->fetch_assoc()): ?>
                    <tr>
                        <td><small><?php echo date('d M Y h:i A', strtotime($msg['created_at'])); ?></small></td>
                        <td><b><?php echo $msg['name']; ?></b></td>
                        <td><?php echo $msg['subject']; ?></td>
                        <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                        <td><small><?php echo $msg['ip_address']; ?></small></td>
                        <td>
                            <?php if($msg['reply']): ?>
                                <div class="replied"><b>Replied:</b><br><?php echo nl2br(htmlspecialchars($msg['reply'])); ?><br><small><?php echo date('d M h:i A', strtotime($msg['replied_at'])); ?></small></div>
                            <?php else: ?>
                            <form method="POST" class="reply-box">
                                <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                <textarea name="reply_text" rows="3" placeholder="Type reply here..." required></textarea>
                                <button name="send_reply">Send Reply</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 30px;">No messages yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
<?php $conn->close(); ?>