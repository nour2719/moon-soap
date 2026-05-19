<?php
$host = 'localhost';
$dbname = 'soap_store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // تعيين الترميز للغة العربية
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// ============================================
// دوال مساعدة للمتجر
// ============================================

function getCartCount() {
    if(isset($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    }
    return 0;
}

function getCartTotal($pdo) {
    $total = 0;
    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $ids = implode(',', array_keys($_SESSION['cart']));
        $stmt = $pdo->query("SELECT id, price FROM products WHERE id IN ($ids)");
        $products = $stmt->fetchAll();
        foreach($products as $product) {
            $quantity = $_SESSION['cart'][$product['id']];
            $total += $product['price'] * $quantity;
        }
    }
    return $total;
}

// ============================================
// دوال إضافية لنظام المستخدمين
// ============================================

// دالة للتحقق من تسجيل دخول المستخدم
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة للتحقق من تسجيل دخول المدير
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// دالة لجلب بيانات المستخدم الحالي
function getCurrentUser($pdo) {
    if(isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

// دالة لجلب اسم المستخدم الحالي
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'زائر';
}

// دالة لتسجيل خروج المستخدم
function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}

// دالة لتحويل التاريخ إلى صيغة عربية
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// دالة لتنسيق السعر
function formatPrice($price) {
    return number_format($price, 2) . 'ج.م';
}
?>