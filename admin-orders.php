<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');  // ← غيرنا من login.php إلى admin-login.php
    exit();
}

if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
}

$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الطلبات - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 30px; }
        .admin-header { background: white; padding: 20px 30px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { font-size: 1.5rem; font-weight: 500; }
        .btn-back { background: #1a1a1a; color: white; padding: 8px 20px; border-radius: 30px; text-decoration: none; }
        table { width: 100%; background: white; border-radius: 12px; overflow: hidden; border-collapse: collapse; }
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #eaeaea; }
        th { background: #faf8f5; font-weight: 500; }
        select { padding: 6px 12px; border-radius: 20px; border: 1px solid #eaeaea; }
        .status-pending { background: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; display: inline-block; }
        .status-processing { background: #cfe2ff; color: #004085; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; }
        .status-completed { background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; }
        .status-cancelled { background: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; }
        .btn-edit { background: #4CAF50; color: white; padding: 5px 12px; border-radius: 20px; text-decoration: none; font-size: 0.7rem; }
        @media (max-width: 768px) { .container { padding: 15px; } table { font-size: 0.7rem; } th, td { padding: 8px; } }
    </style>
</head>
<body>
<div class="container">

    <button onclick="history.back()" style="
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
    " onmouseover="this.style.background='#a07d2a'" onmouseout="this.style.background='#c6a43b'">
        <i class="fas fa-arrow-right"></i> العودة للصفحة السابقة
    </button>
    <div class="admin-header">
        <h1><i class="fas fa-shopping-cart"></i> إدارة الطلبات</h1>
        <a href="admin-index.php" class="btn-back"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $order): ?>
            <tr>
                <td><?php echo $order['order_number']; ?></td>
                <td><?php echo $order['full_name']; ?></td>
                <td><?php echo number_format($order['total_amount'],2); ?> ج.م</td>
                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                <td>
                    <span class="status-<?php echo $order['status']; ?>">
                        <?php 
                        if($order['status'] == 'pending') echo 'قيد الانتظار';
                        elseif($order['status'] == 'processing') echo 'قيد التجهيز';
                        elseif($order['status'] == 'completed') echo 'مكتمل';
                        else echo 'ملغي';
                        ?>
                    </span>
                </td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="pending" <?php echo $order['status']=='pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                            <option value="processing" <?php echo $order['status']=='processing' ? 'selected' : ''; ?>>قيد التجهيز</option>
                            <option value="completed" <?php echo $order['status']=='completed' ? 'selected' : ''; ?>>مكتمل</option>
                            <option value="cancelled" <?php echo $order['status']=='cancelled' ? 'selected' : ''; ?>>ملغي</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-edit">عرض التفاصيل</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>