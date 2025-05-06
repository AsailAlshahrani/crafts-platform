<?php
session_start();
include 'config.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $region = $_POST['region'];
    $craftType = $_POST['craftType'];
    $craftDescription = $_POST['craftDescription'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $createdDate = date('Y-m-d H:i:s');

    if ($password !== $confirmPassword) {
        $errorMessage = "كلمة المرور وتأكيد كلمة المرور غير متطابقتين!";
    } else {
        // هل البريد موجود مسبقاً؟
        $check = $conn->prepare("SELECT * FROM new_users WHERE Email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "هذا البريد الإلكتروني مسجل مسبقًا.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // إضافة المستخدم أولاً
            $stmt1 = $conn->prepare("INSERT INTO new_users (Email, PasswordHash, Role, CreatedDate) VALUES (?, ?, 'Craftsman', ?)");
            $stmt1->bind_param("sss", $email, $hashedPassword, $createdDate);

            if ($stmt1->execute()) {
                $userId = $stmt1->insert_id;

                // معالجة ملفات البورتفوليو
                $portfolioFiles = $_FILES['portfolioFiles'];
                $filePaths = [];
                $uploadDir = 'uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                for ($i = 0; $i < count($portfolioFiles['name']); $i++) {
                    $fileName = basename($portfolioFiles['name'][$i]);
                    $targetFilePath = $uploadDir . time() . "_" . $fileName;
                    if (move_uploaded_file($portfolioFiles['tmp_name'][$i], $targetFilePath)) {
                        $filePaths[] = $targetFilePath;
                    }
                }

                $portfolioFileStr = implode(",", $filePaths);

                // إدخال بيانات الحرفي
                $stmt2 = $conn->prepare("INSERT INTO new_artisans (UserId, FullName, Email, Region, CraftType, CraftDescription, PortfolioFile, PasswordHash, CreatedDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("issssssss", $userId, $fullName, $email, $region, $craftType, $craftDescription, $portfolioFileStr, $hashedPassword, $createdDate);

                if ($stmt2->execute()) {
                    $successMessage = "✅ تم إرسال بياناتك بنجاح!";
                } else {
                    $errorMessage = "❌ فشل في حفظ بيانات الحرفي: " . $stmt2->error;
                }
                $stmt2->close();
            } else {
                $errorMessage = "❌ فشل في إنشاء حساب المستخدم: " . $stmt1->error;
            }

            $stmt1->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الحرفي</title>
    <style>
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

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: rgb(192, 31, 31);
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
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            text-align: right;
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
            background-color: #4b5943;
        }

        .required {
            color: red;
            font-size: 18px;
        }

        .success-message, .error-message {
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        <img src=http://localhost/crafts-platform/uploads/sanna.png alt="شعار" class="logo">
    </div>
    <div class="header">
        <h1>تسجيل الحرفي</h1>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-message"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="error-message"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="fullName">الاسم الكامل <span class="required">*</span></label>
            <input type="text" id="fullName" name="fullName" placeholder="أدخل اسمك الكامل" required>
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
                <option value="الرياض">الرياض</option>
                <option value="جدة">جدة</option>
                <option value="الدمام">الدمام</option>
                <option value="بيشة">بيشة</option>
                <option value="مكة">مكة</option>
                <option value="المدينة">المدينة</option>
            </select>
        </div>
        <div class="form-group">
            <label for="craftType">نوع الحرفة <span class="required">*</span></label>
            <input type="text" id="craftType" name="craftType" placeholder="أدخل نوع الحرفة" required>
        </div>
        <div class="form-group">
            <label for="craftDescription">وصف الحرفة <span class="required">*</span></label>
            <textarea id="craftDescription" name="craftDescription" placeholder="أدخل وصفًا لحرفتك" required></textarea>
        </div>
        <div class="form-group">
            <label for="portfolioFiles">أرفق ملف الأعمال <span class="required">*</span></label>
            <input type="file" id="portfolioFiles" name="portfolioFiles[]" multiple required>
        </div>
        <button type="submit">إرسال</button>
    </form>
</div>

<a href="login.php" class="arrow-button">→</a>
</body>
</html>
