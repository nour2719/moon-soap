<?php
session_start();
require_once 'config/database.php';

// إذا كان المستخدم مسجل دخول بالفعل، يوجه للصفحة الرئيسية
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 0");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_username'] = $user['username'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول - صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            max-width: 450px;
            margin: 80px auto;
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
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            border: 1px solid #f5c6cb;
        }
        .demo-info {
            margin-top: 30px;
            padding: 20px;
            background: #faf8f5;
            border-radius: 8px;
            font-size: 0.8rem;
        }
        .demo-info p {
            margin-bottom: 8px;
        }
        .demo-info strong {
            color: #c6a43b;
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
                <a href="register.php"><i class="far fa-user-plus"></i></a>
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
            <h1>🔐 تسجيل الدخول</h1>
            <p>مرحباً بعودتك! سجل دخولك للمتابعة</p>
            
            <?php if($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">دخول</button>
            </form>
            
            <div class="auth-link">
                ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a>
            </div>
            
            <div class="demo-info">
                <p><strong>💡 للتجربة:</strong></p>
                <p>يمكنك إنشاء حساب جديد أو استخدام حساب تجريبي (قم بإنشائه من صفحة التسجيل)</p>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container">
        <div class="copyright">
            <p>© 2024 صابونيتي. صنع بعناية من مكونات طبيعية 100%</p>
        </div>
    </div>
</footer>

</body>
</html>