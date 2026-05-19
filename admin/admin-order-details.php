<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');  // ← تم التعديل
    exit();
}

$order_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if(!$order) {
    header('Location: admin-orders.php');  // ← تم التعديل
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل الطلب - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; background: white; border-radius: 12px; }
        h1 { margin-bottom: 20px; }
        .order-info { background: #faf8f5; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #eaeaea; }
        th { background: #faf8f5; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #c6a43b; text-decoration: none; }
        .btn-return {
            background: #c6a43b;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.85rem;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .btn-return:hover {
            background: #a07d2a;
        }
    </style>
</head>
<body>
<div class="container">
    <button onclick="history.back()" class="btn-return">
        <i class="fas fa-arrow-right"></i> العودة للصفحة السابقة
    </button>
    <a href="admin-orders.php" class="btn-back"><i class="fas fa-arrow-right"></i> العودة إلى الطلبات</a>  <!-- ← تم التعديل -->
    <h1>تفاصيل الطلب #<?php echo $order['order_number']; ?></h1>
    
    <div class="order-info">
        <p><strong>العميل:</strong> <?php echo $order['full_name']; ?></p>
        <p><strong>البريد:</strong> <?php echo $order['email']; ?></p>
        <p><strong>الجوال:</strong> <?php echo $order['phone']; ?></p>
        <p><strong>العنوان:</strong> <?php echo $order['address']; ?></p>
        <p><strong>المدينة:</strong> <?php echo $order['city']; ?></p>
        <p><strong>طريقة الدفع:</strong> <?php echo $order['payment_method'] == 'cash' ? 'الدفع عند الاستلام' : 'تحويل بنكي'; ?></p>
        <p><strong>تاريخ الطلب:</strong> <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td><?php echo $item['product_name']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($item['price'],2); ?> ج.م</td>
                <td><?php echo number_format($item['quantity'] * $item['price'],2); ?> ج.م</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>الإجمالي</strong></td>
                <td><strong><?php echo number_format($order['total_amount'],2); ?> ج.م</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
