<div class="pos-container" style="display: flex; gap: 20px; height: calc(100vh - 120px); overflow: hidden;">
    
    <!-- LEFT: Product Grid -->
    <div class="pos-products" style="flex: 2; overflow-y: auto; padding-right: 10px;">
        <div class="search-bar" style="margin-bottom: 20px;">
            <input type="text" id="posSearch" placeholder="ค้นหาสินค้า (ชื่อ, SKU)..." class="form-control" style="width: 100%; padding: 10px; font-size: 16px;">
        </div>

        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px;">
            <?php foreach ($products as $p): ?>
                <?php $img = !empty($p['product_picture']) ? $p['product_picture'] : 'assets/images/logo/banner2.png'; ?>
                <div class="product-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; cursor: pointer; transition: transform 0.2s;" 
                     onclick="addToCart(<?= $p['product_id'] ?>)"
                     data-name="<?= strtolower($p['name']) ?> <?= strtolower($p['sku']) ?>"
                >
                    <div style="height: 120px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f9f9f9;">
                        <img src="<?= htmlspecialchars($img) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="padding: 10px;">
                        <h4 style="margin: 0 0 5px; font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($p['name']) ?></h4>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <?php if (isset($p['discount']) && $p['discount'] > 0): 
                                $finalPrice = $p['price'] * (1 - ($p['discount'] / 100));
                            ?>
                                <div>
                                    <span style="color: #999; text-decoration: line-through; font-size: 12px;">฿<?= number_format($p['price'], 0) ?></span>
                                    <span style="color: #e74c3c; font-weight: bold;">฿<?= number_format($finalPrice, 0) ?></span>
                                </div>
                            <?php else: ?>
                                <span style="color: #2c3e50; font-weight: bold;">฿<?= number_format($p['price'], 0) ?></span>
                            <?php endif; ?>

                            <span style="font-size: 12px; color: <?= $p['stock'] > 0 ? '#28a745' : '#dc3545' ?>" id="stock_display_<?= $p['product_id'] ?>">
                                คงเหลือ: <?= $p['stock'] ?>
                            </span>
                        </div>
                        <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;" onclick="event.stopPropagation()">
                            <!-- Quantity Selector (Left) -->
                            <div class="qty-selector" style="background: #e9f7ef; border-radius: 25px; padding: 5px; display: flex; align-items: center; border: 1px solid #d4efdf;">
                                <button type="button" onclick="adjustCardQty(<?= $p['product_id'] ?>, -1)" 
                                        style="width: 32px; height: 32px; border-radius: 50%; border: none; background: #58d68d; color: white; font-size: 18px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                    -
                                </button>
                                <input type="number" id="card_qty_<?= $p['product_id'] ?>" value="1" min="1" 
                                       onclick="this.select()"
                                       style="width: 40px; text-align: center; border: none; background: transparent; font-weight: bold; font-size: 16px; color: #1d8348; outline: none; -moz-appearance: textfield;">
                                <button type="button" onclick="adjustCardQty(<?= $p['product_id'] ?>, 1)" 
                                        style="width: 32px; height: 32px; border-radius: 50%; border: none; background: #58d68d; color: white; font-size: 18px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                    +
                                </button>
                            </div>

                            <!-- Add Button (Right) -->
                            <button type="button" onclick="addToCartFromCard(<?= $p['product_id'] ?>)" 
                                    style="background: #fff; border: 2px solid #000; border-radius: 25px; padding: 5px 15px; font-size: 14px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 5px; height: 42px; transition: all 0.2s;">
                                <i class="fas fa-shopping-cart"></i> เพิ่ม
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT: Cart -->
    <div class="pos-cart" id="pos-cart-container" style="flex: 1; background: #fff; border: 1px solid #ddd; border-radius: 8px; display: flex; flex-direction: column;">
        
        <!-- INITIAL RENDER (Server Side) -->
        <div class="cart-header" style="padding: 15px; border-bottom: 1px solid #eee; background: #f8f9fa;">
            <h3 style="margin: 0; font-size: 18px;"><i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า</h3>
            <a href="javascript:void(0)" onclick="clearCart()" style="font-size: 12px; color: #dc3545; text-decoration: none; float: right;">ล้างรายการ</a>
        </div>

        <div class="cart-items" style="flex: 1; overflow-y: auto; padding: 0;">
            <?php if (empty($cart)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-shopping-basket" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>ยังไม่มีสินค้าในตะกร้า</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <?php foreach ($cart as $id => $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">
                                <div style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($item['name']) ?></div>
                                <div style="font-size: 12px; color: #666;">
                                    <?= number_format($item['final_unit_price'], 2) ?> x 
                                    <i class="fas fa-minus-circle" onclick="decreaseQty(<?= $id ?>)" style="cursor: pointer; color: #f39c12; margin-right: 2px;"></i>
                                    <?= $item['qty'] ?>
                                    <?php if($item['discount_percent'] > 0): ?>
                                        <span style="color: #e74c3c; font-size: 10px;">(-<?= $item['discount_percent'] ?>%)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: right; padding: 10px;">
                                <div style="font-weight: bold;"><?= number_format($item['line_total'], 2) ?></div>
                                <a href="javascript:void(0)" onclick="removeFromCart(<?= $id ?>)" style="color: #dc3545; font-size: 12px;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <div class="cart-footer" style="padding: 20px; border-top: 1px solid #eee; background: #f8f9fa;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 18px; font-weight: bold;">
                <span>ยอดรวมสุทธิ</span>
                <span style="color: #2ecc71;">฿<?= number_format($totalAmount, 2) ?></span>
            </div>
            
            <a href="index.php?action=staff_pos_checkout" 
               class="btn-checkout <?= empty($cart) ? 'disabled' : '' ?>"
               onclick="return confirm('ยืนยันการขาย?')"
               style="display: block; width: 100%; padding: 12px; background: #2ecc71; color: white; text-align: center; border-radius: 5px; text-decoration: none; font-weight: bold;"
            >
                <i class="fas fa-money-bill-wave"></i> ชำระเงิน
            </a>
        </div>

    </div>
</div>

<script>
// Search Filter
document.getElementById('posSearch').addEventListener('keyup', function() {
    let val = this.value.toLowerCase();
    let items = document.querySelectorAll('.product-card');
    items.forEach(item => {
        let name = item.getAttribute('data-name');
        if (name.includes(val)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

function adjustCardQty(id, delta) {
    let input = document.getElementById('card_qty_' + id);
    let val = parseInt(input.value) || 1;
    val += delta;
    if (val < 1) val = 1;
    input.value = val;
}

function addToCartFromCard(id) {
    let input = document.getElementById('card_qty_' + id);
    let qty = parseInt(input.value) || 1;
    addToCart(id, qty).then(success => {
        if(success) input.value = 1;
    });
}

// Load Cart Function (Update UI without refresh)
function loadCart() {
    fetch('index.php?action=staff_pos_get_cart&t=' + new Date().getTime())
    .then(response => response.json())
    .then(data => {
        // อัปเดต HTML ของตะกร้า
        document.getElementById('pos-cart-container').innerHTML = data.html;
        
        // อัปเดตจำนวนสต็อกคงเหลือบนหน้าจอ
        if (data.stockData) {
            for (let productId in data.stockData) {
                let stockDisplay = document.getElementById('stock_display_' + productId);
                if (stockDisplay) {
                    let remaining = data.stockData[productId];
                    stockDisplay.innerText = 'คงเหลือ: ' + remaining;
                    if (remaining > 0) {
                        stockDisplay.style.color = '#28a745';
                    } else {
                        stockDisplay.style.color = '#dc3545';
                    }
                }
            }
        }
    })
    .catch(error => console.error('Error loading cart:', error));
}

// Add to Cart AJAX
function addToCart(productId, qty = 1) {
    let formData = new FormData();
    formData.append('product_id', productId);
    formData.append('qty', qty);

    return fetch('index.php?action=staff_pos_add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadCart();
            return true;
        } else {
            alert(data.msg);
            return false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback if JSON fails
        location.reload(); 
        return false;
    });
}

// Decrease Qty (AJAX)
function decreaseQty(id) {
    fetch('index.php?action=staff_pos_decrease&ajax=1&id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') loadCart();
    })
    .catch(() => location.reload());
}

// Remove from Cart (AJAX)
function removeFromCart(id) {
    fetch('index.php?action=staff_pos_remove&ajax=1&id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') loadCart();
    })
    .catch(() => location.reload());
}

// Clear Cart (AJAX)
function clearCart() {
    if(!confirm('ยืนยันล้างตะกร้า?')) return;
    
    fetch('index.php?action=staff_pos_clear&ajax=1')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') loadCart();
    })
    .catch(() => location.reload());
}

// Initial Load & History Navigation (Fix back button issue)
window.addEventListener('pageshow', function(event) {
    loadCart();
});
</script>

<style>
    /* Remove Number Arrow/Spinners */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }

    .btn-checkout.disabled {
        background: #ccc !important;
        pointer-events: none;
    }
    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>
