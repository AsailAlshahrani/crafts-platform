<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_crafts_db";

// الاتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// دالة للتحقق من المستخدم
function checkUser($conn, $table, $email, $pass, $roleColumn = 'Role', $roleValue = null) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $storedHash = $user['PasswordHash'] ?? '';
        if ((password_verify($pass, $storedHash) || $pass === $storedHash) &&
            (!$roleValue || ($user[$roleColumn] ?? '') === $roleValue)) {
            return $user;
        }
    }
    return null;
}

// تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['register'])) {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    // التحقق من كل الأدوار
    $user = checkUser($conn, 'new_users', $email, $pass, 'Role', 'Admin') ??
            checkUser($conn, 'new_users', $email, $pass, 'Role', 'Craftsman') ??
            checkUser($conn, 'new_users', $email, $pass, 'Role', 'Craftsperson') ??
            checkUser($conn, 'new_artisans', $email, $pass) ??
            checkUser($conn, 'new_profiles', $email, $pass);

    if ($user) {
        $role = $user['Role'] ?? 'Craftsperson';
        $_SESSION['user'] = $user;

        if ($role == "Admin") {
            header("Location: /crafts-platform/auth/admin_dashboard.php");
        } elseif ($role == "Craftsman") {
            header("Location: /crafts-platform/auth/artisan_dashboard.php");
        } elseif ($role == "Craftsperson") {
            header("Location: /crafts-platform/auth/main.php");
        } else {
            $_SESSION['error'] = "نوع المستخدم غير معروف.";
            header("Location: login.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        header("Location: login.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #FCF9DE;
            color: #000;
            line-height: 1.6;
            direction: rtl;
        }
        .logo {
            display: block;
            margin: 30px auto;
            width: 200px;
            height: auto;
        }
        .login-container {
            width: 400px;
            margin: 100px auto;
            background: #A9B387;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: right;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
            color: #000;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            color: #000;
        }
        .btn-primary {
            background-color: #5E6F52;
        }
        .btn-primary:hover {
            background-color: #FCF9DE;
        }
        .login-links {
            text-align: center;
            margin-top: 20px;
        }
        .login-links a {
            color: #5E6F52;
            text-decoration: none;
            margin: 0 10px;
        }
        .login-links a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <img src=http://localhost/crafts-platform/uploads/sanna.png alt="شعار الموقع" class="logo">

    <div class="login-container">
        <h1>تسجيل الدخول</h1>
        <form id="login-form" action="login.php" method="post">
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">تسجيل الدخول</button>
        </form>

        <?php 
        if (isset($_SESSION['error'])) { 
            echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        } 
        ?>

        <div class="login-links">
            <a href="forgot_password.php">هل نسيت كلمة المرور؟</a><br>
            <a href="register.php">تسجيل جديد</a><br>
            <a href="register_artisan.php">تسجيل كحرفي؟ هنا</a>
        </div>
    </div>
</body>
</html>
