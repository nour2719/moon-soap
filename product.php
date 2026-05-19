<?php
session_start();
require_once 'config/database.php';

$product_id = $_GET['id'] ?? 0;

// جلب المنتج
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    header('Location: index.php');
    exit();
}

// جلب التقييمات
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ? AND is_approved = 1");
$stmt->execute([$product_id]);
$rating_stats = $stmt->fetch();
$avg_rating = round($rating_stats['avg_rating'] ?? 0, 1);
$total_reviews = $rating_stats['total_reviews'] ?? 0;

// إضافة تقييم
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $user_name = htmlspecialchars($_POST['user_name']);
    $user_email = htmlspecialchars($_POST['user_email']);
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment']);
    
    if($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_name, user_email, rating, comment, is_approved) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$product_id, $user_name, $user_email, $rating, $comment]);
        header("Location: product.php?id=$product_id");
        exit();
    }
}

$reviews = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC");
$reviews->execute([$product_id]);
$reviews_list = $reviews->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - صابونيتي</title>
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

        .header {
            padding: 24px 0;
            border-bottom: 1px solid #eaeaea;
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

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            padding: 60px 0;
        }

        .product-gallery {
            background: #faf8f5;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
        }

        .product-gallery img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-info h1 {
            font-size: 2rem;
            font-weight: 400;
            margin-bottom: 16px;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .stars {
            color: #ffc107;
            font-size: 0.9rem;
        }

        .reviews-count {
            color: #888;
            font-size: 0.8rem;
        }

        .product-description {
            color: #666;
            line-height: 1.6;
            margin: 24px 0;
        }

        .product-meta {
            margin: 24px 0;
            padding: 20px 0;
            border-top: 1px solid #eaeaea;
            border-bottom: 1px solid #eaeaea;
        }

        .product-meta p {
            margin-bottom: 12px;
        }

        .badge {
            display: inline-block;
            background: #f0e6d8;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            margin: 3px;
        }

        .ingredients {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 24px 0;
            padding: 20px 0;
            border-top: 1px solid #eaeaea;
            border-bottom: 1px solid #eaeaea;
        }

        .ingredient {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .price {
            font-size: 1.8rem;
            font-weight: 500;
            margin: 24px 0;
        }

        .btn-add-to-cart {
            background: #1a1a1a;
            color: white;
            border: none;
            padding: 14px 32px;
            font-size: 0.9rem;
            cursor: pointer;
            border-radius: 30px;
            width: 100%;
            transition: background 0.2s;
        }

        .btn-add-to-cart:hover {
            background: #333;
        }

        .reviews-section {
            margin-top: 60px;
            padding-top: 40px;
            border-top: 1px solid #eaeaea;
        }

        .reviews-section h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .review-item {
            padding: 20px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .review-name {
            font-weight: 500;
        }

        .review-date {
            color: #888;
            font-size: 0.8rem;
        }

        .review-stars {
            color: #ffc107;
            font-size: 0.8rem;
            margin: 8px 0;
        }

        .review-comment {
            color: #666;
            line-height: 1.6;
        }

        .add-review {
            margin-top: 40px;
            padding: 30px;
            background: #faf8f5;
            border-radius: 8px;
        }

        .add-review h4 {
            margin-bottom: 20px;
        }

        .add-review input, .add-review textarea, .add-review select {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            font-family: inherit;
        }

        .btn-submit {
            background: #1a1a1a;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 30px;
            font-size: 0.9rem;
        }

        .btn-submit:hover {
            background: #333;
        }

        .footer {
            background: #faf8f5;
            padding: 60px 0 40px;
            margin-top: 80px;
        }

        .copyright {
            text-align: center;
            color: #888;
            font-size: 0.7rem;
            padding-top: 40px;
            border-top: 1px solid #eaeaea;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }
            .product-detail {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .nav {
                display: none;
            }
            .ingredients {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">
    <div class="product-detail">
        <div class="product-gallery">
            <?php if($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
            <?php else: ?>
                <div style="font-size: 100px; padding: 60px;">🧼</div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="rating">
                <div class="stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= $avg_rating ? '★' : '☆'; ?>
                    <?php endfor; ?>
                </div>
                <div class="reviews-count">(<?php echo $total_reviews; ?> تقييمات)</div>
            </div>

            <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>

            <div class="product-meta">
                <p><strong>🧴 التصنيف:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                <p><strong>🎯 نوع البشرة:</strong><br>
                    <?php 
                    $skin_types = explode("\n", $product['skin_type']);
                    foreach($skin_types as $type):
                        if(trim($type)):
                    ?>
                        <span class="badge"><?php echo htmlspecialchars(trim($type)); ?></span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </p>
                <p><strong>📦 التوفر:</strong> <?php echo htmlspecialchars($product['stock']); ?></p>
            </div>

            <div class="ingredients">
                <?php 
                $ingredients_list = explode('،', $product['ingredients']);
                foreach($ingredients_list as $ingredient): 
                ?>
                    <div class="ingredient">✦ <?php echo htmlspecialchars(trim($ingredient)); ?></div>
                <?php endforeach; ?>
            </div>

            <div class="price"><?php echo number_format($product['price'], 2); ?> ج.م</div>

            <form method="POST" action="cart.php">
                <input type="hidden" name="add_to_cart" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn-add-to-cart">
                    <i class="fas fa-shopping-bag"></i> أضف إلى السلة
                </button>
            </form>
        </div>
    </div>

    <div class="reviews-section">
        <h3>📝 التقييمات والمراجعات</h3>
        
        <?php if(count($reviews_list) > 0): ?>
            <?php foreach($reviews_list as $review): ?>
            <div class="review-item">
                <div class="review-header">
                    <span class="review-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                    <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                </div>
                <div class="review-stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                    <?php endfor; ?>
                </div>
                <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #888; text-align: center; padding: 40px;">لا توجد تقييمات بعد. كن أول من يقيم هذا المنتج!</p>
        <?php endif; ?>

        <div class="add-review">
            <h4>⭐ أضف تقييمك</h4>
            <form method="POST">
                <input type="text" name="user_name" placeholder="الاسم الكامل" required>
                <input type="email" name="user_email" placeholder="البريد الإلكتروني" required>
                <select name="rating" required>
                    <option value="">اختر التقييم</option>
                    <option value="5">★★★★★ ممتاز</option>
                    <option value="4">★★★★☆ جيد جداً</option>
                    <option value="3">★★★☆☆ جيد</option>
                    <option value="2">★★☆☆☆ مقبول</option>
                    <option value="1">★☆☆☆☆ ضعيف</option>
                </select>
                <textarea name="comment" rows="4" placeholder="اكتب رأيك في المنتج..." required></textarea>
                <button type="submit" name="submit_review" class="btn-submit">إرسال التقييم</button>
            </form>
        </div>
    </div>
</main>



<?php include 'footer.php'; ?>

</body>
</html>