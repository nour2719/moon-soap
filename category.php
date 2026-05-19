<?php
session_start();
require_once 'config/database.php';

// الحصول على نوع التصنيف من الرابط
$type = $_GET['type'] ?? 'soap';

// تحديد الاستعلام حسب النوع
$title = '';
$icon = '';
$query = '';

switch($type) {
    case 'soap':
        $title = 'صابون طبيعي';
        $icon = 'fa-soap';
        $query = "SELECT * FROM products WHERE category = 'طبيعي' OR category = 'عضوي' ORDER BY created_at DESC";
        break;
    case 'shampoo':
        $title = 'شامبو طبيعي';
        $icon = 'fa-spray-can';
        $query = "SELECT * FROM products WHERE category = 'شامبو' ORDER BY created_at DESC";
        break;
    case 'perfume':
        $title = 'عطور فاخرة';
        $icon = 'fa-perfume';
        $query = "SELECT * FROM products WHERE category = 'فاخر' OR category = 'عطور' ORDER BY created_at DESC";
        break;
    default:
        $title = 'المنتجات';
        $icon = 'fa-box';
        $query = "SELECT * FROM products ORDER BY created_at DESC";
}

$stmt = $pdo->query($query);
$products = $stmt->fetchAll();

// إذا لم يجد منتجات، اعرض جميع المنتجات كبديل
if(count($products) == 0 && $type != 'all') {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - صابونيتي</title>
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

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #faf8f5 0%, #f5eee6 100%);
            padding: 60px 0;
            text-align: center;
            margin-bottom: 50px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 500;
            color: #4a3b2c;
        }

        .page-header h1 i {
            color: #c6a43b;
            margin-left: 15px;
        }

        .page-header p {
            color: #666;
            margin-top: 10px;
        }

        .results-count {
            text-align: center;
            margin-bottom: 30px;
            color: #888;
            font-size: 0.9rem;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin: 40px 0;
        }

        .product-card {
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 16px;
        }

        .product-category {
            font-size: 0.7rem;
            color: #c6a43b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: inline-block;
            background: #f0e6d8;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .product-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 10px 0 5px;
            color: #2c2c2c;
        }

        .product-skin {
            font-size: 0.7rem;
            color: #888;
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #c6a43b;
            margin: 10px 0;
        }

        .btn-add {
            background: #1a1a1a;
            color: white;
            border: none;
            padding: 8px 20px;
            width: 100%;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 30px;
        }

        .btn-add:hover {
            background: #c6a43b;
        }

        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #c6a43b;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .no-products {
            text-align: center;
            padding: 60px;
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

        @media (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }
            .nav {
                display: none;
            }
            .products-grid {
                grid-template-columns: 1fr;
            }
            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
                text-align: center;
            }
        }
    </style>
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
            </div>
            <div class="header-icons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">
                        <i class="far fa-user"></i>
                        <span class="user-name"><?php echo $_SESSION['user_name']; ?></span>
                    </a>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php">
                        <i class="far fa-user"></i>
                        <span class="user-name">دخول</span>
                    </a>
                    <a href="register.php">
                        <i class="fas fa-user-plus"></i>
                    </a>
                <?php endif; ?>
                <a href="cart.php" style="position: relative;">
                    <i class="far fa-shopping-bag"></i>
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><i class="fas <?php echo $icon; ?>"></i> <?php echo $title; ?></h1>
        <p>اكتشف تشكيلتنا المميزة من <?php echo $title; ?></p>
    </div>
</section>

<div class="container">
    <a href="shop.php" class="back-link"><i class="fas fa-arrow-right"></i> العودة إلى المتجر</a>
    
    <div class="results-count">
        🧴 تم العثور على <?php echo count($products); ?> منتج
    </div>

    <?php if(count($products) > 0): ?>
    <div class="products-grid">
        <?php foreach($products as $product): ?>
        <div class="product-card" onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
            <div class="product-image">
                <?php if($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                    <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <?php else: ?>
                    <div style="padding: 40px;">
                        <?php if($type == 'soap'): ?>🧼<?php elseif($type == 'shampoo'): ?>🧴<?php else: ?>🌸<?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="product-info">
                <span class="product-category"><?php echo $product['category']; ?></span>
                <h3 class="product-title"><?php echo $product['name']; ?></h3>
                <div class="product-skin">
                    <i class="fas fa-face-smile"></i> <?php echo $product['skin_type']; ?>
                </div>
                <div class="product-price"><?php echo number_format($product['price'], 2); ?> ج.م</div>
                <form method="POST" action="cart.php" onclick="event.stopPropagation()">
                    <input type="hidden" name="add_to_cart" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn-add">أضف إلى السلة</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="no-products">
        <i class="fas fa-box-open" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
        <p>لا توجد منتجات في هذا القسم حالياً</p>
        <a href="shop.php" style="color: #c6a43b;">عودة إلى المتجر</a>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>