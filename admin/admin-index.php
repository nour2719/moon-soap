<?php
session_start();
require_once '../config/database.php';

// التحقق من وجود كوكي "تذكرني"
if(!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['admin_remember'])) {
    $username = $_COOKIE['admin_remember'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if($user) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
    }
}

// إذا لم يكن مسجل دخول، يوجه لتسجيل الدخول
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// إحصائيات
$totalProducts = $pdo->query("SELECT COUNT(*) as total FROM products")->fetch()['total'];
$totalOrders = $pdo->query("SELECT COUNT(*) as total FROM orders")->fetch()['total'];
$pendingOrders = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'")->fetch()['total'];
$totalReviews = $pdo->query("SELECT COUNT(*) as total FROM reviews")->fetch()['total'];
$totalRevenue = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: #1a1a1a;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-header { padding: 30px; text-align: center; border-bottom: 1px solid #333; }
        .sidebar-header h2 { font-size: 1.3rem; font-weight: 500; }
        .sidebar-header span { color: #c6a43b; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: #aaa;
            text-decoration: none;
            transition: all 0.2s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: #333; color: white; }
        .main-content { flex: 1; margin-right: 280px; padding: 30px; }
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .welcome h3 { font-size: 1.2rem; font-weight: 500; }
        .welcome p { color: #888; font-size: 0.8rem; margin-top: 4px; }
        .logout-btn {
            background: none;
            border: 1px solid #eaeaea;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            color: #e74c3c;
            text-decoration: none;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-card i { font-size: 2rem; color: #c6a43b; margin-bottom: 16px; }
        .stat-card h4 { font-size: 0.8rem; color: #888; margin-bottom: 8px; }
        .stat-card .value { font-size: 2rem; font-weight: 600; }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: #1a1a1a;
            transition: transform 0.2s;
            display: block;
        }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .menu-card i { font-size: 2.5rem; color: #c6a43b; margin-bottom: 16px; }
        .menu-card h3 { font-size: 1.1rem; font-weight: 500; margin-bottom: 8px; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); position: fixed; z-index: 1000; }
            .main-content { margin-right: 0; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>صابون<span>يتي</span></h2>
            <p style="font-size: 0.7rem; color: #666; margin-top: 8px;">لوحة التحكم</p>
        </div>
        <div class="sidebar-nav">
            <a href="admin-index.php" class="active"><i class="fas fa-tachometer-alt"></i> الرئيسية</a>
            <a href="admin-products.php"><i class="fas fa-box"></i> المنتجات</a>
            <a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> الطلبات</a>
            <a href="admin-reviews.php"><i class="fas fa-star"></i> التقييمات</a>
            <a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <div class="welcome">
                <h3>مرحباً، <?php echo htmlspecialchars($_SESSION['admin_username']); ?></h3>
                <p>مرحباً بعودتك! إليك ملخص متجرك اليوم</p>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card"><i class="fas fa-box"></i><h4>المنتجات</h4><div class="value"><?php echo $totalProducts; ?></div></div>
            <div class="stat-card"><i class="fas fa-shopping-cart"></i><h4>الطلبات</h4><div class="value"><?php echo $totalOrders; ?></div></div>
            <div class="stat-card"><i class="fas fa-clock"></i><h4>طلبات معلقة</h4><div class="value"><?php echo $pendingOrders; ?></div></div>
            <div class="stat-card"><i class="fas fa-star"></i><h4>التقييمات</h4><div class="value"><?php echo $totalReviews; ?></div></div>
        </div>
        
        <div class="menu-grid">
            <a href="products.php" class="menu-card"><i class="fas fa-box"></i><h3>📦 إدارة المنتجات</h3><p>إضافة، تعديل، حذف المنتجات</p></a>
            <a href="orders.php" class="menu-card"><i class="fas fa-shopping-cart"></i><h3>📋 إدارة الطلبات</h3><p>متابعة حالة الطلبات</p></a>
            <a href="reviews.php" class="menu-card"><i class="fas fa-star"></i><h3>⭐ إدارة التقييمات</h3><p>مراجعة تقييمات العملاء</p></a>
        </div>
    </div>
</div>
</body>
</html>
