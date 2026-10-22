<?php
session_start();
$conn = new mysqli("localhost", "root", "jone", "login ice");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$is_locked = false;
$remaining_time = 0;
$lockout_time = '';
$user_ip = $_SERVER['REMOTE_ADDR'];

if ($conn && !$conn->connect_error) {
    $lock_check = $conn->prepare("SELECT lockout_until FROM login_attempts WHERE ip_address = ? AND lockout_until > NOW()");
    $lock_check->bind_param("s", $user_ip);
    $lock_check->execute();
    $lock_result = $lock_check->get_result();
    
    if($lock_result->num_rows > 0) {
        $is_locked = true;
        $row = $lock_result->fetch_assoc();
        $lockout_until = strtotime($row['lockout_until']);
        $remaining_time = $lockout_until - time();
        $lockout_time = date('h:i A', $lockout_until);
    }
}

if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php?success=Logged out successfully");
    exit();
}

$success = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_locked) {
    $name = trim($_POST['name']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    if(empty($name) || empty($subject) || empty($message)) {
        $error = "All fields are required";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, subject, message, ip_address) VALUES (?, ?, ?,?)");
        $stmt->bind_param("ssss", $name, $subject, $message, $ip_address);
        
        if($stmt->execute()) {
            $success = "Thank you! Your message has been sent successfully 🍦";
            $_POST = array(); // Form clear aagum
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<head>
    <title>Contact Us - NIYO Ice Cream</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #0f0f1e;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, #ff6b9d, #c06c84);
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.25;
            top: -200px;
            left: -200px;
            z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.25;
            bottom: -150px;
            right: -150px;
            z-index: 0;
        }
        .navbar {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        .nav-links a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s ease;
        }
        .nav-links a:hover { color: #ff6b9d; }
        .nav-links a.active { color: #ff6b9d; }
        .btn-logout {
            padding: 8px 18px;
            background: linear-gradient(135deg, #ff5252, #ff1744);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 82, 82, 0.4);
        }
        .hero {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 80px 20px 60px;
        }
        .hero h1 {
            color: #fff;
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        .hero p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
        }
        .container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr; 
            gap: 40px;
            align-items: start;
        }
        .contact-info {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
        }
        .contact-info h2 {
            color: #fff;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: 700;
        }
        .info-item {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            align-items: start;
        }
        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff6b9d, #ff8fab);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.3);
        }
        .info-content h3 {
            color: #fff;
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .info-content p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            line-height: 1.6;
        }
        .info-content a {
            color: #ff6b9d;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .info-content a:hover { color: #ff8fab; }
        .location-image-box {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .location-image-box h3 {
            color: #fff;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .location-image-link {
            display: block;
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        .location-image-link:hover {
            transform: translateY(-5px);
            border-color: #ff6b9d;
            box-shadow: 0 12px 30px rgba(255, 107, 157, 0.4);
        }
        .location-image-link img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }
        .location-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 15px;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        .social-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .social-section h3 {
            color: #fff;
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .social-images {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .social-link {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.1);
            display: block;
        }
        .social-link:hover {
            transform: translateY(-5px);
            border-color: #ff6b9d;
            box-shadow: 0 8px 25px rgba(255, 107, 157, 0.4);
        }
        .social-link img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .contact-form { 
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
        }
        .contact-form h2 {
            color: #fff;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: 700;
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
        .form-group input,
        .form-group textarea {
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
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        .form-group input:disabled,
        .form-group textarea:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff6b9d, #ff8fab);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.3);
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255, 107, 157, 0.5);
        }
        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .message {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
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
        .map-section {
            margin-top: 40px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            padding: 30px;
            text-align: center;
        }
        .map-section h3 {
            color: #fff;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .lockout-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 15, 30, 0.9);
            backdrop-filter: blur(15px);
            z-index: 999;
            display: <?php echo $is_locked ? 'block' : 'none'; ?>;
        }
        .lockout-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: -10px 0 40px rgba(0,0,0,0.5);
            z-index: 1000;
            padding: 40px 30px;
            overflow-y: auto;
            animation: slideInRight 0.4s ease;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        .lockout-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .lock-icon-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff5252, #ff1744);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(255, 82, 82, 0.4);
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }
        .lockout-header h2 {
            color: #fff;
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .lockout-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            line-height: 1.6;
        }
        .timer-box-lockout {
            background: rgba(255, 82, 82, 0.15);
            border: 2px solid rgba(255, 82, 82, 0.3);
            border-radius: 16px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .timer-label-lockout {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .timer-value-lockout {
            color: #ff5252;
            font-size: 36px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        .unlock-time-lockout {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            margin-top: 8px;
        }
        .info-box-lockout {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 18px;
            margin: 20px 0;
        }
        .info-box-lockout h3 {
            color: #fff;
            font-size: 15px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .info-box-lockout ul {
            list-style: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            line-height: 1.8;
        }
        .info-box-lockout ul li {
            padding-left: 18px;
            position: relative;
            margin-bottom: 6px;
        }
        .info-box-lockout ul li:before {
            content: '→';
            position: absolute;
            left: 0;
            color: #ff6b9d;
            font-weight: 700;
        }
        .btn-close-all {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff5252, #ff1744);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(255, 82, 82, 0.3);
            margin-top: 20px;
        }
        .btn-close-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255, 82, 82, 0.5);
        }
    </style>
</head>
<body>
    <?php if($is_locked): ?>
    <div class="lockout-overlay" id="lockoutOverlay">
        <div class="lockout-panel"> 
            <div class="lockout-header">
                <div class="lock-icon-large">🔒</div>
                <h2>Account Locked</h2>
                <p>Too many failed login attempts. Your access is temporarily restricted.</p>
            </div>
            <div class="timer-box-lockout">
                <div class="timer-label-lockout">Time Remaining</div>
                <div class="timer-value-lockout" id="countdownLockout">
                    <?php 
                    $minutes = floor($remaining_time / 60);
                    $seconds = $remaining_time % 60;
                    echo sprintf('%02d:%02d', $minutes, $seconds);
                    ?>
                </div>
                <div class="unlock-time-lockout">Unlocks at <?php echo $lockout_time; ?></div>
            </div>
            <div class="info-box-lockout">
                <h3>⚠️ What Happened?</h3>
                <ul>
                    <li>Multiple incorrect password attempts detected</li>
                    <li>Account locked for 15 minutes for security</li>
                    <li>Wait for timer or close this page</li>
                    <li>Contact support if needed</li>
                </ul>
            </div>
            <button onclick="closeEverything()" class="btn-close-all">✖ Close All & Exit</button>
        </div>
    </div>
    <?php endif; ?>

    <nav class="navbar">
        <a href="about.php" class="logo">🍦 NIYO Ice Cream shop</a>
        <div class="nav-links">
           <a href="about.php">About</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="flavors.php">Shop</a>
            <a href="team workers details.php">employees</a>
            <a href="contact.php">Contact</a>
            <button onclick="logoutAndClose()" class="btn-logout">Logout ✖</button>
        </div>
    </nav>

    <div class="hero">
        <h1>Get In Touch</h1>
        <p>Have a question or craving? We're here to make your day sweeter!</p>
    </div>

    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Contact Information</h2>
                <div class="info-item">
                    <div class="info-icon">📍</div>
                    <div class="info-content">
                        <h3>Visit Us</h3>
                        <p>NO-207, main street<br> mannar</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">📞</div>
                    <div class="info-content">
                        <h3>Call Us</h3>
                        <p><a href="tel:0777851296">0777851296</a><br>Mon-Sun: 9:00AM - 10:00PM</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">⏰</div>
                    <div class="info-content">
                        <h3>Opening Hours</h3>
                        <p>Monday - Sunday<br>10:00 AM - 8:30 PM</p>
                    </div>
                </div>
                <div class="location-image-box">
                    <h3>📍 Our Location</h3>
                    <a href="https://maps.app.goo.gl/whoukDrkURuPTJwd7?g_st=aw" target="_blank" class="location-image-link">
                        <img src="location.png" alt="NIYO Ice Cream Location">
                        <div class="location-overlay">Click to Open in Google Maps 🗺️</div>
                    </a>
                </div>
                <div class="social-section">
                    
                </div>
            </div>

            
            <div class="contact-form">
                <h2>Send Us a Message</h2>
                
                <?php if(!empty($error)): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($success)): ?>
                    <div class="message success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="name" placeholder="" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required <?php echo $is_locked ? 'disabled' : ''; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" placeholder="" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required <?php echo $is_locked ? 'disabled' : ''; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" placeholder="" required <?php echo $is_locked ? 'disabled' : ''; ?>><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn" <?php echo $is_locked ? 'disabled' : ''; ?>>Send Message 🍦</button>
                </form>
            </div>
        </div>

        
    </div>

    <?php if($is_locked): ?>
    <script>
        let remainingSeconds = <?php echo $remaining_time; ?>;
        function updateCountdown() {
            if (remainingSeconds <= 0) {
                window.location.href = 'login.php?success=Account unlocked! You can try again';
                return;
            }
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            document.getElementById('countdownLockout').textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            remainingSeconds--;
        }
        setInterval(updateCountdown, 1000);
        function closeEverything() {
            window.open('', '_self', '');
            window.close();
            setTimeout(function() {
                window.location.href = 'about:blank';
            }, 100);
        }
    </script>
    <?php endif; ?>

    <script>
        function logoutAndClose() {
            if(confirm('Are you sure you want to logout and close?')) {
                window.location.href = 'contact.php?logout=1';
            }
        }
    </script>
</body>
</html>