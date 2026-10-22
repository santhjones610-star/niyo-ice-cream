<?php
session_start();
$order_success = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Niyo Ice Cream</title>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
            
           
            background: url('background ice.jpeg') no-repeat center fixed;
            background-size: cover;
        }
        
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4); 
            backdrop-filter: blur(1px);
            z-index: -1;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
            position: relative;
            z-index: 1;
        }
        .logo {
            font-size: 24px; font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logo img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
        .nav-links a {
            margin-left: 20px;
            text-decoration: none; 
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        .nav-links a:hover { color: #667eea; }
        .nav-links a.active { color: #667eea; }
        .success-banner {
            background: #4CAF50; color: white; text-align: center;
            padding: 15px; font-weight: 600; font-size: 16px;
            position: relative;
            z-index: 1;
        }
        .hero {
            text-align: center; padding: 60px 20px; color: white;
            position: relative;
            z-index: 1;
        }
        .hero h1 {
            font-size: 42px; margin-bottom: 15px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
        }
        .hero p {
            font-size: 18px; opacity: 0.95;
            max-width: 600px; margin: 0 auto;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.4);
        }
        .container {
            max-width: 1100px; margin: -40px auto 40px; padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        .card {
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px);
            border-radius: 16px; padding: 40px;
            margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .card h2 {
            color: #667eea; font-size: 28px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .card p {
            color: #3e5968; 
            line-height: 1.8;
            font-size: 16px; 
            margin-bottom: 15px;
        }
        .features {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px; margin-top: 30px;
        }
        .feature {
            text-align: center; padding: 25px; background: #f8f9ff;
            border-radius: 12px; transition: transform 0.3s ease;
        }
        .feature:hover { transform: translateY(-5px); }
        .feature-icon { font-size: 48px; margin-bottom: 15px; }
        .feature h3 { color: #333; font-size: 18px; margin-bottom: 10px; }
        .feature p { color: #666; font-size: 14px; line-height: 1.6; }
        .btn {
            display: inline-block; padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; text-decoration: none; border-radius: 10px;
            font-weight: 700; margin-top: 20px; transition: transform 0.2s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 32px; }
            .hero p { font-size: 16px; }
            .card { padding: 25px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <div class="logo">🍦 Niyo Ice Cream shop</div>
        </div>
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

    <?php if($order_success > 0): ?>
    <div class="success-banner">
        ✅ Order #<?php echo $order_success; ?> Successfully Placed! Thank you for choosing Niyo Ice Cream
    </div>
    <?php endif; ?>

    <div class="hero">
        <h1>WELCOME TO NIYO ICE CREAM</h1>
        <p>Where Every Scoop Tells a Story of Happiness</p>
    </div>

    <div class="container">
        <div class="card">
            <h2>🍦 Our Story</h2>
            <p>
               Niyo Ice Cream was started in 2024 by Godwin Wimalrajai with a simple dream to bring joy to the people of Mannar,
                Sri Lanka through the magic of ice cream. 
                What started as a small family business has now grown to become your favorite ice cream hub in your neighborhood.
            </p>
            <p>
                Ice cream is not just a dessert but also a delicious treat made using only the finest ingredients sourced locally and internationally.
                 Made with a variety of nutritious ingredients such as fruits, milk, classic vanilla, choco and more. 
                 For those who can't come and enjoy in person, you can order and enjoy.
                 From exotic tropical flavors, we serve over 30+ varieties to satisfy every craving.
            </p>
            <p>
                And a way to create sweet memories with your loved ones. Every flavor we create is made with love, 
                Whether it's a hot summer day or a cool evening, Niyo Ice Cream is here to sweeten your day
            </p>
            <a href="flavors.php" class="btn">Order Now 🍦</a>
        </div>

        <div class="card">
            <h2>✨ Why Choose Niyo?</h2>
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">🥛</div>
                    <h3>Fresh Milk</h3>
                    <p>We use only fresh, pure milk from local farms for creamy, rich texture</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🌿</div>
                    <h3>Natural Ingredients</h3>
                    <p>No artificial colors or preservatives. Just pure, natural goodness</p>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">🍇</div>
                    <h3>Fresh Fruits</h3>
                    <p>Hand-Picked Fruits Fruit Pieces in Every Scoop 
                        We blend mango, strawberry, apple, pineapple, papaya, sourced daily from local farms for a natural sweetness and flavor 
                        No artificial flavors added</p>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">💝</div>
                    <h3>Made with Love</h3>
                    <p>Every batch is handcrafted with passion and attention to detail</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>