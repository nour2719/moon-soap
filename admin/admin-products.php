<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول كمسؤول
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

define('UPLOAD_DIR', '../uploads/');

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

$action = $_GET['action'] ?? 'list';

// قائمة أنواع البشرة المتاحة
$skin_types_options = [
    'للبشرة الجافة' => '🧴 للبشرة الجافة',
    'للبشرة الدهنية' => '💧 للبشرة الدهنية',
    'للبشرة الحساسة' => '🌸 للبشرة الحساسة',
    'للبشرة العادية' => '✨ للبشرة العادية',
    'للبشرة المختلطة' => '🔄 للبشرة المختلطة',
    'لجميع أنواع البشرة' => '🌟 لجميع أنواع البشرة',
    'للشعر الجاف' => '💇‍♀️ للشعر الجاف',
    'للشعر الدهني' => '💇‍♂️ للشعر الدهني',
    'للشعر العادي' => '✨ للشعر العادي',
    'للشعر المتقصف' => '⚠️ للشعر المتقصف',
    'لجميع أنواع الشعر' => '🌟 لجميع أنواع الشعر'
];

// إضافة منتج
if($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $ingredients = $_POST['ingredients'];
    $stock = $_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $skin_types = $_POST['skin_types'] ?? [];
    
    $image_name = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        if(in_array($extension, $allowed)) {
            $upload_path = UPLOAD_DIR . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, ingredients, image, stock, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $category, $ingredients, $image_name, $stock, $featured]);
        $product_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO product_skin_types (product_id, skin_type) VALUES (?, ?)");
        foreach($skin_types as $skin_type) {
            $stmt->execute([$product_id, $skin_type]);
        }
        
        $pdo->commit();
        header('Location: products.php?msg=added');
        exit();
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// تعديل منتج
if($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $ingredients = $_POST['ingredients'];
        $stock = $_POST['stock'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        $skin_types = $_POST['skin_types'] ?? [];
        
        $image_name = $_POST['current_image'];
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            if(in_array($extension, $allowed)) {
                $upload_path = UPLOAD_DIR . $image_name;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
                if(!empty($_POST['current_image']) && file_exists(UPLOAD_DIR . $_POST['current_image'])) {
                    unlink(UPLOAD_DIR . $_POST['current_image']);
                }
            }
        }
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category=?, ingredients=?, image=?, stock=?, featured=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $category, $ingredients, $image_name, $stock, $featured, $id]);
            
            $pdo->prepare("DELETE FROM product_skin_types WHERE product_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("INSERT INTO product_skin_types (product_id, skin_type) VALUES (?, ?)");
            foreach($skin_types as $skin_type) {
                $stmt->execute([$id, $skin_type]);
            }
            
            $pdo->commit();
            header('Location: products.php?msg=updated');
            exit();
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        header('Location: products.php');
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT skin_type FROM product_skin_types WHERE product_id = ?");
    $stmt->execute([$id]);
    $product_skin_types = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// حذف منتج
if($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if($product && !empty($product['image']) && file_exists(UPLOAD_DIR . $product['image'])) {
        unlink(UPLOAD_DIR . $product['image']);
    }
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: products.php?msg=deleted');
    exit();
}

// جلب المنتجات حسب التقسيمة المطلوبة (صابون، شامبو، عطور)
// تشمل جميع المنتجات التي تحمل هذه التصنيفات أو ما يشابهها
$soap_products = $pdo->query("SELECT * FROM products WHERE category IN ('صابون', 'طبيعي', 'عضوي', 'فاخر') ORDER BY created_at DESC")->fetchAll();
$shampoo_products = $pdo->query("SELECT * FROM products WHERE category IN ('شامبو') ORDER BY created_at DESC")->fetchAll();
$perfume_products = $pdo->query("SELECT * FROM products WHERE category IN ('عطور', 'عطر') ORDER BY created_at DESC")->fetchAll();

// دالة لجلب أنواع البشرة لمنتج معين
function getProductSkinTypes($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT skin_type FROM product_skin_types WHERE product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/logo.ico" href="/soap-store/assets/favicon.png">
    <link rel="shortcut icon" href="/soap-store/assets/favicon.ico">
    <title>إدارة المنتجات - صابونيتي</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <div class="sidebar-header">
        <img src="../uploads/logo.ico" alt="صابونيتي" style="width: 80px; margin-bottom: 10px;">
        <p style="font-size: 0.7rem; color: #666; margin-top: 8px;">لوحة التحكم</p>
    </div>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5efe7; color: #1a1a1a; }
        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }
        
        .admin-header {
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .admin-header h1 { font-size: 1.5rem; font-weight: 600; color: #4a3b2c; }
        .admin-header h1 i { color: #c6a43b; margin-left: 10px; }
        
        .btn-back, .btn-home {
            background: #1a1a1a;
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back:hover, .btn-home:hover { background: #c6a43b; }
        
        .btn-add {
            background: #c6a43b;
            color: white;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 25px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-add:hover { background: #a07d2a; transform: translateY(-2px); }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-right: 4px solid #28a745;
        }
        
        .category-section {
            margin: 50px 0;
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0d5c5;
        }
        
        .category-header h2 {
            font-size: 1.6rem;
            font-weight: 600;
            color: #4a3b2c;
        }
        
        .category-header h2 i {
            color: #c6a43b;
            margin-left: 10px;
        }
        
        .category-count {
            background: #c6a43b;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            margin-right: 10px;
        }
        
        .view-all {
            color: #c6a43b;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .view-all:hover { text-decoration: underline; }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f8f4ef;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-image img { transform: scale(1.05); }
        
        .product-image .no-image { font-size: 4rem; opacity: 0.5; }
        
        .product-info {
            padding: 20px;
            text-align: center;
        }
        
        .product-category {
            font-size: 0.7rem;
            color: #c6a43b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: inline-block;
            background: #f0e6d8;
            padding: 4px 12px;
            border-radius: 20px;
        }
        
        .product-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 10px 0 5px;
            color: #2c2c2c;
        }
        
        .product-skin-types {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin: 10px 0;
        }
        
        .skin-badge {
            font-size: 0.65rem;
            background: #f0e6d8;
            color: #4a3b2c;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #c6a43b;
            margin: 10px 0;
        }
        
        .product-price::after { content: " ج.م"; font-size: 0.8rem; }
        
        .product-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn-edit-card {
            background: #2c7a47;
            color: white;
            padding: 6px 16px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-delete-card {
            background: #e74c3c;
            color: white;
            padding: 6px 16px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit-card:hover, .btn-delete-card:hover {
            opacity: 0.85;
            transform: scale(1.02);
        }
        
        .stock-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            z-index: 1;
        }
        .stock-badge.available { background: #2c7a47; }
        .stock-badge.on-order { background: #e67e22; }
        .stock-badge.out { background: #e74c3c; }
        
        .featured-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #c6a43b;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .form-card {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-card h2 { margin-bottom: 25px; color: #4a3b2c; font-weight: 600; }
        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0d5c5;
            border-radius: 10px;
            font-family: inherit;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #c6a43b;
            box-shadow: 0 0 0 3px rgba(198,164,59,0.1);
        }
        
        .skin-types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            background: #f9f7f4;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #e0d5c5;
        }
        
        .skin-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 0;
        }
        .skin-checkbox input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; margin: 0; }
        
        .btn-submit {
            background: #c6a43b;
            color: white;
            border: none;
            padding: 12px 35px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-submit:hover { background: #a07d2a; }
        
        .empty-section {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
            color: #888;
        }
        .empty-section i { font-size: 3rem; margin-bottom: 15px; color: #c6a43b; }
        
        .footer-bar {
            margin-top: 60px;
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 0.8rem;
            border-top: 1px solid #e0d5c5;
        }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .admin-header { flex-direction: column; gap: 15px; text-align: center; }
            .products-grid { grid-template-columns: 1fr; }
            .category-header { flex-direction: column; gap: 10px; text-align: center; }
            .skin-types-grid { grid-template-columns: 1fr; }
        }
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
        <h1><i class="fas fa-box"></i> إدارة المنتجات</h1>
        <div style="display: flex; gap: 12px;">
            <a href="../admin-index.php" class="btn-home"><i class="fas fa-home"></i> الرئيسية</a>
            <a href="dashboard.php" class="btn-back"><i class="fas fa-chart-line"></i> لوحة التحكم</a>
        </div>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'added'): ?>
            <div class="message"><i class="fas fa-check-circle"></i> ✅ تم إضافة المنتج بنجاح</div>
        <?php elseif($_GET['msg'] == 'updated'): ?>
            <div class="message"><i class="fas fa-edit"></i> ✏️ تم تعديل المنتج بنجاح</div>
        <?php elseif($_GET['msg'] == 'deleted'): ?>
            <div class="message"><i class="fas fa-trash"></i> 🗑️ تم حذف المنتج بنجاح</div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if(isset($error) && $error): ?>
        <div class="message" style="background: #f8d7da; color: #721c24;">⚠️ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($action == 'add'): ?>
        <!-- نموذج إضافة منتج -->
        <div class="form-card">
            <h2><i class="fas fa-plus-circle"></i> ➕ إضافة منتج جديد</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>اسم المنتج *</label>
                    <input type="text" name="name" required placeholder="مثال: صابونة اللافندر الطبيعي">
                </div>
                <div class="form-group">
                    <label>الوصف *</label>
                    <textarea name="description" rows="4" required placeholder="وصف المنتج..."></textarea>
                </div>
                <div class="form-group">
                    <label>السعر (ج.م) *</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>التصنيف *</label>
                    <select name="category" required>
                        <option value="">اختر التصنيف</option>
                        <option value="صابون">🧼 صابون</option>
                        <option value="شامبو">🧴 شامبو</option>
                        <option value="عطور">🌸 عطور</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>أنواع البشرة / الشعر (يمكن اختيار أكثر من نوع)</label>
                    <div class="skin-types-grid">
                        <?php foreach($skin_types_options as $value => $label): ?>
                            <label class="skin-checkbox">
                                <input type="checkbox" name="skin_types[]" value="<?php echo $value; ?>">
                                <span><?php echo $label; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small style="color: #888;">✓ يمكنك اختيار أكثر من نوع بشرة للمنتج الواحد</small>
                </div>
                
                <div class="form-group">
                    <label>المكونات</label>
                    <textarea name="ingredients" rows="3" placeholder="زيت الزيتون، زيت جوز الهند..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>التوفر</label>
                    <select name="stock">
                        <option value="متوفر">✅ متوفر</option>
                        <option value="بالطلب">📦 بالطلب</option>
                        <option value="غير متوفر">❌ غير متوفر</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><input type="checkbox" name="featured"> ⭐ منتج مميز</label>
                </div>
                
                <div class="form-group">
                    <label>صورة المنتج</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                
                <button type="submit" class="btn-submit">💾 حفظ المنتج</button>
                <a href="products.php" style="margin-right: 15px; color: #888;">إلغاء</a>
            </form>
        </div>
        
    <?php elseif($action == 'edit' && isset($product)): ?>
        <!-- نموذج تعديل منتج -->
        <div class="form-card">
            <h2><i class="fas fa-edit"></i> ✏️ تعديل المنتج</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?php echo $product['image']; ?>">
                <div class="form-group">
                    <label>اسم المنتج *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>الوصف *</label>
                    <textarea name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>السعر (ج.م) *</label>
                    <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>التصنيف *</label>
                    <select name="category" required>
                        <option value="صابون" <?php echo $product['category'] == 'صابون' ? 'selected' : ''; ?>>🧼 صابون</option>
                        <option value="شامبو" <?php echo $product['category'] == 'شامبو' ? 'selected' : ''; ?>>🧴 شامبو</option>
                        <option value="عطور" <?php echo $product['category'] == 'عطور' ? 'selected' : ''; ?>>🌸 عطور</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>أنواع البشرة / الشعر (يمكن اختيار أكثر من نوع)</label>
                    <div class="skin-types-grid">
                        <?php foreach($skin_types_options as $value => $label): ?>
                            <label class="skin-checkbox">
                                <input type="checkbox" name="skin_types[]" value="<?php echo $value; ?>" <?php echo in_array($value, $product_skin_types) ? 'checked' : ''; ?>>
                                <span><?php echo $label; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small style="color: #888;">✓ يمكنك اختيار أكثر من نوع بشرة للمنتج الواحد</small>
                </div>
                
                <div class="form-group">
                    <label>المكونات</label>
                    <textarea name="ingredients" rows="3"><?php echo htmlspecialchars($product['ingredients']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>التوفر</label>
                    <select name="stock">
                        <option value="متوفر" <?php echo $product['stock'] == 'متوفر' ? 'selected' : ''; ?>>✅ متوفر</option>
                        <option value="بالطلب" <?php echo $product['stock'] == 'بالطلب' ? 'selected' : ''; ?>>📦 بالطلب</option>
                        <option value="غير متوفر" <?php echo $product['stock'] == 'غير متوفر' ? 'selected' : ''; ?>>❌ غير متوفر</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><input type="checkbox" name="featured" <?php echo $product['featured'] ? 'checked' : ''; ?>> ⭐ منتج مميز</label>
                </div>
                
                <?php if($product['image']): ?>
                    <div class="form-group">
                        <label>الصورة الحالية</label>
                        <div><img src="../uploads/<?php echo $product['image']; ?>" width="80" style="border-radius: 10px;"></div>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>تغيير الصورة</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                
                <button type="submit" class="btn-submit">💾 تحديث المنتج</button>
                <a href="products.php" style="margin-right: 15px; color: #888;">إلغاء</a>
            </form>
        </div>
        
    <?php else: ?>
        
        <a href="?action=add" class="btn-add"><i class="fas fa-plus"></i> + إضافة منتج جديد</a>
        
        <!-- ============================================ -->
        <!-- قسم الصابون -->
        <!-- ============================================ -->
        <div class="category-section">
            <div class="category-header">
                <h2><i class="fas fa-soap"></i> 🧼 صابون طبيعي</h2>
                <a href="#" class="view-all">عرض الكل <i class="fas fa-arrow-left"></i></a>
            </div>
            
            <?php if(count($soap_products) > 0): ?>
                <div class="products-grid">
                    <?php foreach($soap_products as $product): 
                        $skin_types = getProductSkinTypes($pdo, $product['id']);
                    ?>
                        <div class="product-card">
                            <?php if($product['featured']): ?>
                                <div class="featured-badge">⭐ مميز</div>
                            <?php endif; ?>
                            
                            <?php 
                                $stock_class = '';
                                if($product['stock'] == 'متوفر') $stock_class = 'available';
                                elseif($product['stock'] == 'بالطلب') $stock_class = 'on-order';
                                else $stock_class = 'out';
                            ?>
                            <div class="stock-badge <?php echo $stock_class; ?>">
                                <?php 
                                    if($product['stock'] == 'متوفر') echo '✅ متوفر';
                                    elseif($product['stock'] == 'بالطلب') echo '📦 بالطلب';
                                    else echo '❌ غير متوفر';
                                ?>
                            </div>
                            
                            <div class="product-image">
                                <?php if(!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                                    <img src="../uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">🧼</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-skin-types">
                                    <?php if(count($skin_types) > 0): ?>
                                        <?php foreach($skin_types as $skin): ?>
                                            <span class="skin-badge">
                                                <?php 
                                                    if(strpos($skin, 'جافة') !== false) echo '🧴';
                                                    elseif(strpos($skin, 'دهنية') !== false) echo '💧';
                                                    elseif(strpos($skin, 'حساسة') !== false) echo '🌸';
                                                    elseif(strpos($skin, 'جميع') !== false) echo '🌟';
                                                    else echo '✨';
                                                ?>
                                                <?php echo htmlspecialchars($skin); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="skin-badge">✨ لجميع أنواع البشرة</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-price"><?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-actions">
                                    <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn-edit-card"><i class="fas fa-edit"></i> تعديل</a>
                                    <a href="?action=delete&id=<?php echo $product['id']; ?>" class="btn-delete-card" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')"><i class="fas fa-trash"></i> حذف</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-section">
                    <i class="fas fa-soap"></i>
                    <p>لا توجد منتجات في قسم الصابون بعد</p>
                    <a href="?action=add" style="color: #c6a43b;">➕ أضف أول منتج</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ============================================ -->
        <!-- قسم الشامبو -->
        <!-- ============================================ -->
        <div class="category-section">
            <div class="category-header">
                <h2><i class="fas fa-spray-can"></i> 🧴 شامبو طبيعي</h2>
                <a href="#" class="view-all">عرض الكل <i class="fas fa-arrow-left"></i></a>
            </div>
            
            <?php if(count($shampoo_products) > 0): ?>
                <div class="products-grid">
                    <?php foreach($shampoo_products as $product): 
                        $skin_types = getProductSkinTypes($pdo, $product['id']);
                    ?>
                        <div class="product-card">
                            <?php if($product['featured']): ?>
                                <div class="featured-badge">⭐ مميز</div>
                            <?php endif; ?>
                            
                            <?php 
                                $stock_class = '';
                                if($product['stock'] == 'متوفر') $stock_class = 'available';
                                elseif($product['stock'] == 'بالطلب') $stock_class = 'on-order';
                                else $stock_class = 'out';
                            ?>
                            <div class="stock-badge <?php echo $stock_class; ?>">
                                <?php 
                                    if($product['stock'] == 'متوفر') echo '✅ متوفر';
                                    elseif($product['stock'] == 'بالطلب') echo '📦 بالطلب';
                                    else echo '❌ غير متوفر';
                                ?>
                            </div>
                            
                            <div class="product-image">
                                <?php if(!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                                    <img src="../uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">🧴</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category">شامبو</span>
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-skin-types">
                                    <?php if(count($skin_types) > 0): ?>
                                        <?php foreach($skin_types as $skin): ?>
                                            <span class="skin-badge">
                                                <?php 
                                                    if(strpos($skin, 'شعر') !== false) echo '💇';
                                                    elseif(strpos($skin, 'جميع') !== false) echo '🌟';
                                                    else echo '✨';
                                                ?>
                                                <?php echo htmlspecialchars($skin); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="skin-badge">🌟 لجميع أنواع الشعر</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-price"><?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-actions">
                                    <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn-edit-card"><i class="fas fa-edit"></i> تعديل</a>
                                    <a href="?action=delete&id=<?php echo $product['id']; ?>" class="btn-delete-card" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')"><i class="fas fa-trash"></i> حذف</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-section">
                    <i class="fas fa-spray-can"></i>
                    <p>لا توجد منتجات في قسم الشامبو بعد</p>
                    <a href="?action=add" style="color: #c6a43b;">➕ أضف أول منتج</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ============================================ -->
        <!-- قسم العطور -->
        <!-- ============================================ -->
        <div class="category-section">
            <div class="category-header">
                <h2><i class="fas fa-perfume"></i> 🌸 عطور فاخرة</h2>
                <a href="#" class="view-all">عرض الكل <i class="fas fa-arrow-left"></i></a>
            </div>
            
            <?php if(count($perfume_products) > 0): ?>
                <div class="products-grid">
                    <?php foreach($perfume_products as $product): 
                        $skin_types = getProductSkinTypes($pdo, $product['id']);
                    ?>
                        <div class="product-card">
                            <?php if($product['featured']): ?>
                                <div class="featured-badge">⭐ مميز</div>
                            <?php endif; ?>
                            
                            <?php 
                                $stock_class = '';
                                if($product['stock'] == 'متوفر') $stock_class = 'available';
                                elseif($product['stock'] == 'بالطلب') $stock_class = 'on-order';
                                else $stock_class = 'out';
                            ?>
                            <div class="stock-badge <?php echo $stock_class; ?>">
                                <?php 
                                    if($product['stock'] == 'متوفر') echo '✅ متوفر';
                                    elseif($product['stock'] == 'بالطلب') echo '📦 بالطلب';
                                    else echo '❌ غير متوفر';
                                ?>
                            </div>
                            
                            <div class="product-image">
                                <?php if(!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                                    <img src="../uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">🌸</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category">عطر فاخر</span>
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-skin-types">
                                    <?php if(count($skin_types) > 0): ?>
                                        <?php foreach($skin_types as $skin): ?>
                                            <span class="skin-badge">
                                                🌸 <?php echo htmlspecialchars($skin); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="skin-badge">✨ رائحة ثابتة وطويلة</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-price"><?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-actions">
                                    <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn-edit-card"><i class="fas fa-edit"></i> تعديل</a>
                                    <a href="?action=delete&id=<?php echo $product['id']; ?>" class="btn-delete-card" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')"><i class="fas fa-trash"></i> حذف</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-section">
                    <i class="fas fa-perfume"></i>
                    <p>لا توجد منتجات في قسم العطور بعد</p>
                    <a href="?action=add" style="color: #c6a43b;">➕ أضف أول منتج</a>
                </div>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
    
    <div class="footer-bar">
        <i class="fas fa-copyright"></i> 2024 صابونيتي - لوحة تحكم المسؤول
    </div>
</div>
</body>
</html>
