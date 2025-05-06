<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_crafts_db";

// الاتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// دالة للبحث عن الإيميل في الجداول الثلاثة
function findUserByEmail($conn, $email) {
    $tables = ['new_users', 'new_profiles', 'new_artisans'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return true;
        }
    }
    return false;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!empty($email)) {
        if (findUserByEmail($conn, $email)) {
            $message = "✅ تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني (مؤقت).";
        } else {
            $message = "❌ البريد الإلكتروني غير موجود في النظام.";
        }
    } else {
        $message = "❗ الرجاء إدخال البريد الإلكتروني.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>استعادة كلمة المرور</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #FCF9DE;
            color: #000;
            line-height: 1.6;
            direction: rtl;
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
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="email"] {
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
            color: #fff;
            background-color: #5E6F52;
        }

        .btn:hover {
            background-color: #FCF9DE;
            color: #5E6F52;
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

        .message {
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>استعادة كلمة المرور</h1>
        <form method="POST">
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit" class="btn">إرسال رابط إعادة التعيين</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <div class="login-links">
            <a href="login.php">العودة إلى تسجيل الدخول</a>
        </div>
    </div>
</body>
</html>
