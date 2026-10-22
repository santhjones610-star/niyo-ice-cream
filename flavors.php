<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$conn = new mysqli("localhost", "root", "jone", "login ice");
if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

$categories = ['Cup', 'Cone', 'Coffee Ice', 'Special', 'Vanilla', 'Chocolate','fruits mix ice'];
$active_cat = $_GET['cat']?? 'all';

if($active_cat == 'all') {
    $result = $conn->query("SELECT * FROM flavors WHERE status='active' ORDER BY category, id DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM flavors WHERE status='active' AND category =? ORDER BY id DESC");
    $stmt->bind_param("s", $active_cat);
    $stmt->execute();
    $result = $stmt->get_result();
}

$flavors = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Our Flavors - NIYO ICE CREAM SHOP</title>
 
  <style>
    * { margin: 0;
     padding: 0; 
     box-sizing: border-box; 
     font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
     }
    body { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); 
    min-height: 100vh;
     padding-top: 70px;
     }

  .navbar { position: fixed; 
  top: 0;
   left: 0; 
   right: 0; 
   background: white;
    padding: 15px 26px;
     box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      display: flex;
       justify-content: space-between;
        align-items: center;
         z-index: 9999;
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

  .nav-links { display: flex;
   align-items: center;
   }

  .nav-links a { margin-right: 20px;
   text-decoration: none;
    color: #333; 
    font-weight: 600;
     font-size: 14px; 
     transition: color 0.3s ease;
     }

  .nav-links a:hover,.nav-links a.active { color: #667eea; 
}
  .cart { position: fixed;
   top: 15px;
    right: 20px;
     cursor: pointer;
      font-size: 1.3rem;
       color: #667eea; 
       z-index: 10000; 
       background: white; 
       padding: 12px;
        border-radius: 50%;
         box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
        }

  .cart-count { position: absolute; 
  top: -20px;
   right: -8px;
    background: #f6f2f3;
     color: white;
      font-size: 12px;
       width: 22px;
        height: 22px; 
        border-radius: 10%; 
        display: flex;
         align-items: center; 
         justify-content: center; 
         font-weight: 700;
         }

  .section-title { text-align: center;
   padding: 2rem 0 1rem; 
   color:lightseagreen;
   }

  .section-title h2 { font-size: 2rem;
   text-shadow: 2px 2px 8px rgba(82, 95, 125, 0.2);
   }
  .section-title i { color: #cbc5c6;
   margin: 0.5rem 0; 
   font-size: 1.5rem; 
  }
  .category-tabs { display: flex; 
  flex-wrap: wrap; 
  justify-content: center;
   gap: 12px; 
   padding: 0 5% 2rem;
    max-width: 1200px; 
    margin: 0 auto; 
  }
  .category-tabs a { padding: 12px 22px; 
  background: white;
   color: #667eea;
    border-radius: 50px;
     text-decoration: none;
      font-weight: 700; 
      border: 2px solid #667eea; 
      transition: all 0.3s ease;
       font-size: 15px;
       }

  .category-tabs a.active,.category-tabs a:hover { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
  color: white;
   box-shadow: 0 6px 20px rgba(102,126,234,0.4);
    transform: translateY(-2px); 
  }

  .flavors-grid { display: grid; 
  grid-template-columns: repeat(3, 1fr);
   gap: 2rem;
    padding: 0 5% 3rem; 
    max-width: 1200px;
     margin: 0 auto; 
    }

    

  .flavor-card { background: white;
   border-radius: 16px;
    padding: 1.2rem;
     box-shadow: 0 4px 15px rgba(126, 77, 77, 0.08);
      text-align: center; 
      transition: transform 0.3s ease; 
      display: flex;
       flex-direction: column;
       }
  .flavor-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(199, 34, 89, 0.3);
 }

  .flavor-img img { width: 100%; 
  height: 290px;
   object-fit: cover; 
   border-radius: 12px;
   }

  .flavor-card h3 { margin: 1rem 0 0.3rem; 
  color: #2f3542;
   text-transform: capitalize; 
   font-size: 1.2rem; 
  }

  .price { color: #ff6b81;
   font-weight: 700;
    font-size: 1.2rem;
     margin-bottom: 0.5rem;
     }

  .stock-info { font-size: 12px;
   color: #10b981;
    margin-bottom: 1rem;
     font-weight: 600; 
    }

  .stock-info.low { color: #ff9f1c; 
}
  .stock-info.out { color: #ff4757;
 }
  .cart-row { display: flex;
   flex-direction: column;
    gap: 0.8rem; 
    margin-top: auto;
   }
  .quantity-box { width: 100%;
   text-align: left; 
  }
  .quantity-box label { font-size: 0.8rem; 
  color: #57606f; 
  display: block;
   margin-bottom: 4px;
    font-weight: 600;
   }
  .quantity-box select { width: 100%;
   padding: 0.9rem;
    border: 1px solid #ddd;
     border-radius: 8px;
      font-size: 1rem;
     }
  .btn { background: linear-gradient(135deg, #ff6b81, #ff4757); 
  color: white; 
  border: none;
   padding: 0.9rem 1.2rem;
    border-radius: 8px; 
    cursor: pointer;
     font-weight: 700;
      font-size: 1rem;
       width: 100%; 
       transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.3);
       }
  .btn:hover { transform: translateY(-2px); 
}
  .btn:disabled { background: #ccc; 
  cursor: not-allowed; 
  box-shadow: none;
 }

   
  .cart-drawer { position: fixed;
   top: 0; 
   right: -400px; 
   width: 360px; 
   height: 100vh;
    background: white;
     box-shadow: -4px 0 15px rgba(0,0,0,0.1);
      transition: 0.3s ease; 
      z-index: 999;
       display: flex; 
       flex-direction: column; 
      }

  .cart-drawer.open { right: 0;
 }
  .cart-header { flex-shrink: 0;
   display: flex; 
   justify-content: space-between;
    align-items: center;
     padding: 1rem;
      border-bottom: 1px solid #eee;
       font-weight: 600; 
       font-size: 0.95rem; 
       margin-top: 55px; 
      }
  .close-cart { background: none;
   border: none;
    font-size: 1.2rem; 
    cursor: pointer;
     color: #333; 
    }

  .cart-items { flex: 1; 
  overflow-y: auto;
   padding: 1rem;
    min-height: 0;
   }
  .cart-item { display: flex;
   gap: 0.3rem;
    margin-bottom: 1rem;
     padding-bottom: 1rem;
      border-bottom: 1px solid #eee;
     }

  .cart-item img { width: 60px;
   height: 60px; 
   object-fit: cover;
    border-radius: 8px;
   }

  .item-details { flex: 1;
 }
  .item-details h4 { font-size: 0.9rem;
   margin-bottom: 0.2rem; 
   text-transform: capitalize; 
   color: #333; 
  }

  .item-details p { font-size: 0.75rem; 
  color: #747d8c; 
  line-height: 1.3; 
}

  .qty-control { display: flex;
   align-items: center;
    gap: 0.5rem;
     margin: 0.4rem 0;
     }

  .qty-btn { width: 22px; 
  height: 22px; 
  border: 1px solid #ddd;
   background: #f1f2f6;
    border-radius: 4px;
     cursor: pointer;
      font-size: 0.9rem;
     }

  .item-price { font-weight: 600; 
  font-size: 0.9rem; 
  text-align: right;
   color: #667eea;
   }

  .remove-item { color: #ff4757;
   cursor: pointer; 
   font-size: 1rem;
    margin-top: 0.3rem;
     text-align: right;
     }

  .cart-footer { flex-shrink: 0; 
  border-top: 1px solid #eee;
   padding: 1rem;
    background: #f8f9ff;
     box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
     }

  .cart-row-total.total { font-weight: 700;
   font-size: 1.4rem; 
   color: #667eea; 
   text-align: center; 
   padding: 15px 0;
   }

  .checkout-btn { width: 100%;
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
   color: white; 
   border: none;
    padding: 0.9rem;
     border-radius: 8px;
      font-weight: 700;
       margin-top: 10px;
        cursor: pointer; 
        transition: transform 0.5s ease;
         font-size: 15px; 
        }

  .checkout-btn:hover { transform: translateY(-2px);
 }

  .overlay { position: fixed; 
  left: 0; 
  width: 100%;
   height: 100%;
    background: rgba(0,0,0,0.4);
     display: none; 
     z-index: 998; 
    }
  .overlay.show { display: block;
 }
  .empty-msg { grid-column: 1/-1;
   text-align:center;
    padding:100px 20px;
     color:#666; 
     font-size:20px;
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

  <div class="cart" id="cartIcon">
    <i class="fas fa-shopping-cart"></i>
    <span class="cart-count">0</span>
  </div>

  <section class="flavors-section">
    <div class="section-title">
      <h2>Our Flavors</h2>
      <i class="fas fa-ice-cream"></i>
      <p>Choose from a wide range of delicious flavors.</p>
    </div>

    <div class="category-tabs">
        <a href="flavors.php?cat=all" class="<?=$active_cat=='all'?'active':''?>">All</a>
        <?php foreach($categories as $cat):?>
            <a href="flavors.php?cat=<?=urlencode($cat)?>" class="<?=$active_cat==$cat?'active':''?>"><?=$cat?></a>
        <?php endforeach;?>
    </div>

    <div class="flavors-grid">
      <?php if(count($flavors) > 0):?>
        <?php foreach($flavors as $f):?>
          <div class="flavor-card">
            <div class="flavor-img">
              <img src="<?=htmlspecialchars($f['image_path'])?>" alt="<?=htmlspecialchars($f['name'])?>" onerror="this.src='uploads/default.jpg'">
            </div>
            <h3><?=htmlspecialchars($f['name'])?></h3>
            <p class="price">Rs.<?=number_format($f['price'],2)?></p>

            <?php
            $stock = $f['stock']?? 0;
            if($stock == 0){ echo '<p class="stock-info out">Out of Stock</p>'; }
            elseif($stock < 5){ echo '<p class="stock-info low">Only '.$stock.' Ltr Left!</p>'; }
            else{ echo '<p class="stock-info">In Stock: '.$stock.' </p>'; }
          ?>

            <div class="cart-row">
              <?php if($stock > 0):?>
              <div class="quantity-box">
                <label>Quantity</label>
                <select>
                    <?php for($i=1; $i<=min(4, $stock); $i++):?>
                    <option><?=$i?></option>
                    <?php endfor;?>
                </select>
              </div>
              <button class="btn add-to-cart"
                data-id="<?=$f['id']?>"
                data-name="<?=htmlspecialchars($f['name'])?>"
                data-price="<?=$f['price']?>"
                data-img="<?=htmlspecialchars($f['image_path'])?>"
                data-stock="<?=$stock?>"
                data-desc="<?=htmlspecialchars($f['description']?? '')?>">
                Add to Cart
              </button>
              <?php else:?>
              <button class="btn" disabled>Out of Stock</button>
              <?php endif;?>
            </div>
          </div>
        <?php endforeach;?>
      <?php else:?>
        <div class="empty-msg">😢 No flavors in "<?=htmlspecialchars($active_cat)?>" category yet.</div>
      <?php endif;?>
    </div>
  </section>

  <div class="overlay" id="overlay"></div>
  <div class="cart-drawer" id="cartDrawer">
    <div class="cart-header">
      <span>YOUR CART (<span id="cartItemCount">0</span> ITEMS)</span>
      <button class="close-cart" id="closeCart"><i class="fas fa-times"></i></button>
    </div>
    <div class="cart-items" id="cartItems"></div>
    <div class="cart-footer">
      <div class="cart-row-total total">Total: <span id="total">Rs.0</span></div>
      <form action="checkout.php" method="POST" id="checkoutForm">
        <input type="hidden" name="cart_data" id="cartDataInput">
        <input type="hidden" name="total" id="totalInput">
        <button type="submit" class="checkout-btn">PROCEED TO CHECKOUT (<span id="checkoutTotal">Rs.0</span>)</button>
      </form>
    </div>
  </div>

<script>
const cartIcon = document.getElementById('cartIcon');
const cartDrawer = document.getElementById('cartDrawer');
const closeCart = document.getElementById('closeCart');
const overlay = document.getElementById('overlay');
const cartItemsEl = document.getElementById('cartItems');
const cartCount = document.querySelector('.cart-count');
const cartItemCount = document.getElementById('cartItemCount');

let cart = JSON.parse(localStorage.getItem('cart')) || [];

function openCart() { cartDrawer.classList.add('open'); overlay.classList.add('show'); renderCart(); }
function closeCartDrawer() { cartDrawer.classList.remove('open'); overlay.classList.remove('show'); }
cartIcon.addEventListener('click', openCart);
closeCart.addEventListener('click', closeCartDrawer);
overlay.addEventListener('click', closeCartDrawer);

document.querySelectorAll('.add-to-cart').forEach(btn => {
  btn.addEventListener('click', e => {
    const card = e.target.closest('.flavor-card');
    const qty = parseInt(card.querySelector('select').value);
    const id = parseInt(e.target.dataset.id);
    const name = e.target.dataset.name;
    const price = parseFloat(e.target.dataset.price);
    const img = e.target.dataset.img;
    const stock = parseInt(e.target.dataset.stock);
    const desc = e.target.dataset.desc || '';

    const existing = cart.find(item => item.id === id);
    if (existing) {
        if(existing.qty + qty > stock){
            alert('Sorry! Only '+stock+'  available for '+name);
            return;
        }
        existing.qty += qty;
    }
    else { cart.push({ id, name, price, qty, img, desc, stock }); }

    saveCart();
    openCart();
  });
});

function saveCart() { localStorage.setItem('cart', JSON.stringify(cart)); updateCartCount(); }
function updateCartCount() { const count = cart.reduce((sum, item) => sum + item.qty, 0); cartCount.textContent = count; cartItemCount.textContent = count; }

function renderCart() {
  cartItemsEl.innerHTML = '';
  let total = 0;
  cart.forEach((item, index) => {
    total += item.price * item.qty;
    const itemEl = document.createElement('div');
    itemEl.className = 'cart-item';
    itemEl.innerHTML = `
      <img src="${item.img}" alt="${item.name}">
      <div class="item-details">
        <h4>${item.name}</h4>
        <p>${item.desc}</p>
        <div class="qty-control">
          <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
          <span>${item.qty}</span>
          <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
        </div>
      </div>
      <div>
        <div class="item-price">Rs.${(item.price * item.qty).toFixed(2)}</div>
        <div class="remove-item" onclick="removeItem(${index})"><i class="fas fa-times-circle"></i></div>
      </div>
    `;
    cartItemsEl.appendChild(itemEl);
  });

  document.getElementById('total').textContent = `Rs.${total.toFixed(2)}`;
  document.getElementById('checkoutTotal').textContent = `Rs.${total.toFixed(2)}`;
  document.getElementById('cartDataInput').value = JSON.stringify(cart);
  document.getElementById('totalInput').value = total.toFixed(2);
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
  if (cart.length === 0) { e.preventDefault(); alert('Cart is empty. Please add items.'); }
});

function updateQty(index, change) {
  cart[index].qty += change;
  if (cart[index].qty <= 0) { cart.splice(index, 1); }
  saveCart();
  renderCart();
}
function removeItem(index) { cart.splice(index, 1); saveCart(); renderCart(); }
updateCartCount();
</script>
</body>
</html>