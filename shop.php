<?php
session_start();
require_once 'config/database.php';

// جلب منتجات الصابون
$stmt = $pdo->query("SELECT * FROM products WHERE category = 'طبيعي' OR category = 'عضوي' LIMIT 8");
$soap_products = $stmt->fetchAll();

// جلب منتجات الشامبو
$stmt = $pdo->query("SELECT * FROM products WHERE category = 'شامبو' LIMIT 8");
$shampoo_products = $stmt->fetchAll();
if(count($shampoo_products) == 0) {
    $stmt = $pdo->query("SELECT * FROM products WHERE category = 'طبيعي' LIMIT 8");
    $shampoo_products = $stmt->fetchAll();
}

// جلب منتجات العطور
$stmt = $pdo->query("SELECT * FROM products WHERE category = 'فاخر' OR category = 'عطور' LIMIT 8");
$perfume_products = $stmt->fetchAll();
if(count($perfume_products) == 0) {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY RAND() LIMIT 8");
    $perfume_products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المتجر - صابونيتي | صابون طبيعي، شامبو، عطور</title>
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

        .page-header p {
            color: #666;
            margin-top: 10px;
        }

        /* Section Title */
        .section-title {
            text-align: center;
            margin: 40px 0 30px;
        }

        .section-title h2 {
            font-size: 1.8rem;
            font-weight: 500;
            color: #4a3b2c;
        }

        /* Category Section */
        .category-section {
            margin: 60px 0;
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 30px;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 15px;
        }

        .category-header h2 {
            font-size: 1.5rem;
            font-weight: 500;
            color: #4a3b2c;
        }

        .category-header h2 i {
            color: #c6a43b;
            margin-left: 10px;
        }

        .category-header a {
            color: #c6a43b;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .category-header a:hover {
            text-decoration: underline;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
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
            .category-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>🛍️ المتجر</h1>
        <p>اكتشف تشكيلتنا المميزة من المنتجات الطبيعية</p>
    </div>
</section>

<div class="container">
    
    <!-- ============================================ -->
    <!-- قسم الصابون الطبيعي -->
    <!-- ============================================ -->
    <div class="category-section" id="soap">
        <div class="category-header">
            <h2><i class="fas fa-soap"></i> 🧼 صابون طبيعي</h2>
            <a href="category.php?type=soap">عرض الكل <i class="fas fa-arrow-left"></i></a>
        </div>
        <div class="products-grid">
            <?php foreach($soap_products as $product): ?>
            <div class="product-card" onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
                <div class="product-image">
                    <?php if($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                        <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <?php else: ?>
                        <div style="padding: 40px;">🧼</div>
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
    </div>

    <!-- ============================================ -->
    <!-- قسم الشامبو الطبيعي -->
    <!-- ============================================ -->
    <div class="category-section" id="shampoo">
        <div class="category-header">
            <h2><i class="fas fa-spray-can"></i> 🧴 شامبو طبيعي</h2>
             <a href="category.php?type=shampoo">عرض الكل <i class="fas fa-arrow-left"></i></a>
        </div>
        <div class="products-grid">
            <?php foreach($shampoo_products as $product): ?>
            <div class="product-card" onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
                <div class="product-image">
                    <?php if($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                        <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <?php else: ?>
                        <div style="padding: 40px;">🧴</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-category">شامبو</span>
                    <h3 class="product-title"><?php echo $product['name']; ?></h3>
                    <div class="product-skin">
                        <i class="fas fa-face-smile"></i> لجميع أنواع الشعر
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
    </div>

    <!-- ============================================ -->
    <!-- قسم العطور الفاخرة -->
    <!-- ============================================ -->
    <div class="category-section" id="perfume">
        <div class="category-header">
            <h2><i class="fas fa-perfume"></i> 🌸 عطور فاخرة</h2>
            <a href="category.php?type=perfume">عرض الكل <i class="fas fa-arrow-left"></i></a>
        </div>
        <div class="products-grid">
            <?php foreach($perfume_products as $product): ?>
            <div class="product-card" onclick="location.href='product.php?id=<?php echo $product['id']; ?>'">
                <div class="product-image">
                    <?php if($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                        <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <?php else: ?>
                        <div style="padding: 40px;">🌸</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-category">عطر فاخر</span>
                    <h3 class="product-title"><?php echo $product['name']; ?></h3>
                    <div class="product-skin">
                        <i class="fas fa-face-smile"></i> رائحة ثابتة وطويلة
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
    </div>

</div>


<?php include 'footer.php'; ?>

</body>
</html>