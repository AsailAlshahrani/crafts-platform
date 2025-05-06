<?php
session_start();
$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$data = [];
$success = "";
$error = "";
$validTypes = ['profiles', 'artisans', 'courses'];

if (!in_array($type, $validTypes) || $id <= 0) {
    die("طلب غير صالح.");
}

if ($type === 'profiles') {
    $stmt = $conn->prepare("SELECT firstName, lastName, email, region, interests FROM new_profiles WHERE UserId = ?");
    $stmt->bind_param("i", $id);
} elseif ($type === 'artisans') {
    $stmt = $conn->prepare("SELECT FullName, Email, Region, CraftType, CraftDescription FROM new_artisans WHERE ArtisanId = ?");
    $stmt->bind_param("i", $id);
} elseif ($type === 'courses') {
    $stmt = $conn->prepare("SELECT Title, Description, Price FROM new_courses WHERE CourseId = ?");
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'profiles') {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $region = $_POST['region'];
        $interests = $_POST['interests'];

        $stmt = $conn->prepare("UPDATE new_profiles SET firstName=?, lastName=?, email=?, region=?, interests=? WHERE UserId=?");
        $stmt->bind_param("sssssi", $firstName, $lastName, $email, $region, $interests, $id);

    } elseif ($type === 'artisans') {
        $fullName = $_POST['fullName'];
        $email = $_POST['email'];
        $region = $_POST['region'];
        $craftType = $_POST['craftType'];
        $craftDescription = $_POST['craftDescription'];

        $stmt = $conn->prepare("UPDATE new_artisans SET FullName=?, Email=?, Region=?, CraftType=?, CraftDescription=? WHERE ArtisanId=?");
        $stmt->bind_param("sssssi", $fullName, $email, $region, $craftType, $craftDescription, $id);

    } elseif ($type === 'courses') {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $price = $_POST['price'];

        $stmt = $conn->prepare("UPDATE new_courses SET Title=?, Description=?, Price=? WHERE CourseId=?");
        $stmt->bind_param("ssdi", $title, $desc, $price, $id);
    }

    if ($stmt->execute()) {
        $success = "✅ تم التعديل بنجاح.";
    } else {
        $error = "❌ فشل التعديل: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل <?= $type ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FCF9DE;
            font-family: 'Tajawal', sans-serif;
            padding: 60px 20px;
        }

        .container {
            background-color: #A9B387;
            border-radius: 20px;
            padding: 30px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #8B0000;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
        }

        .btn-save {
            background-color: #5E6F52;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 10px;
        }

        .btn-back {
            background-color: #7C9473;
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 10px;
        }

        .btn-back:hover {
            background-color: #68885f;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>تعديل <?= $type === 'profiles' ? 'المستفيد' : ($type === 'artisans' ? 'الحرفي' : 'الدورة') ?></h2>

        <?php if ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php if ($type === 'profiles'): ?>
                <div class="mb-3"><label>الاسم الأول</label>
                    <input type="text" name="firstName" class="form-control" value="<?= $data['firstName'] ?>" required></div>
                <div class="mb-3"><label>الاسم الأخير</label>
                    <input type="text" name="lastName" class="form-control" value="<?= $data['lastName'] ?>" required></div>
                <div class="mb-3"><label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="<?= $data['email'] ?>" required></div>
                <div class="mb-3"><label>المنطقة</label>
                    <input type="text" name="region" class="form-control" value="<?= $data['region'] ?>"></div>
                <div class="mb-3"><label>الاهتمامات</label>
                    <textarea name="interests" class="form-control"><?= $data['interests'] ?></textarea></div>

            <?php elseif ($type === 'artisans'): ?>
                <div class="mb-3"><label>الاسم الكامل</label>
                    <input type="text" name="fullName" class="form-control" value="<?= $data['FullName'] ?>" required></div>
                <div class="mb-3"><label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="<?= $data['Email'] ?>" required></div>
                <div class="mb-3"><label>المنطقة</label>
                    <input type="text" name="region" class="form-control" value="<?= $data['Region'] ?>"></div>
                <div class="mb-3"><label>نوع الحرفة</label>
                    <input type="text" name="craftType" class="form-control" value="<?= $data['CraftType'] ?>"></div>
                <div class="mb-3"><label>وصف الحرفة</label>
                    <textarea name="craftDescription" class="form-control"><?= $data['CraftDescription'] ?></textarea></div>

            <?php elseif ($type === 'courses'): ?>
                <div class="mb-3"><label>اسم الدورة</label>
                    <input type="text" name="title" class="form-control" value="<?= $data['Title'] ?>" required></div>
                <div class="mb-3"><label>الوصف</label>
                    <textarea name="description" class="form-control" required><?= $data['Description'] ?></textarea></div>
                <div class="mb-3"><label>السعر</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?= $data['Price'] ?>" required></div>
            <?php endif; ?>

            <div class="text-center">
                <button type="submit" class="btn btn-save">حفظ التعديلات</button><br>
                <a href="admin_dashboard.php" class="btn-back">رجوع إلى لوحة التحكم</a>
            </div>
        </form>
    </div>
</body>
</html>
