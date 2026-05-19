<?php
session_start();
require_once '../config/database.php';

if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin-index.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if($user && $password == $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            
            if(isset($_POST['remember_me'])) {
                setcookie('admin_remember', $username, time() + 2592000, '/');
            }
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}

if(!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['admin_remember'])) {
    $username = $_COOKIE['admin_remember'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if($user) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة التحكم | صابونيتي</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #faf8f5 0%, #f5eee6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            position: relative;
        }
        
        /* تصميم خلفية مميزة مثل باقي الموقع */
        body::before {
            content: "🧼";
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 120px;
            opacity: 0.05;
            pointer-events: none;
        }
        
        body::after {
            content: "🌸";
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 100px;
            opacity: 0.05;
            pointer-events: none;
        }
        
        .auth-container {
            max-width: 480px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08), 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #e8dfd3;
            padding: 50px 45px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .auth-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 45px rgba(0,0,0,0.1);
        }
        
        /* أيقونة رأسية */
        .logo-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-icon span {
            font-size: 3.5rem;
            background: #f5efe7;
            display: inline-block;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .auth-container h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-align: center;
            color: #4a3b2c;
        }
        
        .auth-container > p {
            color: #8b7355;
            margin-bottom: 35px;
            text-align: center;
            font-size: 0.85rem;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.85rem;
            color: #4a3b2c;
        }
        
        .form-group label i {
            color: #c6a43b;
            margin-left: 6px;
            width: 20px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e8dfd3;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #fefcf9;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #c6a43b;
            box-shadow: 0 0 0 4px rgba(198,164,59,0.1);
            background: white;
        }
        
        .form-group input::placeholder {
            color: #cbbfa8;
            font-size: 0.85rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #c6a43b;
        }
        
        .checkbox-group label {
            font-size: 0.85rem;
            color: #8b7355;
            cursor: pointer;
        }
        
        .btn-submit {
            width: 100%;
            background: #4a3b2c;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 40px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-submit:hover {
            background: #c6a43b;
            transform: scale(1.02);
        }
        
        .alert-error {
            background: #fef2f0;
            color: #c45a3b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            border: 1px solid #f5d5cc;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .alert-error i {
            font-size: 1rem;
        }
        
        .admin-badge {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #f0e8df;
            font-size: 0.75rem;
            color: #a18f76;
        }
        
        .admin-badge i {
            color: #c6a43b;
            margin-left: 5px;
        }
        
        /* روابط إضافية */
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #a18f76;
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #c6a43b;
        }
        
        /* Responsive */
        @media (max-width: 550px) {
            body {
                padding: 15px;
            }
            .auth-container {
                padding: 35px 25px;
            }
            .logo-icon span {
                width: 65px;
                height: 65px;
                line-height: 65px;
                font-size: 2.5rem;
            }
            .auth-container h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="logo-icon">
        <span>🧼</span>
    </div>
    
    <h1>تسجيل الدخول</h1>
    <p>أدخل بياناتك للدخول إلى لوحة التحكم</p>
    
    <?php if($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> اسم المستخدم</label>
            <input type="text" name="username" placeholder="أدخل اسم المستخدم" required autocomplete="off">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-lock"></i> كلمة المرور</label>
            <input type="password" name="password" placeholder="أدخل كلمة المرور" required>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" name="remember_me" id="remember_me">
            <label for="remember_me">تذكرني لمدة 30 يوم</label>
        </div>
        
        <button type="submit" class="btn-submit">
            <i class="fas fa-sign-in-alt"></i> دخول
        </button>
    </form>
    
    <div class="back-link">
        <a href="../index.php"><i class="fas fa-arrow-right"></i> العودة إلى المتجر</a>
    </div>
    
    <div class="admin-badge">
        <i class="fas fa-shield-alt"></i> هذه الصفحة مخصصة للمدير فقط
    </div>
</div>

</body>
</html>