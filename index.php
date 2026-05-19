<?php
session_start();
require_once 'config/database.php';

// جلب المنتجات المميزة
$stmt = $pdo->query("SELECT * FROM products WHERE featured = 1 LIMIT 8");
$featured_products = $stmt->fetchAll();

// جلب منتجات الصابون
$stmt = $pdo->query("SELECT * FROM products WHERE category = 'طبيعي' OR category = 'عضوي'");
$soap_products = $stmt->fetchAll();

// جلب منتجات الشامبو (لو موجودة، وإلا عرض منتجات افتراضية)
$stmt = $pdo->query("SELECT * FROM products WHERE category = 'شامبو' LIMIT 4");
$shampoo_products = $stmt->fetchAll();
if(count($shampoo_products) == 0) {
    // إذا لم يوجد شامبو، استخدم آخر منتجات
    $stmt = $pdo->query("SELECT * FROM products LIMIT 4");
    $shampoo_products = $stmt->fetchAll();
}

// جلب منتجات العطور (لو موجودة، وإلا عرض منتجات افتراضية)
$stmt = $pdo->query("SELECT * FROM products WHERE category = 'فاخر' LIMIT 4");
$perfume_products = $stmt->fetchAll();
if(count($perfume_products) == 0) {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");
    $perfume_products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="uploads/logo.ico" href="/soap-store/uploads/logo.ico">
    <link rel="shortcut icon" href="/soap-store/uploads/logo.ico">
    <title>moon soap</title>
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

        /* Hero Section */
        .hero {
            padding: 80px 0;
            text-align: center;
            background: linear-gradient(135deg, #faf8f5 0%, #f5eee6 100%);
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 400;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .hero p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        /* Section Title */
        .section-title {
            text-align: center;
            margin: 60px 0 40px;
        }

        .section-title h3 {
            font-size: 1.8rem;
            font-weight: 400;
            letter-spacing: -0.01em;
        }

        .section-title p {
            color: #888;
            font-size: 0.9rem;
            margin-top: 8px;
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

        /* Benefits Section */
        .benefits-section {
            padding: 60px 0;
            background: #ffffff;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 40px;
        }

        .benefit-card {
            text-align: center;
            padding: 30px;
            background: #faf8f5;
            border-radius: 16px;
            transition: transform 0.3s;
        }

        .benefit-card:hover {
            transform: translateY(-5px);
        }

        .benefit-card i {
            font-size: 2.5rem;
            color: #c6a43b;
            margin-bottom: 20px;
        }

        .benefit-card h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .benefit-card p {
            color: #666;
            font-size: 0.85rem;
            line-height: 1.6;
        }

        /* Info Banner */
        .info-banner {
            background: #faf8f5;
            padding: 60px 0;
            margin: 40px 0;
            text-align: center;
        }

        .info-banner h3 {
            font-size: 1.8rem;
            font-weight: 400;
            margin-bottom: 20px;
        }

        .info-banner p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .info-banner .badges {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .info-banner .badge {
            text-align: center;
        }

        .info-banner .badge i {
            font-size: 2rem;
            color: #c6a43b;
            margin-bottom: 10px;
        }

        .info-banner .badge h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .info-banner .badge p {
            font-size: 0.8rem;
            color: #888;
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
            font-size: 0.8rem;
        }

        .category-header a:hover {
            text-decoration: underline;
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
            .benefits-grid {
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
            .benefits-grid {
                grid-template-columns: 1fr;
            }
            .hero h1 {
                font-size: 2rem;
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


<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>صابون طبيعي فاخر</h1>
        <p>مصنوع يدوياً من أفضل المكونات الطبيعية لعناية فائقة ببشرتك</p>
    </div>
</section>

<!-- ============================================ -->
<!-- قسم فوائد المنتجات الطبيعية -->
<!-- ============================================ -->
<section class="benefits-section">
    <div class="container">
        <div class="section-title">
            <h3>✨ فوائد المنتجات الطبيعية</h3>
            <p>لماذا تختار منتجاتنا لعناية بشرتك وشعرك؟</p>
        </div>

        <div class="benefits-grid">
            <div class="benefit-card">
                <i class="fas fa-hand-holding-heart"></i>
                <h4>صابون طبيعي 100%</h4>
                <p>صابوننا مصنوع يدوياً من زيوت طبيعية نقية، خالي من المواد الكيماوية الضارة، مناسب لجميع أنواع البشرة حتى الحساسة منها.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-spray-can-sparkles"></i>
                <h4>شامبو طبيعي للشعر</h4>
                <p>شامبو طبيعي يغذي فروة الرأس ويقوي بصيلات الشعر، يمنع التساقط ويعزز النمو بدون سلفات أو بارابين.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-perfume"></i>
                <h4>عطور فاخرة طبيعية</h4>
                <p>عطورنا مستخلصة من الزيوت العطرية الطبيعية، روائح ثابتة تدوم طويلاً، خالية من الكحول الضار بالبشرة.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-seedling"></i>
                <h4>مكونات عضوية</h4>
                <p>نستخدم فقط المكونات العضوية المعتمدة، لضمان أفضل جودة وفعالية لمنتجاتنا.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-recycle"></i>
                <h4>صديق للبيئة</h4>
                <p>جميع منتجاتنا قابلة للتحلل، وعبواتنا صديقة للبيئة، لأن العناية بالطبيعة جزء من مهمتنا.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-truck-fast"></i>
                <h4>توصيل سريع</h4>
                <p>نوصل طلباتك لجميع المناطق خلال 2-5 أيام، مع تغليف آمن وحماية للمنتجات.</p>
            </div>
        </div>
    </div>
</section>

<!-- Info Banner -->
<section class="info-banner">
    <div class="container">
        <h3>لماذا تختار منتجاتنا الطبيعية؟</h3>
        <p>نحن نؤمن بأن الطبيعة تمنحنا أفضل ما لديها. منتجاتنا مصنوعة يدوياً من مكونات طبيعية 100%، خالية من المواد الكيماوية الضارة، لتمنح بشرتك وشعرك عناية فائقة.</p>
        
        <div class="badges">
            <div class="badge">
                <i class="fas fa-leaf"></i>
                <h4>طبيعي 100%</h4>
                <p>مكونات عضوية طبيعية</p>
            </div>
            <div class="badge">
                <i class="fas fa-hand-sparkles"></i>
                <h4>خالٍ من الكيماويات</h4>
                <p>بدون بارابين أو سلفات</p>
            </div>
            <div class="badge">
                <i class="fas fa-smile"></i>
                <h4>مناسب للبشرة الحساسة</h4>
                <p>لطيف وآمن للجميع</p>
            </div>
            <div class="badge">
                <i class="fas fa-star"></i>
                <h4>جودة عالية</h4>
                <p>منتجات معتمدة</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- قسم المنتجات المميزة -->
<!-- ============================================ -->
<div class="container">
    <div class="section-title">
        <h3>🛍️ منتجاتنا المميزة</h3>
        <p>اكتشف تشكيلتنا الأكثر طلباً</p>
    </div>

    <div class="products-grid">
        <?php foreach($featured_products as $product): ?>
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

<!-- زر عرض جميع المنتجات -->
<div style="text-align: center; margin: 40px 0;">
    <a href="shop.php" style="background: #c6a43b; color: white; padding: 12px 32px; border-radius: 30px; text-decoration: none; display: inline-block;">
        عرض جميع المنتجات <i class="fas fa-arrow-left"></i>
    </a>
</div>

<?php include 'footer.php'; ?>

</body>
</html>