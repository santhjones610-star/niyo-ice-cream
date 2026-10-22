<?php
session_start();

if(isset($_SESSION['admin']))
{
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html >
<head>

<title>Admin Login - Ice Cream Shop</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,sans-serif;
}

body{
background:#f8c8dc;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.login-box{
width:400px;
background:#fff;
padding:30px;
border-radius:10px;
box-shadow:0 0 15px rgba(0,0,0,0.3);
}

.login-box h2{
text-align:center;
margin-bottom:20px;
color:#ff1493;
}

.input-box{
margin-bottom:20px;
}

.input-box label{
display:block;
margin-bottom:8px;
font-weight:bold;
}

.input-box input{
width:100%;
padding:12px;
border:1px solid #ccc;
border-radius:5px;
font-size:16px;
}

.btn{
width:100%;
padding:12px;
background:#ff1493;
color:white;
border:none;
border-radius:5px;
font-size:18px;
cursor:pointer;
}

.btn:hover{
background:#e60073;
}

.error{
color:red;
text-align:center;
margin-bottom:15px;
font-weight:bold;
}

</style>

</head>

<body>

<div class="login-box">

<h2>Admin Login</h2>

<?php
if(isset($_GET['error']))
{
echo "<div class='error'>Invalid Username or Password</div>";
}
?>

<form action="dashboard.php" method="POST">

<div class="input-box">
<label>Username</label>
<input type="text" name="username" placeholder="Enter Username" required>
</div>

<div class="input-box">
<label>Password</label>
<input type="password" name="password" placeholder="Enter Password" required>
</div>

<input type="submit" value="Login" class="btn">

</form>

</div>

</body>
</html>