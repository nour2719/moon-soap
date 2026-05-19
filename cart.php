<?php
session_start();
require_once 'config/database.php';

// تهيئة السلة
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// إضافة منتج للسلة
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['add_to_cart'])) {
        $product_id = $_POST['add_to_cart'];
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if(isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        header('Location: cart.php');
        exit();
    }
    
    if(isset($_POST['update_cart'])) {
        foreach($_POST['quantities'] as $id => $quantity) {
            if($quantity <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = intval($quantity);
            }
        }
        header('Location: cart.php');
        exit();
    }
    
    if(isset($_POST['remove_item'])) {
        $product_id = $_POST['remove_item'];
        unset($_SESSION['cart'][$product_id]);
        header('Location: cart.php');
        exit();
    }
}

// جلب المنتجات في السلة
$cart_items = [];
$total = 0;
if(!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $products_data = $stmt->fetchAll();
    
    foreach($products_data as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة التسوق - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #1a1a1a;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 40px;
        }
        .header {
            padding: 24px 0;
            border-bottom: 1px solid #eaeaea;
        }
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .logo h1 a {
            text-decoration: none;
            color: #1a1a1a;
        }
        .logo span {
            font-weight: 300;
            color: #c6a43b;
        }
        .nav {
            display: flex;
            gap: 32px;
        }
        .nav a {
            text-decoration: none;
            color: #1a1a1a;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .nav a:hover {
            color: #c6a43b;
        }
        .header-icons {
            display: flex;
            gap: 24px;
            align-items: center;
        }
        .header-icons a {
            color: #1a1a1a;
            text-decoration: none;
            font-size: 1.1rem;
            position: relative;
        }
        .user-name {
            font-size: 0.85rem;
            font-weight: 500;
            margin-right: 5px;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -12px;
            background: #c6a43b;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 50%;
        }
        .cart-container {
            padding: 60px 0;
            min-height: 60vh;
        }
        .cart-header {
            margin-bottom: 40px;
        }
        .cart-header h1 {
            font-size: 2rem;
            font-weight: 400;
        }
        .empty-cart {
            text-align: center;
            padding: 80px 0;
        }
        .empty-cart-icon {
            font-size: 80px;
            margin-bottom: 24px;
            opacity: 0.5;
        }
        .empty-cart p {
            color: #888;
            margin-bottom: 24px;
        }
        .btn-continue {
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 30px;
            display: inline-block;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart-table th {
            text-align: right;
            padding: 16px;
            border-bottom: 1px solid #eaeaea;
            font-weight: 500;
            color: #888;
        }
        .cart-table td {
            padding: 24px 16px;
            border-bottom: 1px solid #eaeaea;
        }
        .cart-product {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .cart-product-image {
            width: 80px;
            height: 80px;
            background: #faf8f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cart-product-name {
            font-weight: 500;
        }
        .cart-quantity {
            width: 60px;
            padding: 8px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            text-align: center;
        }
        .btn-remove {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .btn-remove:hover {
            color: #e74c3c;
        }
        .cart-total {
            text-align: left;
            font-size: 1.2rem;
            font-weight: 500;
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .btn-update {
            background: none;
            border: 1px solid #eaeaea;
            padding: 10px 24px;
            border-radius: 30px;
            cursor: pointer;
        }
        .btn-checkout {
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 30px;
            display: inline-block;
        }
        .footer {
            background: #faf8f5;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #888;
            font-size: 0.8rem;
        }
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .nav { display: none; }
            .cart-product { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container cart-container">
    <div class="cart-header">
        <h1>سلة التسوق</h1>
    </div>

    <?php if(empty($cart_items)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <p>سلة التسوق فارغة</p>
            <a href="index.php" class="btn-continue">تسوق الآن</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>الإجمالي</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <div class="cart-product-image">
                                        <?php if($item['product']['image'] && file_exists('uploads/' . $item['product']['image'])): ?>
                                            <img src="uploads/<?php echo $item['product']['image']; ?>" width="60" height="60" style="object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            🧼
                                        <?php endif; ?>
                                    </div>
                                    <span class="cart-product-name"><?php echo $item['product']['name']; ?></span>
                                </div>
                             </div>
                            </td>
                            <td><?php echo number_format($item['product']['price'], 2); ?>ج.م</div>
                            <td>
                                <input type="number" name="quantities[<?php echo $item['product']['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" min="0" class="cart-quantity">
                             </div>
                            <td><?php echo number_format($item['subtotal'], 2); ?>ج.م</div>
                            <td>
                                <button type="submit" name="remove_item" value="<?php echo $item['product']['id']; ?>" class="btn-remove">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                             </div>
                         </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: left;"><strong>الإجمالي الكلي</strong></div>
                        <td colspan="2"><strong><?php echo number_format($total, 2); ?>ج.م</strong></div>
                     </tr>
                </tfoot>
            </table>

            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn-update">تحديث السلة</button>
                <a href="checkout.php" class="btn-checkout">إتمام الشراء</a>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>

</body>
</html>