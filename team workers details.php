<?php
session_start();
$conn = new mysqli("localhost", "root", "jone", "login ice");

if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

$success = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM team_members WHERE id =?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
    }
    $stmt->close();
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $phone = trim($_POST['phone']);
    $work = trim($_POST['work']);

    $stmt = $conn->prepare("UPDATE team_members SET name=?, role=?, phone=?, work=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $role, $phone, $work, $id);

    if($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
    }
    $stmt->close();
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $phone = trim($_POST['phone']);
    $work = trim($_POST['work']);

    if(empty($name) || empty($role) || empty($phone) || empty($work)) {
        $error = "All fields are required";
    } else {
        $stmt = $conn->prepare("INSERT INTO team_members (name, role, phone, work) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $role, $phone, $work);

        if($stmt->execute()) {
            $success = "Team member added successfully! 🎉";
            $_POST = array();
        } else {
            $error = "Error: ". $stmt->error;
        }
        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM team_members ORDER BY id ASC");
$total_members = $result->num_rows;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Details - Niyo Ice Cream</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c4d70 0%, #8b6153 100%);
            min-height: 100vh;
        }
      .navbar {
            background: white;
             padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
             justify-content: space-between; 
             align-items: center;
        }
      .logo {
            font-size: 24px; 
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
        }
      .nav-links a {
            margin-left: 20px; 
            text-decoration: none;
             color: #333;
            font-weight: 600; 
            font-size: 14px;
        }
      .nav-links a:hover,.nav-links a.active { color: #667eea; 
    }
      .hero {
            text-align: center;
             padding: 60px 20px;
              color: white;
        }
      .hero h1 {
            font-size: 42px;
             margin-bottom: 15px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
        }
      .hero p {
            font-size: 18px;
             opacity: 0.95;
            max-width: 600px;
             margin: 0 auto;
        }
      .container {
            max-width: 1200px;
             margin: -40px auto 40px;
              padding: 0 20px;
        }
      .card {
            background: white;
             border-radius: 16px; 
             padding: 40px;
            margin-bottom: 25px;
             box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
      .card h2 {
            color: #667eea;
             font-size: 28px;
              margin-bottom: 20px;
            display: flex; 
            align-items: center;
             gap: 10px;
        }
      .message {
            padding: 14px 18px;
             border-radius: 12px;
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
      .team-table {
            width: 100%;
             border-collapse: collapse; 
             margin-top: 25px;
            overflow: hidden; 
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
      .team-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
      .team-table th {
            padding: 16px; 
            text-align: left; 
            font-weight: 700; 
            font-size: 15px;
        }
      .team-table td {
            padding: 16px; 
            border-bottom: 1px solid #f0f0f0;
            color: #3e5968; 
            font-size: 15px;
        }
      .team-table tbody tr { transition: all 0.3s ease; }
      .team-table tbody tr:hover { background: #f8f9ff; }
      .team-table tbody tr:last-child td { border-bottom: none; }
      .team-table input {
            width: 100%; 
            padding: 8px;
             border: 1px solid #ddd;
            border-radius: 6px;
             font-size: 14px;
        }
      .role-badge {
            display: inline-block; 
            padding: 6px 12px; 
            background: #e8eaf6;
            color: #667eea;
             border-radius: 20px; 
             font-size: 13px;
              font-weight: 600;
        }

      
      .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: nowrap;
       }
      .btn {
            padding: 7px 14px; 
            border-radius: 6px;
             text-decoration: none;
            font-size: 12px; 
            font-weight: 600;
             border: none; 
             cursor: pointer;
            white-space: nowrap;
        }
      .edit-btn { background: #4361ee;
       color: white;
     }
      .edit-btn:hover { background: #3a56d4;
     }
      .save-btn { background: #10b981;
       color: white; 
       display: none; 
    }
      .save-btn:hover { background: #059669; 
    }
      .cancel-btn { background: #9ca3af;
       color: white;
        display: none; 
    }
      .cancel-btn:hover { background: #6b7280; 
    }
      .delete-btn { background: #ff5252;
       color: white; 
    }
      .delete-btn:hover { background: #e53935;
     }

      .add-form {
            background: #f8f9ff;
             padding: 25px; 
             border-radius: 12px;
              margin-bottom: 30px;
        }
      .form-grid {
            display: grid;
             grid-template-columns: 1fr 1fr 1fr 2fr auto; 
             gap: 15px; 
             align-items: end;
        }
      .form-group label {
            display: block;
             color: #3e5968; 
             font-size: 13px;
            font-weight: 600;
             margin-bottom: 6px;
        }
      .form-group input {
            width: 100%; 
            padding: 10px 14px;
             border: 2px solid #e0e0e0;
            border-radius: 8px;
             font-size: 14px;
              font-family: inherit;
        }
      .form-group input:focus {
            outline: none; 
            border-color: #667eea;
        }
      .add-btn {
            padding: 10px 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
             border: none;
              border-radius: 8px;
               font-weight: 700;
            cursor: pointer;
             font-size: 14px;
        }
      .add-btn:hover { transform: translateY(-2px); }
      .total-count {
            margin-top: 25px; 
            text-align: center;
             color: #667eea;
            font-weight: 600; 
            font-size: 16px;
        }
      .fade-out { opacity: 0; 
      transition: opacity 0.5s ease; }
       
        
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">🍦 Niyo ice cream shop</div>
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

    <div class="hero">
        <h1>OUR TEAM</h1>
        <p>Meet the Amazing People Behind Niyo Ice Cream</p>
    </div>

    <div class="container">
        <div class="card">
            <h2>👨‍🍳 employees</h2>

            <?php if(!empty($error)):?>
                <div class="message error"><?php echo $error;?></div>
            <?php endif;?>

            <?php if(!empty($success)):?>
                <div class="message success"><?php echo $success;?></div>
            <?php endif;?>

            <div class="add-form">
                <h3 style="color: #667eea; margin-bottom: 15px;">Add New Team Member</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" placeholder="" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" name="role" placeholder="" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" placeholder="07x xxx xxxx" required>
                        </div>
                        <div class="form-group">
                            <label>Work / Responsibility</label>
                            <input type="text" name="work" placeholder="" required>
                        </div>
                        <button type="submit" name="add_member" class="add-btn">Add Member</button>
                    </div>
                </form>
            </div>

            <table class="team-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Work / Responsibility</th>
                        <th style="width: 200px;">Action</th>
                    </tr>
                </thead>
                <tbody id="teamBody">
                    <?php if($result->num_rows > 0):?>
                        <?php while($row = $result->fetch_assoc()):?>
                        <tr id="row-<?php echo $row['id'];?>">
                            <td><span class="view-mode"><?php echo htmlspecialchars($row['name']);?></span><input type="text" class="edit-mode" value="<?php echo htmlspecialchars($row['name']);?>" style="display:none;"></td>
                            <td><span class="view-mode"><span class="role-badge"><?php echo htmlspecialchars($row['role']);?></span></span><input type="text" class="edit-mode" value="<?php echo htmlspecialchars($row['role']);?>" style="display:none;"></td>
                            <td><span class="view-mode"><?php echo htmlspecialchars($row['phone']);?></span><input type="text" class="edit-mode" value="<?php echo htmlspecialchars($row['phone']);?>" style="display:none;"></td>
                            <td><span class="view-mode"><?php echo htmlspecialchars($row['work']);?></span><input type="text" class="edit-mode" value="<?php echo htmlspecialchars($row['work']);?>" style="display:none;"></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn edit-btn" onclick="editRow(<?php echo $row['id'];?>)">Edit</button>
                                    <button class="btn save-btn" onclick="saveRow(<?php echo $row['id'];?>)">Save</button>
                                    <button class="btn cancel-btn" onclick="cancelEdit(<?php echo $row['id'];?>)">Cancel</button>
                                    <button class="btn delete-btn" onclick="deleteMember(<?php echo $row['id'];?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile;?>
                    <?php else:?>
                        <tr id="noData">
                            <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                                No team members yet. Add your first member above! 👆
                            </td>
                        </tr>
                    <?php endif;?>
                </tbody>
            </table>

            <p class="total-count" id="totalCount">
                💝 Total Team Members: <?php echo $total_members;?>
            </p>
        </div>
    </div>

    <script>
    function editRow(id) {
        const row = document.getElementById('row-' + id);
        row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
        row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'block');
        row.querySelector('.edit-btn').style.display = 'none';
        row.querySelector('.save-btn').style.display = 'inline-block';
        row.querySelector('.cancel-btn').style.display = 'inline-block';
    }

    function cancelEdit(id) {
        const row = document.getElementById('row-' + id);
        row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'block');
        row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
        row.querySelector('.edit-btn').style.display = 'inline-block';
        row.querySelector('.save-btn').style.display = 'none';
        row.querySelector('.cancel-btn').style.display = 'none';
    }

    function saveRow(id) {
        const row = document.getElementById('row-' + id);
        const inputs = row.querySelectorAll('.edit-mode');

        const data = {
            action: 'update',
            id: id,
            name: inputs[0].value,
            role: inputs[1].value,
            phone: inputs[2].value,
            work: inputs[3].value
        };

        fetch(window.location.pathname, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(data)
        })
      .then(response => response.json())
      .then(data => {
            if(data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.msg);
            }
        });
    }

    function deleteMember(id) {
        if(confirm('Delete this member?')) {
            fetch(window.location.pathname, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete&id=' + id
            })
          .then(response => response.json())
          .then(data => {
                if(data.status === 'success') {
                    const row = document.getElementById('row-' + id);
                    row.classList.add('fade-out');
                    setTimeout(() => {
                        row.remove();
                        let count = document.querySelectorAll('#teamBody tr[id^="row-"]').length;
                        document.getElementById('totalCount').innerText = '💝 Total Team Members: ' + count;

                        if(count === 0) {
                            document.getElementById('teamBody').innerHTML = `
                                <tr id="noData">
                                    <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                                        No team members yet. Add your first member above! 👆
                                    </td>
                                </tr>
                            `;
                        }
                    }, 500);
                } else {
                    alert('Error: ' + data.msg);
                }
            })
          .catch(error => alert('Error: ' + error));
        }
    }
    </script>
</body>
</html>
<?php $conn->close();?>