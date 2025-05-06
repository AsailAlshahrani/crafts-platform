<?php
session_start();
include 'config.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];
    $region = $_POST["region"];
    $interests = $_POST["interests"];

    if ($password !== $confirmPassword) {
        $error = "كلمة المرور غير متطابقة.";
    } else {
        $check = $conn->prepare("SELECT * FROM new_users WHERE Email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "هذا البريد الإلكتروني مسجل مسبقًا.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // أولاً: أضف المستخدم إلى new_users
            $stmt1 = $conn->prepare("INSERT INTO new_users (Email, PasswordHash, Role, CreatedDate) VALUES (?, ?, 'Craftsperson', NOW())");
            $stmt1->bind_param("ss", $email, $hashedPassword);
            if ($stmt1->execute()) {
                $userId = $stmt1->insert_id;

                // ثانياً: أضف إلى new_profiles باستخدام UserId من الجدول الأول
                $stmt2 = $conn->prepare("INSERT INTO new_profiles (UserId, firstName, lastName, email, passwordHash, region, interests) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("issssss", $userId, $firstName, $lastName, $email, $hashedPassword, $region, $interests);
                if ($stmt2->execute()) {
                    $success = "✅ تم إنشاء الحساب بنجاح!";
                    $_POST = [];
                } else {
                    $error = "❌ حدث خطأ عند حفظ البيانات الإضافية: " . $stmt2->error;
                }
                $stmt2->close();
            } else {
                $error = "❌ لم يتم إنشاء المستخدم: " . $stmt1->error;
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
    <title>تسجيل مستخدم جديد</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #FCF9DE;
            direction: rtl;
            color: #000;
        }

        .logo {
            display: block;
            margin: 30px auto;
            width: 150px;
        }

        .login-container {
            width: 450px;
            margin: 20px auto;
            background: #A9B387;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #8B0000;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: right;
        }

        label {
            display: block;
            font-weight: bold;
        }

        label.required::after {
            content: " *";
            color: red;
        }

        input, textarea, select {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 0 auto;
            text-align: right;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .btn {
            background-color: #5E6F52;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #4b5943;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        /* زر الرجوع الدائري */
        .floating-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background-color: #5E6F52;
            color: white;
            font-size: 28px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            transition: background-color 0.3s ease;
            z-index: 999;
        }

        .floating-btn:hover {
            background-color: #4b5943;
        }
    </style>
</head>
<body>

    <img src="http://localhost/crafts-platform/uploads/sanna.png" alt="شعار الموقع" class="logo">

    <div class="login-container">
        <h1>تسجيل مستخدم جديد</h1>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="message error"><?= $error ?></div> 
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="required" for="firstName">الاسم الأول:</label>
                <input type="text" name="firstName" id="firstName" value="<?= $_POST['firstName'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label class="required" for="lastName">الاسم الأخير:</label>
                <input type="text" name="lastName" id="lastName" value="<?= $_POST['lastName'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label class="required" for="email">البريد الإلكتروني:</label>
                <input type="email" name="email" id="email" value="<?= $_POST['email'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label class="required" for="password">كلمة المرور:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label class="required" for="confirmPassword">تأكيد كلمة المرور:</label>
                <input type="password" name="confirmPassword" id="confirmPassword" required>
            </div>
            <div class="form-group">
                <label class="required" for="region">المنطقة:</label>
                <select name="region" id="region" required>
                    <option value="">-- اختر المنطقة --</option>
                    <?php
                    $regions = ["الرياض", "مكة المكرمة", "المدينة المنورة", "الشرقية", "عسير", "القصيم", "تبوك", "نجران", "الباحة"];
                    foreach ($regions as $r) {
                        $selected = (isset($_POST['region']) && $_POST['region'] == $r) ? 'selected' : '';
                        echo "<option value='$r' $selected>$r</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="interests">الاهتمامات:</label>
                <textarea name="interests" id="interests" rows="3"><?= $_POST['interests'] ?? '' ?></textarea>
            </div>
            <button type="submit" class="btn">تسجيل</button>
        </form>
    </div>

    <!-- زر الرجوع العائم -->
    <a href="login.php" class="floating-btn" title="رجوع">&rarr;</a>

</body>
</html>
