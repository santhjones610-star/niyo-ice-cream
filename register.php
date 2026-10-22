<?php
session_start();
$conn = new mysqli("localhost", "root", "jone", "login ice");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = "Username, Email, Phone, Password are required";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $check = $conn->prepare("SELECT id FROM register WHERE username = ? OR email = ? OR phone = ?");
        $check->bind_param("sss", $username, $email, $phone);
        $check->execute();
        $result = $check->get_result();

        if($result->num_rows > 0) {
            $error = "Username, Email or Phone already exists";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO register (username, email, phone, address, password) VALUES (?, ?, ?,?,?)");
            $stmt->bind_param("sssss", $username, $email, $phone, $address, $hashed_password);

            if($stmt->execute()) {
                $new_user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                
                $stmt->close();
                $conn->close();
                header("location: about.php?success=Registration successful! Welcome to Niyo");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIYO Ice Cream Register</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('background ice.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .container { 
            display: flex; 
            justify-content: center;
            align-items: center;
            width: 100%; 
            max-width: 500px;
            background: rgba(236, 177, 177, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3); 
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
        }
        
        .right-panel { 
            width: 100%;
            padding: 50px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
       
            

        
        .logo-icon img {
            width: 110px;
            height: 130px;
            object-fit: cover;
            border-radius: 20px;
              margin-bottom: 15px;
              margin-left: 128px;
            overflow: hidden; 
        }





        .login-form { width: 100%; max-width: 350px; }
        .login-form h2 { 
            color: #333; 
            font-size: 28px; 
            margin-bottom: 30px; 
            text-align: center; 
            font-weight: 700; 
        }

        
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        .login-form input, .login-form textarea { 
            width: 100%; 
            padding: 14px 18px; 
            border: 2px solid #ccc; 
            border-radius: 10px; 
            font-size: 15px; 
            transition: all 0.3s ease; 
            background: rgba(255,255,255,0.9);
        }
        .login-form input:focus, .login-form textarea:focus { 
            outline: none; 
            border-color: #667eea; 
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); 
        }
        .login-form button { 
            width: 100%; 
            padding: 14px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 16px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: transform 0.2s ease; 
            margin-top: 10px; 
        }
        .login-form button:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4); 
        }
        .error { 
            background: #fee; 
            color: #c33; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            font-size: 14px; 
            text-align: center; 
            border: 1px solid #fcc; 
        }
        .success { 
            background: #efe; 
            color: #3c3; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            font-size: 14px; 
            text-align: center; 
            border: 1px solid #cfc; 
        }
        .login-link { 
            text-align: center; 
            margin-top: 20px; 
            color: #333; 
            font-size: 14px; 
            font-weight: 500;
        }
        .login-link a { 
            color: #667eea; 
            text-decoration: none; 
            font-weight: 700; 
        }
        .login-link a:hover { text-decoration: underline; }
        
        @media (max-width: 768px) { 
            .container { max-width: 90%; }
            .right-panel { padding: 40px 25px; } 
        }
    </style>
</head>
<body>
<div class="container">
    <div class="right-panel">
        <form method="POST" class="login-form">
            <h2>🍦 CREATE ACCOUNT</h2>
            <div class="logo-icon">
                <img src=" 2" alt="NIYO Ice Cream Logo">
            
            <?php if(!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            
            <div class="form-group">
                <label for="username">Username </label>
                <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" placeholder="" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="password">Password </label>
                <input type="password" id="password" name="password" placeholder="Min 6 characters" required>
            </div>

            <button type="submit">REGISTER</button>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>





