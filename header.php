<?php
// لا تضع session_start() هنا لأنها موجودة بالفعل في كل صفحة
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صابونيتي - متجر الصابون الطبيعي الفاخر</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <div class="container">
        <div class="header-inner">
            <div class="logo">
                <h1><a href="index.php">صابون<span>يتي</span></a></h1>
            </div>
            <div class="nav">
                <a href="index.php">الرئيسية</a>
                <a href="shop.php">المتجر</a>
                <a href="#soap">صابون</a>
                <a href="#shampoo">شامبو</a>
                <a href="#perfume">عطور</a>
                <a href="#">عن الموقع</a>
            </div>
            <div class="header-icons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name"><?php echo $_SESSION['user_name']; ?></span>
                    </a>
                    <a href="my-orders.php">
                        <i class="fas fa-clipboard-list"></i>
                        <span class="user-name">طلباتي</span>
                    </a>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name">دخول</span>
                    </a>
                    <a href="register.php">
                        <i class="fas fa-user-plus"></i>
                    </a>
                <?php endif; ?>
                
                <!-- سلة المشتريات - أيقونة دائرية -->
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
            </div>
        </div>
    </div>
</header>

<style>
    /* تنسيق أيقونة سلة المشتريات */
    .cart-icon {
        position: relative;
        background: #c6a43b;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: white;
        transition: all 0.3s;
        text-decoration: none;
    }
    
    .cart-icon i {
        font-size: 1.2rem;
    }
    
    .cart-icon .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        padding: 2px 6px;
        border-radius: 50%;
        font-size: 0.65rem;
        font-weight: bold;
        min-width: 18px;
        text-align: center;
    }
    
    .cart-icon:hover {
        transform: scale(1.1);
        background: #a8892e !important;
    }
    
    /* باقي تنسيقات الهيدر */
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
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .user-name {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 0 20px;
        }
        .nav {
            display: none;
        }
        .user-name {
            display: none;
        }
        .cart-icon {
            width: 36px;
            height: 36px;
        }
        .cart-icon i {
            font-size: 1rem;
        }
    }
</style>