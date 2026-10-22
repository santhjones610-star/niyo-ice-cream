<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$conn = new mysqli("localhost", "root", "jone", "login ice");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";


if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_stock'])){
    $id = intval($_POST['id']);
    $stock = intval($_POST['stock']);
    
    $stmt = $conn->prepare("UPDATE flavors SET stock=? WHERE id=?");
    $stmt->bind_param("ii", $stock, $id);
    if($stmt->execute()){
        $success = "Stock Updated Successfully for ID #$id ✅";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}


$result = $conn->query("SELECT * FROM flavors ORDER BY category, name ASC");
$low_stock_count = $conn->query("SELECT COUNT(*) as c FROM flavors WHERE stock < 5 AND stock > 0")->fetch_assoc()['c'];
$out_stock_count = $conn->query("SELECT COUNT(*) as c FROM flavors WHERE stock = 0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Management - Niyo Ice Cream</title>
    
    <style>
        * { margin: 0; padding: 0;
         box-sizing: border-box;
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
         }
        body { background: #667893;
         padding-top: 75px; 
         background-image: url('2.jpg');
        
    
    }
        .navbar { position: fixed;
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
        }
        .nav-links a { margin-left: 20px; 
        text-decoration: none;
         color: #555; 
         font-weight: 600;
          font-size: 14px;
         }
        .nav-links a:hover, .nav-links a.active { color: #667eea; 
    }
        
        .container { max-width: 1200px; 
        margin: 30px auto;
         padding: 0 20px;
         }
        
        .alert { padding: 15px;
         border-radius: 10px; 
         margin-bottom: 20px;
          border-left: 4px solid; 
        }
        .alert-low { background: #fff3cd; 
        color: #856404;
         border-color: #ffc107; 
        }
        .alert-out { background: #f8d7da; 
        color: #721c24;
         border-color: #dc3545;
         }
        .alert i { margin-right: 8px;
     }
        
        .panel { background:#667893 ;
         border-radius: 12px; 
         padding: 25px;
          box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
        }
        .panel h2 { color: #2c3e50;
         margin-bottom: 20px;
          display: flex; 
          align-items: center;
           gap: 10px; 
        }
        
        .message { padding: 12px;
         border-radius: 8px;
          margin-bottom: 15px;
           text-align: center;
            font-weight: 600;
         }
        .success { background: #d4edda;
         color: #155724; 
        }
        .error { background: #f8d7da;
         color: #721c24;
         }
        
        .stock-table { width: 100%;
         border-collapse: collapse; 
         font-size: 14px;
         }
        .stock-table th { text-align: left;
         padding: 14px 10px;
          background: #f8fafc;
           color: #7f8c8d; 
           font-size: 12px;
            text-transform: uppercase; 
            border-bottom: 2px solid #e2e8f0; 
        }
        .stock-table td { padding: 14px 10px; 
        border-bottom: 1px solid #edf2f7; 
    }
        .stock-table img { width: 50px;
         height: 50px; 
         border-radius: 8px;
          object-fit: cover; 
        }
        
        .stock-badge { padding: 6px 12px;
         border-radius: 20px;
          font-size: 12px;
           font-weight: 700;
         }
        .stock-ok { background: #d4edda;
         color: #155724; 
        }
        .stock-low { background: #fff3cd; 
        color: #856404; 
    }
        .stock-out { background: #f8d7da; 
        color: #721c24;
     }
        
        .stock-input { width: 80px;
         padding: 8px; 
         border: 2px solid #ddd; 
         border-radius: 6px; 
         text-align: center; 
         font-weight: 600; 
        }
        .btn-update { padding: 8px 16px;
         background: #4361ee; 
         color: white; 
         border: none; 
         border-radius: 6px;
          cursor: pointer; 
          font-weight: 600; 
        }
        .btn-update:hover { background: #3a56d4; 
    }
        
        .category-badge { background: #e8eaf6;
         color: #667eea; 
         padding: 4px 10px;
          border-radius: 12px; 
          font-size: 12px; 
          font-weight: 600; 
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
        
        <?php if($out_stock_count > 0): ?>
        <div class="alert alert-out">
            <i class="fas fa-times-circle"></i>
            <b>Out of Stock:</b> <?php echo $out_stock_count; ?> Flavors. Customers cannot order these.
        </div>
        <?php endif; ?>

        <?php if($low_stock_count > 0): ?>
        <div class="alert alert-low">
            <i class="fas fa-exclamation-triangle"></i>
            <b>Low Stock Alert:</b> <?php echo $low_stock_count; ?> Flavors have less than 5L. Please restock.
        </div>
        <?php endif; ?>

        <div class="panel">
            <h2><i class="fas fa-boxes"></i> Inventory Management</h2>

            <?php if(!empty($success)):?><div class="message success"><?php echo $success;?></div><?php endif;?>
            <?php if(!empty($error)):?><div class="message error"><?php echo $error;?></div><?php endif;?>

            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Flavor Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Update Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $stock = $row['stock'];
                            if($stock == 0) { $status_class = 'stock-out'; $status_text = 'Out of Stock'; }
                            elseif($stock < 5) { $status_class = 'stock-low'; $status_text = 'Low Stock'; }
                            else { $status_class = 'stock-ok'; $status_text = 'In Stock'; }
                        ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($row['image_path']); ?>" onerror="this.src='"></td>
                            <td><b><?php echo htmlspecialchars($row['name']); ?></b></td>
                            <td><span class="category-badge"><?php echo $row['category']; ?></span></td>
                            <td><b>Rs. <?php echo number_format($row['price'], 2); ?></b></td>
                            <td><b><?php echo $stock; ?> Ltr</b></td>
                            <td><span class="stock-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td>
                                <form method="POST" style="display: flex; gap: 8px;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="number" name="stock" value="<?php echo $stock; ?>" class="stock-input" min="0">
                                    <button type="submit" name="update_stock" class="btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">No flavors found. Add flavors first.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>