<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول المستخدم
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// إلغاء طلب
if(isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $order_id = $_GET['cancel'];
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if($order) {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$order_id]);
        $message = 'تم إلغاء الطلب بنجاح';
    } else {
        $error = 'لا يمكن إلغاء هذا الطلب';
    }
}

// جلب طلبات المستخدم
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - صابونيتي</title>
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
            background: #f5f5f5;
            color: #1a1a1a;
        }
        
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 40px;
        }
        
        /* Header */
        .header {
            padding: 24px 0;
            border-bottom: 1px solid #eaeaea;
            background: white;
            position: sticky;
            top: 0;
            z-index: 1000;
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
            transition: color 0.2s;
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
        
        /* Orders Page */
        .orders-container {
            padding: 60px 0;
            min-height: 70vh;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 500;
            color: #4a3b2c;
        }
        
        .page-header p {
            color: #888;
            margin-top: 10px;
        }
        
        .orders-grid {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .order-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
            transition: transform 0.2s;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .order-number {
            font-size: 1.1rem;
            font-weight: 600;
            color: #c6a43b;
        }
        
        .order-date {
            color: #888;
            font-size: 0.8rem;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-body {
            margin-bottom: 20px;
        }
        
        .order-items {
            margin-bottom: 15px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #f0f0f0;
        }
        
        .order-total {
            text-align: left;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eaeaea;
        }
        
        .order-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        
        .btn-cancel {
            background: none;
            border: 1px solid #e74c3c;
            color: #e74c3c;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-cancel:hover {
            background: #e74c3c;
            color: white;
        }
        
        .btn-view {
            background: none;
            border: 1px solid #c6a43b;
            color: #c6a43b;
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
        }
        
        .btn-view:hover {
            background: #c6a43b;
            color: white;
        }
        
        .no-orders {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 16px;
        }
        
        .no-orders i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 60px 0 40px;
            margin-top: 80px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
        }
        
        .footer-column h4 {
            font-size: 0.9rem;
            margin-bottom: 16px;
            color: #c6a43b;
        }
        
        .footer-column a {
            display: block;
            color: #aaa;
            text-decoration: none;
            font-size: 0.8rem;
            margin-bottom: 8px;
        }
        
        .footer-column a:hover {
            color: #c6a43b;
        }
        
        .copyright {
            text-align: center;
            color: #888;
            font-size: 0.7rem;
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }
            .nav {
                display: none;
            }
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .order-actions {
                justify-content: flex-start;
            }
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>


<main class="container orders-container">
    <div class="page-header">
        <h1>📦 طلباتي</h1>
        <p>تابع حالة طلباتك وألغي أي طلب قبل تجهيزه</p>
    </div>
    
    <?php if($message): ?>
        <div class="alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if(count($orders) > 0): ?>
        <div class="orders-grid">
            <?php foreach($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-number">#<?php echo $order['order_number']; ?></span>
                    <span class="order-date">📅 <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span>
                    <span class="order-status status-<?php echo $order['status']; ?>">
                        <?php 
                        if($order['status'] == 'pending') echo '⏳ قيد الانتظار';
                        elseif($order['status'] == 'processing') echo '🔧 قيد التجهيز';
                        elseif($order['status'] == 'completed') echo '✅ تم التوصيل';
                        else echo '❌ ملغي';
                        ?>
                    </span>
                </div>
                
                <div class="order-body">
                    <div class="order-items">
                        <?php 
                        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                        $stmt->execute([$order['id']]);
                        $items = $stmt->fetchAll();
                        foreach($items as $item):
                        ?>
                        <div class="order-item">
                            <span><?php echo $item['product_name']; ?></span>
                            <span><?php echo $item['quantity']; ?> × <?php echo number_format($item['price'], 2); ?> ج.م</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        <span>الإجمالي:</span>
                        <span><?php echo number_format($order['total_amount'], 2); ?> ج.م</span>
                    </div>
                </div>
                
                <div class="order-actions">
                    <?php if($order['status'] == 'pending'): ?>
                        <a href="?cancel=<?php echo $order['id']; ?>" class="btn-cancel" onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟')">
                            <i class="fas fa-times"></i> إلغاء الطلب
                        </a>
                    <?php endif; ?>
                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-view">
                        <i class="fas fa-eye"></i> تفاصيل الطلب
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-orders">
            <i class="fas fa-box-open"></i>
            <h3>لا توجد طلبات سابقة</h3>
            <p>لم تقم بإجراء أي طلب بعد</p>
            <a href="shop.php" class="btn-view" style="display: inline-block; margin-top: 20px;">تسوق الآن</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>

</body>
</html>