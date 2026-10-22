<?php
session_start();
$conn = new mysqli("localhost", "root", "jone", "login ice");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if(isset($_GET['success'])){
    $success = htmlspecialchars($_GET['success']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)) {
        $error = "Username and Password are required";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM register WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: about.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Username not found";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html >
<head>
    
    <title>NIYO Ice Cream - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            
            background: url('background ice.jpeg') no-repeat center fixed;
            background-size: cover;
        }
       
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(15, 15, 30, 0.7); 
            backdrop-filter: blur(2px);
            z-index: 0;
        }
        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #ff6b9d, #c06c84);
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.3;
            top: -200px;
            left: -200px;
            z-index: 0;
        }
       
        .bg-blur-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.3;
            bottom: -150px;
            right: -150px;
            z-index: 0;
        }
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo-icon {
            width: 90px;
            height: 100px;
            background: linear-gradient(135deg, #ff6b9d, #ff8fab);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 8px 25px rgba(255, 107, 157, 0.4);
            overflow: hidden; 
        }
        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .logo-section h1 {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .logo-section p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            font-weight: 300;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff6b9d, #ff8fab);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.3);
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255, 107, 157, 0.5);
        }
        .login-btn:active {
            transform: translateY(0);
        }
        .message {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }
        .error {
            background: rgba(255, 82, 82, 0.15);
            color: #ff5252;
            border: 1px solid rgba(255, 82, 82, 0.3);
        }
        .success {
            background: rgba(76, 175, 80, 0.15);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        .divider span {
            position: relative;
            background: #1a1a2e;
            padding: 0 15px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }
        .register-text {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }
        .register-text a {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .register-text a:hover {
            color: #ff8fab;
        }
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 30px;
            }
            .logo-section h1 {
                font-size: 24px;
            }
        }
        
    </style>
</head>
<body>
    <div class="bg-blur-2"></div> 

    <div class="login-card">
        <div class="logo-section">
            <div class="logo-icon">
                <img src=" 2" alt="NIYO Ice Cream Logo">
            </div>
            <h1>NIYO Ice Cream</h1>
            <p>Welcome back! Please login to continue</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">Login to Account</button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="register-text">
            Don't have an account? <a href="register.php">register here</a>
        </div>
    </div>
</body>
</html>