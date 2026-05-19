<?php
session_start();
require_once 'config/database.php';

$order_number = $_GET['order'] ?? '';
if(empty($order_number)) header('Location: index.php');

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$order_number]);
$order = $stmt->fetch();
if(!$order) header('Location: index.php');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تم الطلب - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #ffffff; }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 40px; }
        .header { padding: 24px 0; border-bottom: 1px solid #eaeaea; }
        .header-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo h1 a { text-decoration: none; color: #1a1a1a; font-size: 1.5rem; }
        .logo span { color: #c6a43b; }
        .success-container { text-align: center; padding: 80px 0; min-height: 60vh; }
        .success-icon { font-size: 80px; margin-bottom: 24px; }
        .success-container h1 { font-size: 2rem; font-weight: 400; margin-bottom: 16px; }
        .order-info { background: #faf8f5; padding: 30px; border-radius: 8px; max-width: 400px; margin: 30px auto; text-align: right; }
        .btn-home { background: #1a1a1a; color: white; text-decoration: none; padding: 12px 32px; border-radius: 30px; display: inline-block; margin-top: 20px; }
        .footer { background: #faf8f5; padding: 40px 0; margin-top: 60px; text-align: center; color: #888; font-size: 0.8rem; }
    </style>
</head>
<body>
<header class="header"><div class="container"><div class="header-inner"><div class="logo"><h1><a href="index.php">صابون<span>يتي</span></a></h1></div></div></div></header>
<main class="container success-container">
    <div class="success-icon">✅</div>
    <h1>تم استلام طلبك بنجاح!</h1>
    <p>شكراً لتسوقك من صابونيتي</p>
    <div class="order-info">
        <p><strong>رقم الطلب:</strong> <?php echo $order['order_number']; ?></p>
        <p><strong>المبلغ:</strong> <?php echo number_format($order['total_amount'], 2); ?> ج.م</p>
        <p><strong>الحالة:</strong> قيد المعالجة</p>
    </div>
    <a href="index.php" class="btn-home">العودة للمتجر</a>
</main>
<?php include 'footer.php'; ?>
</html>