<?php
session_start();

if (
    !isset($_SESSION['user']) ||
    (
        ($_SESSION['user']['Role'] ?? '') !== 'Craftsman'
        && !isset($_SESSION['user']['ArtisanId'])
    )
) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$artisanId = $_SESSION['user']['UserId'] ?? $_SESSION['user']['ArtisanId'] ?? 0;
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $fileName = '';

    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/courses/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['course_file']['name']);
        $fileName = time() . "_" . $originalName;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['course_file']['tmp_name'], $targetPath)) {
            // الملف تم رفعه بنجاح
        } else {
            $fileName = '';
        }
    }

    $stmt = $conn->prepare("INSERT INTO new_courses (Title, Description, Price, CraftsmanId, File) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $title, $description, $price, $artisanId, $fileName);
    if ($stmt->execute()) {
        $successMessage = "تمت إضافة الدورة بنجاح.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة دورة جديدة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #FCF9DE;
            font-family: 'Tajawal', sans-serif;
            padding: 80px 20px;
        }
        .container {
            background-color: #A9B387;
            border-radius: 20px;
            padding: 30px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #8B0000;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        label {
            font-weight: bold;
            color: #333;
        }
        .form-control {
            border-radius: 10px;
        }
        .btn-submit {
            background-color: #5E6F52;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 30px;
            margin-top: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-submit:hover {
            background-color: #4b5943;
        }
        .logo {
            display: block;
            margin: 0 auto 30px;
            max-height: 100px;
        }
        .success-msg {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
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

<a href="artisan_dashboard.php" class="arrow-button">→</a>

<img src=http://localhost/crafts-platform/uploads/sanna.png alt="شعار صنعة" class="logo">

<div class="container">
    <h2>إضافة دورة جديدة</h2>

    <?php if (!empty($successMessage)): ?>
        <div class="success-msg"><?= $successMessage ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title">عنوان الدورة:</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description">وصف الدورة:</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="price">السعر (﷼):</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="course_file">ملف الدورة (اختياري):</label>
            <input type="file" class="form-control" id="course_file" name="course_file">
        </div>
        <button type="submit" class="btn btn-submit">إضافة</button>
    </form>
</div>

</body>
</html>
