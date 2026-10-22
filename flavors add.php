<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
$conn = new mysqli("localhost", "root", "jone", "login ice");
if ($conn->connect_error) die("DB Error: ". $conn->connect_error);

$success = ""; $error = "";
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$categories = ['Cup', 'Cone', 'Coffee Ice', 'Special', 'Vanilla', 'Chocolate','Fruits mix ice']; 

$edit_data = null;


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT image_path FROM flavors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if($row && file_exists($row['image_path']) && strpos($row['image_path'], 'default.jpg') === false) unlink($row['image_path']);
    
    $stmt = $conn->prepare("DELETE FROM flavors WHERE id =?");
    $stmt->bind_param("i", $id);
    echo json_encode(['status' => $stmt->execute()? 'success' : 'error', 'msg'=>$stmt->error]);
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_flavor'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $_POST['category']; 
    $description = trim($_POST['description']?? '');
    $target = "default.jpg";

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $error = "❌ Only JPG, PNG, WEBP images are allowed.";
        } else {
            $clean_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
            $img_name = time(). '_'. $clean_name. '.'.$ext;
            $target = $upload_dir. $img_name;
            if(!move_uploaded_file($_FILES['image']['tmp_name'], $target)) $error = "❌ Upload failed!";
        }
    } else { $error = "❌ Please select an image file!"; }

    if(empty($error)){
        $stmt = $conn->prepare("INSERT INTO flavors (name, category, price, image_path, description, status) VALUES (?,?,?,?,?,'active')");
        $stmt->bind_param("ssdss", $name, $category, $price, $target, $description);
        $success = $stmt->execute()? "✅ Successfully Added: ".htmlspecialchars($name)." - ".$category : "SQL Error: ". $stmt->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_flavor'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $_POST['category']; 
    $description = trim($_POST['description']?? '');
    $old_image = $_POST['old_image'];

    $target = $old_image;

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg','jpeg','png','webp'])) {
            $clean_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
            $img_name = time(). '_'. $clean_name. '.'.$ext;
            $new_target = $upload_dir. $img_name;
            if(move_uploaded_file($_FILES['image']['tmp_name'], $new_target)) {
                if(file_exists($old_image) && strpos($old_image, 'default.jpg') === false) unlink($old_image);
                $target = $new_target;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE flavors SET name=?, category=?, price=?, image_path=?, description=? WHERE id=?");
    $stmt->bind_param("ssdssi", $name, $category, $price, $target, $description, $id);
    $success = $stmt->execute()? "✅ Successfully Updated: ".htmlspecialchars($name) : "SQL Error: ". $stmt->error;
}


if(isset($_GET['edit'])){
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM flavors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

$result = $conn->query("SELECT id, name, category, price, IFNULL(image_path,'default.jpg') as image_path, IFNULL(description,'') as description FROM flavors ORDER BY FIELD(category,'Special','Vanilla','Chocolate','Coffee Ice','Cup','Cone','Fruits'), id DESC");
$total_flavors = $result->num_rows;
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin | Ice Cream Flavors</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root{
  --bg: #0b1020;
  --card: rgba(126, 88, 121, 0.6);
  --border: rgba(148, 163, 184, 0.15);
  --text: #d1d4da;
  --muted: #e7edf4;
  --accent: #7dd3fc;      
  --accent2: #a78bfa;     
  --danger: #f87171;
  --shadow: 0 10px 30px rgba(2, 6, 23, 0.5);
}
*{margin:0; 
padding:0; 
box-sizing:border-box;
}

body{font-family:'Segoe UI',sans-serif;
background: radial-gradient(1200px 800px at 80% -10%, #1e3a8a33, transparent),radial-gradient(1000px 600px at -10% 110%, #7c3aed22, transparent),var(--bg);
color:var(--text);
padding:24px;
min-height:100vh;
}

.container{max-width:1200px;
 margin:0 auto;
}

.header{background:var(--card);
backdrop-filter:blur(14px);
padding:20px 24px;
border-radius:16px;
margin-bottom:24px;
display:flex;
 justify-content:space-between;
  align-items:center;
  border:1px solid var(--border);box-shadow:var(--shadow);
}

.header h1{font-size:24px;
background:linear-gradient(90deg,var(--accent),var(--accent2));
-webkit-background-clip:text;
background-clip:text;
color:transparent;
font-weight:800;
}

.back-btn{padding:10px 18px;
background:linear-gradient(90deg,var(--accent),var(--accent2));
color:#0f172a;
text-decoration:none;
border-radius:10px;
font-weight:700;
transition:0.2s;
border:none;}

.back-btn:hover{transform:translateY(-2px);
 box-shadow:0 8px 20px rgba(125,211,252,0.3);
}

.card{background:var(--card);
backdrop-filter:blur(14px);
border-radius:16px;
padding:24px;
margin-bottom:24px;
border:1px solid var(--border);
box-shadow:var(--shadow);
}

.card h2{font-size:20px;
 margin-bottom:20px; 
 color:var(--accent); 
 font-weight:700; 
 text-align:center;
}

.message{padding:14px 18px;
 border-radius:12px;
  margin-bottom:20px;
   font-weight:600; 
   display:flex;
    align-items:center;
     gap:10px;
      border:1px solid;
       backdrop-filter:blur(6px);
    }

.error{background:rgba(239,68,68,0.15);
 color:#fecaca; 
 border-color:rgba(239,68,68,0.3);
}

.success{background:rgba(34,197,94,0.15); 
color:#bbf7d0;
 border-color:rgba(34,197,94,0.3);
}

.form-grid{display:grid; 
grid-template-columns:repeat(3, 1fr);
 gap:20px;
} 


.form-group{margin-bottom:18px;
}

.form-group label{display:block;
 font-weight:600; 
 margin-bottom:8px;
  color:var(--muted);
   font-size:14px;
}

.form-group input,.form-group textarea,.form-group select{width:100%;
 padding:12px 14px;
  border:1px solid var(--border);
  background:rgba(2,6,23,0.4); 
  color:var(--text);
  border-radius:10px;
   font-size:14px;
    transition:0.2s;
} 

.form-group input::placeholder, .form-group textarea::placeholder{color:#64748b;
}

.form-group input:focus,.form-group textarea:focus,.form-group select:focus{outline:none; 
border-color:var(--accent);
box-shadow:0 0 0 3px rgba(125,211,252,0.2);
background:rgba(2,6,23,0.6);
}

.file-input-wrapper{position:relative;
 overflow:hidden;
  display:inline-block;
  width:100%;
}

.file-input-wrapper input[type=file]{position:absolute;
 left:0; 
 top:0;
  opacity:0;
   width:100%; 
   height:100%;
    cursor:pointer;
}

.file-input-label{display:block;
 padding:12px 14px; 
 border:2px dashed var(--border);background:rgba(2,6,23,0.4); 
 color:var(--muted);
 border-radius:10px;
  text-align:center;
   transition:0.2s; 
   cursor:pointer;
}

.file-input-wrapper:hover .file-input-label{border-color:var(--accent); 
color:var(--accent);
 background:rgba(125,211,252,0.05);
}

.add-btn{padding:14px 20px;
 width:100%;
 background:linear-gradient(90deg,var(--accent),var(--accent2));
 color:#0f172a;
  border:none; 
  border-radius:10px;
  font-weight:800; 
  cursor:pointer; 
  font-size:15px;
   transition:0.2s;
}

.add-btn:hover{transform:translateY(-2px);
 box-shadow:0 8px 20px rgba(167,139,250,0.3);
}

.table-wrapper{overflow-x:auto;
 border-radius:12px;
  border:1px solid var(--border);
}

.flavors-table{width:100%; 
border-collapse:collapse; 
min-width:800px; 
background:rgba(2,6,23,0.3);
} 

.flavors-table thead{background:rgba(15,23,42,0.8);
}

.flavors-table th{padding:14px;
 text-align:left; 
 font-weight:700; 
 color:var(--muted); 
 font-size:12px;
  text-transform:uppercase; 
  letter-spacing:0.5px;
   border-bottom:1px solid var(--border);

}

.flavors-table td{padding:14px; 
border-bottom:1px solid var(--border);
}

.flavors-table tbody tr{transition:0.2s;
}
.flavors-table tbody tr:hover{background:rgba(125,211,252,0.05);
}
.flavor-img-thumb{width:60px;
 height:60px;
  object-fit:cover;
   border-radius:10px;
    border:2px solid var(--border);
     background:#0f172a;
    }

.btn{padding:8px 14px;
 border-radius:8px; 
 cursor:pointer;
  font-weight:700;
   transition:0.2s;
    border:1px solid;
     margin-right:5px;
    }

.edit-btn{background:rgba(125,211,252,0.12); 
color:var(--accent);
 border-color:rgba(125,211,252,0.3);
}

.edit-btn:hover{background:var(--accent); 
color:#0f172a;
 transform:scale(1.05);
}

.delete-btn{background:rgba(239,68,68,0.12);
 color:var(--danger); 
 border-color:rgba(239,68,68,0.3);
}

.delete-btn:hover{background:var(--danger); 
color:white; 
transform:scale(1.05);
}

.fade-out{opacity:0; 
transform:scale(0.95);
 transition:all 0.4s ease;
}

.total-count{margin-top:20px;
 text-align:center;
  color:var(--muted);
   font-weight:600;
}

.category-badge{display:inline-block;
 padding:4px 10px;
  border-radius:20px;
  font-size:12px;
   font-weight:800;
    background:linear-gradient(90deg,var(--accent2),var(--accent));
    color:#0f172a;
    border:1px solid rgba(255,255,255,0.1);
}
.preview-img{width:80px;
 height:80px; 
 object-fit:cover;
  border-radius:10px;
   margin-top:10px; border:2px solid var(--border);}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🍦 Flavors add</h1>
        <a href="flavors.php" class="back-btn">← Back to Shop</a>
    </div>

    <div class="card">
        <h2> <?= $edit_data ? '✏️ Edit Flavor' : 'Add New Flavor' ?> </h2>
        <?php if($error):?><div class="message error">⚠️ <?=$error?></div><?php endif;?>
        <?php if($success):?><div class="message success">✅ <?=$success?></div><?php endif;?>
        
        <form method="POST" enctype="multipart/form-data">
            <?php if($edit_data): ?>
                <input type="hidden" name="id" value="<?=$edit_data['id']?>">
                <input type="hidden" name="old_image" value="<?=$edit_data['image_path']?>">
            <?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group"><label>Flavor Name</label><input type="text" name="name" value="<?= $edit_data['name']??'' ?>" required></div>
                <div class="form-group"><label>Category</label> 
                    <select name="category" required>
                        <?php foreach($categories as $cat):?>
                            <option value="<?=$cat?>" <?= ($edit_data['category']??'')==$cat?'selected':'' ?>><?=$cat?></option>
                        <?php endforeach;?>
                    </select>
                </div>
                <div class="form-group"><label>Price (Rs.)</label><input type="number" name="price" value="<?= $edit_data['price']??'' ?>" step="0.01" required></div>
            </div>
            
            <div class="form-group">
                <label>Flavor Image <?= $edit_data ? '(Change pannuna mattum select pannu)' : '*' ?></label>
                <div class="file-input-wrapper">
                    <input type="file" name="image" accept="image/*" id="imageUpload" <?= $edit_data?'':'required' ?>>
                    <label for="imageUpload" class="file-input-label" id="fileLabel">Click to upload JPG, PNG, WEBP</label>
                </div>
                <?php if($edit_data && $edit_data['image_path']): ?>
                    <img src="<?=htmlspecialchars($edit_data['image_path'])?>" class="preview-img">
                <?php endif; ?>
            </div>
            
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= $edit_data['description']??'' ?></textarea></div>
            
            <?php if($edit_data): ?>
                <button type="submit" name="update_flavor" class="add-btn">Update Flavor</button>
                <a href="?" class="add-btn" style="display:block; text-align:center; margin-top:10px; text-decoration:none; background:linear-gradient(90deg,#64748b,#94a3b8);">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_flavor" class="add-btn">Add Flavor</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>📋 All Flavors</h2>
        <div class="table-wrapper">
        <table class="flavors-table">
            <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>DB Path</th><th>Action</th></tr></thead>
            <tbody id="flavorsBody">
            <?php if($result->num_rows>0): while($row=$result->fetch_assoc()):?>
            <tr id="row-<?=$row['id']?>">
                <td><img src="<?=htmlspecialchars($row['image_path'])?>" class="flavor-img-thumb" onerror="this.src='default.jpg';"></td>
                <td><strong><?=htmlspecialchars($row['name'])?></strong></td>
                <td><span class="category-badge"><?=htmlspecialchars($row['category'])?></span></td>
                <td>Rs.<?=number_format($row['price'],2)?></td>
                <td style="font-size:12px;font-family:monospace;color:var(--muted);word-break:break-all;"><?=htmlspecialchars($row['image_path'])?></td>
                <td>
                    <a href="?edit=<?=$row['id']?>" class="btn edit-btn">Edit</a>
                    <button class="btn delete-btn" onclick="deleteFlavor(<?=$row['id']?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; else:?>
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">No flavors yet. Add your first one above! 👆</td></tr>
            <?php endif;?>
            </tbody>
        </table>
        </div>
        <p class="total-count">💝 Total Flavors: <span id="totalCount"><?=$total_flavors?></span></p>
    </div>
</div>

<script>
document.getElementById('imageUpload').addEventListener('change',function(e){
    document.getElementById('fileLabel').textContent = e.target.files[0]? e.target.files[0].name : 'Click to upload JPG, PNG, WEBP';
});

function deleteFlavor(id){
    if(confirm('Are you sure you want to delete this flavor? The image will also be deleted!')){
        fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=delete&id='+id})
       .then(r=>r.json())
       .then(d=>{
            if(d.status==='success'){
                const row=document.getElementById('row-'+id);
                row.classList.add('fade-out');
                setTimeout(()=>{
                    row.remove();
                    const count=document.querySelectorAll('#flavorsBody tr[id^="row-"]').length;
                    document.getElementById('totalCount').innerText=count;
                    if(count===0)document.getElementById('flavorsBody').innerHTML='<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">No flavors yet. Add your first one above! 👆</td></tr>';
                },400);
            }else{alert('Error: '+d.msg);}
        });
    }
}
</script>
</body></html>
<?php $conn->close();?>