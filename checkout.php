<?php
session_start();
require_once 'config/database.php';

if(empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

// ربط الطلب بالمستخدم إذا كان مسجل دخول
$user_id = null;
$user_email = '';
$user_name = '';
$user_phone = '';
$user_address = '';

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if($user) {
        $user_email = $user['email'];
        $user_name = $user['full_name'];
        $user_phone = $user['phone'];
        $user_address = $user['address'];
    }
}

$total = getCartTotal($pdo);
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $city = htmlspecialchars($_POST['city']);
    $payment_method = $_POST['payment_method'];
    
    $order_number = 'ORD-' . strtoupper(uniqid());
    
    try {
        $pdo->beginTransaction();
        
        // إضافة user_id إلى الطلب
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, full_name, email, phone, address, city, payment_method, total_amount, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $order_number, $full_name, $email, $phone, $address, $city, $payment_method, $total]);
        $order_id = $pdo->lastInsertId();
        
        $ids = implode(',', array_keys($_SESSION['cart']));
        $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
        $products = $stmt->fetchAll();
        
        foreach($products as $product) {
            $quantity = $_SESSION['cart'][$product['id']];
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $product['id'], $product['name'], $quantity, $product['price']]);
        }
        
        $pdo->commit();
        $_SESSION['cart'] = [];
        
        header("Location: order-success.php?order=$order_number");
        exit();
        
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "حدث خطأ، يرجى المحاولة مرة أخرى";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام الشراء - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #ffffff; color: #1a1a1a; }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 40px; }
        .header { padding: 24px 0; border-bottom: 1px solid #eaeaea; }
        .header-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo h1 { font-size: 1.5rem; font-weight: 600; }
        .logo h1 a { text-decoration: none; color: #1a1a1a; }
        .logo span { font-weight: 300; color: #c6a43b; }
        .nav { display: flex; gap: 32px; }
        .nav a { text-decoration: none; color: #1a1a1a; font-size: 0.9rem; font-weight: 500; }
        .nav a:hover { color: #c6a43b; }
        .header-icons { display: flex; gap: 24px; align-items: center; }
        .header-icons a { color: #1a1a1a; text-decoration: none; font-size: 1.1rem; position: relative; }
        .user-name { font-size: 0.85rem; font-weight: 500; margin-right: 5px; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #c6a43b; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 50%; }
        .checkout-container { padding: 60px 0; min-height: 70vh; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 60px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.8rem; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #eaeaea; border-radius: 8px; font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #c6a43b;
        }
        .order-summary { background: #faf8f5; padding: 30px; border-radius: 12px; height: fit-content; }
        .order-summary h3 { margin-bottom: 24px; font-weight: 500; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 16px; }
        .grand-total { font-size: 1.2rem; font-weight: 600; color: #c6a43b; }
        .btn-submit { background: #1a1a1a; color: white; border: none; padding: 14px; width: 100%; border-radius: 30px; cursor: pointer; font-size: 1rem; transition: background 0.2s; }
        .btn-submit:hover { background: #333; }
        .footer { background: #faf8f5; padding: 40px 0; margin-top: 60px; text-align: center; color: #888; font-size: 0.8rem; }
        @media (max-width: 768px) { .container { padding: 0 20px; } .nav { display: none; } .checkout-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container checkout-container">
    <div class="checkout-grid">
        <div>
            <h1 style="font-size: 2rem; font-weight: 400; margin-bottom: 32px;">إتمام الشراء</h1>
            <?php if($error): ?><div style="color: #e74c3c; margin-bottom: 20px; padding: 12px; background: #f8d7da; border-radius: 8px;"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>الاسم الكامل</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($user_name); ?>" required></div>
                <div class="form-group"><label>البريد الإلكتروني</label><input type="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required></div>
                <div class="form-group"><label>رقم الجوال</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" required></div>
                <div class="form-group"><label>العنوان</label><textarea name="address" rows="3" required><?php echo htmlspecialchars($user_address); ?></textarea></div>
                <div class="form-group"><label>المدينة</label><input type="text" name="city" required></div>
                <div class="form-group"><label>طريقة الدفع</label>
                    <select name="payment_method" required>
                        <option value="cash">💰 الدفع عند الاستلام</option>
                        <option value="bank">🏦 تحويل بنكي</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">تأكيد الطلب</button>
            </form>
        </div>
        <div class="order-summary">
            <h3>📋 ملخص الطلب</h3>
            <div class="summary-row"><span>🛍️ عدد المنتجات</span><strong><?php echo getCartCount(); ?></strong></div>
            <div class="summary-row"><span>💰 المجموع</span><strong><?php echo number_format($total, 2); ?> ج.م</strong></div>
            <div class="summary-row"><span>🚚 الشحن</span><strong>مجاني</strong></div>
            <hr style="margin: 20px 0; border-color: #eaeaea;">
            <div class="summary-row grand-total"><span>💳 الإجمالي</span><strong><?php echo number_format($total, 2); ?> ج.م</strong></div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>