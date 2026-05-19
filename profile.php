<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// جلب طلبات المستخدم
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// تحديث البيانات
$message = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->execute([$full_name, $phone, $address, $user_id]);
    $message = 'تم تحديث البيانات بنجاح';
    
    // تحديث الجلسة
    $_SESSION['user_name'] = $full_name;
    
    // إعادة تحميل البيانات
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حسابي - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
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

        /* Profile Page */
        .profile-container {
            padding: 60px 0;
        }

        .profile-header {
            margin-bottom: 40px;
        }

        .profile-header h1 {
            font-size: 2rem;
            font-weight: 500;
            color: #4a3b2c;
        }

        .profile-header p {
            color: #888;
            margin-top: 10px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
        }

        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
        }

        .profile-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #c6a43b;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.8rem;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #eaeaea;
            border-radius: 10px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #c6a43b;
            box-shadow: 0 0 0 3px rgba(198,164,59,0.1);
        }

        .form-group input:disabled {
            background: #f5f5f5;
            color: #888;
        }

        .btn-save {
            background: #1a1a1a;
            color: white;
            border: none;
            padding: 10px 28px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .btn-save:hover {
            background: #c6a43b;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eaeaea;
        }

        .orders-table th {
            background: #faf8f5;
            font-weight: 600;
            color: #555;
        }

        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #004085;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-view {
            background: none;
            border: 1px solid #c6a43b;
            color: #c6a43b;
            padding: 4px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.7rem;
            transition: all 0.2s;
            display: inline-block;
        }

        .btn-view:hover {
            background: #c6a43b;
            color: white;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .no-orders {
            text-align: center;
            padding: 40px;
            color: #888;
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
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .orders-table {
                font-size: 0.7rem;
            }
            .orders-table th,
            .orders-table td {
                padding: 6px;
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

<main class="container profile-container">
    <div class="profile-header">
        <h1>مرحباً، <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h1>
        <p>من هنا يمكنك إدارة حسابك ومتابعة طلباتك</p>
    </div>
    
    <?php if($message): ?>
        <div class="alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <!-- معلومات الحساب -->
        <div class="profile-card">
            <h3>📋 معلومات الحساب</h3>
            <form method="POST">
                <div class="form-group">
                    <label>الاسم الكامل</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>رقم الجوال</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="أدخل رقم جوالك">
                </div>
                <div class="form-group">
                    <label>العنوان</label>
                    <textarea name="address" rows="3" placeholder="أدخل عنوانك"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn-save">
                    <i class="fas fa-save"></i> حفظ التغييرات
                </button>
            </form>
        </div>
        
        <!-- طلباتي -->
        <div class="profile-card">
            <h3>📦 طلباتي السابقة</h3>
            <?php if(count($orders) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></div>
                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?> </div>
                            <td><?php echo number_format($order['total_amount'], 2); ?> ج.م</div>
                            <td>
                                <span class="status status-<?php echo $order['status']; ?>">
                                    <?php 
                                    if($order['status'] == 'pending') echo '⏳ قيد الانتظار';
                                    elseif($order['status'] == 'processing') echo '🔧 قيد التجهيز';
                                    elseif($order['status'] == 'completed') echo '✅ مكتمل';
                                    else echo '❌ ملغي';
                                    ?>
                                </span>
                             </div>
                            <td>
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> تفاصيل
                                </a>
                             </div>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-box-open" style="font-size: 3rem; color: #ccc;"></i>
                    <p style="margin-top: 15px;">لا توجد طلبات سابقة</p>
                    <a href="shop.php" class="btn-view" style="margin-top: 15px; display: inline-block;">تسوق الآن</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>


<?php include 'footer.php'; ?>

</body>
</html>