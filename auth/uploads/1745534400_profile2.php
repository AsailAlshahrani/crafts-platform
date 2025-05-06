<?php
include 'test.connection.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $region = $_POST['region'];
    $interests = $_POST['interests'];

    if ($password !== $confirmPassword) {
        $errorMessage = "كلمتا المرور غير متطابقتين.";
    } else {
        // تحقق من وجود البريد مسبقاً
        $sql = "SELECT COUNT(*) FROM profiles WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($emailCount);
            $stmt->fetch();
            $stmt->close();

            if ($emailCount > 0) {
                $errorMessage = "البريد الإلكتروني موجود مسبقًا. من فضلك اختر بريدًا إلكترونيًا آخر.";
            } else {
                // تشفير كلمة المرور
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $insertSql = "INSERT INTO profiles (firstName, lastName, email, passwordHash, region, interests) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                if ($insertStmt) {
                    $insertStmt->bind_param("ssssss", $firstName, $lastName, $email, $passwordHash, $region, $interests);
                    if ($insertStmt->execute()) {
                        $successMessage = "تم إنشاء حسابك بنجاح!";
                    } else {
                        $errorMessage = "حدث خطأ أثناء الحفظ: " . $insertStmt->error;
                    }
                    $insertStmt->close();
                } else {
                    $errorMessage = "خطأ في تحضير الاستعلام: " . $conn->error;
                }
            }
        } else {
            $errorMessage = "خطأ في تحضير الاستعلام: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملفك الشخصي</title>
    <style>
        .logo-container {
            position: absolute;
            text-align: right;
            top: 10px;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 110px;
            margin-top: 32px;
            height: auto;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FCF9DE;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
        }

        .container {
            width: 80%;
            max-width: 800px;
            background-color: #A9B387;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        textarea {
            height: 150px;
        }

        button {
            background-color: #5E6F52;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .required {
            color: red;
            font-size: 18px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .arrow-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #5E6F52;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            text-align: center;
            line-height: 50px;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .arrow-button:hover {
            background-color: #3e4f3c;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo-container">
        <img src="http://localhost/my_project/images/sanaa.png" alt="شعار" class="logo">
    </div>
    <div class="header">
        <h1 style="color:rgb(192, 31, 31);">ملفك الشخصي</h1>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-message"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="error-message"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form id="profileForm" method="POST">
        <div class="form-group">
            <label for="firstName">الاسم <span class="required">*</span></label>
            <input type="text" id="firstName" name="firstName" placeholder="أدخل اسمك" required>
        </div>
        <div class="form-group">
            <label for="lastName">اسم العائلة <span class="required">*</span></label>
            <input type="text" id="lastName" name="lastName" placeholder="أدخل اسم العائلة" required>
        </div>
        <div class="form-group">
            <label for="email">البريد الإلكتروني <span class="required">*</span></label>
            <input type="email" id="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>
        </div>
        <div class="form-group">
            <label for="password">كلمة المرور <span class="required">*</span></label>
            <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
        </div>
        <div class="form-group">
            <label for="confirmPassword">تأكيد كلمة المرور <span class="required">*</span></label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="أعد إدخال كلمة المرور" required>
        </div>
        <div class="form-group">
            <label for="region">المنطقة <span class="required">*</span></label>
            <select id="region" name="region" required>
                <option value="">اختر المنطقة</option>
                <option value="riyadh">الرياض</option>
                <option value="jeddah">جدة</option>
                <option value="dammam">الدمام</option>
                <option value="bishah">بيشة</option>
            </select>
        </div>
        <div class="form-group">
            <label for="interests">صف اهتماماتك <span class="required">*</span></label>
            <textarea id="interests" name="interests" placeholder="أدخل اهتماماتك" required></textarea>
        </div>
        <button type="submit">حفظ</button>
    </form>
</div>

<a href="login.php" class="arrow-button">→</a>

</body>
</html>
