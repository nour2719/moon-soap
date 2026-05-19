<?php
session_start();
require_once 'config/database.php';

// إذا كان المستخدم مسجل دخول بالفعل، يوجه للصفحة الرئيسية
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // التحقق من صحة البيانات
    if(empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif($password !== $confirm_password) {
        $error = 'كلمة المرور غير متطابقة';
    } elseif(strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح';
    } else {
        // التحقق من عدم وجود مستخدم بنفس الاسم أو البريد
        $check = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        
        if($check->rowCount() > 0) {
            $error = 'اسم المستخدم أو البريد الإلكتروني موجود بالفعل';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, 0)");
            if($stmt->execute([$username, $email, $hashed_password, $full_name])) {
                $success = 'تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.';
            } else {
                $error = 'حدث خطأ، يرجى المحاولة مرة أخرى';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        .auth-container h1 {
            font-size: 1.8rem;
            font-weight: 400;
            margin-bottom: 8px;
            text-align: center;
        }
        .auth-container > p {
            color: #888;
            margin-bottom: 32px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            font-family: inherit;
            transition: border 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #c6a43b;
        }
        .btn-submit {
            width: 100%;
            background: #1a1a1a;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: #333;
        }
        .auth-link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.85rem;
            color: #666;
        }
        .auth-link a {
            color: #c6a43b;
            text-decoration: none;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <a href="#">العناية بالجسم</a>
                <a href="#">الهدايا</a>
            </div>
            <div class="header-icons">
                <a href="login.php"><i class="far fa-user"></i></a>
                <a href="cart.php" style="position: relative;">
                    <i class="far fa-shopping-bag"></i>
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
            </div>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <div class="auth-container">
            <h1>✨ إنشاء حساب جديد</h1>
            <p>سجل الآن للاستمتاع بتجربة تسوق مخصصة</p>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>الاسم الكامل</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>تأكيد كلمة المرور</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-submit">إنشاء حساب</button>
            </form>
            
            <div class="auth-link">
                لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
            </div>
        </div>
    </div>
</main>


<?php include 'footer.php'; ?>

</body>
</html>