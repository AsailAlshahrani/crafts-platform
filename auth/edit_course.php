<?php
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsman') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$artisanId = $_SESSION['user']['ArtisanId'] ?? $_SESSION['user']['UserId'] ?? 0;
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$successMessage = "";
$errorMessage = "";

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// جلب بيانات الدورة
$stmt = $conn->prepare("SELECT * FROM new_courses WHERE CourseId = ? AND CraftsmanId = ?");
$stmt->bind_param("ii", $courseId, $artisanId);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    die("❌ الدورة غير موجودة أو لا تملك صلاحية التعديل.");
}

// حذف المرفق إذا طُلب ذلك
if (isset($_POST['delete_attachment'])) {
    if (!empty($course['File']) && file_exists("uploads/" . $course['File'])) {
        unlink("uploads/" . $course['File']);
    }

    $stmt = $conn->prepare("UPDATE new_courses SET File = NULL WHERE CourseId = ? AND CraftsmanId = ?");
    $stmt->bind_param("ii", $courseId, $artisanId);
    $stmt->execute();
    $stmt->close();

    $course['File'] = "";
    $successMessage = "✅ تم حذف المرفق بنجاح.";
}

// تنفيذ التعديل إذا تم إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_changes'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    $filePath = $course['File'];

    // إذا تم رفع ملف جديد
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . "_" . basename($_FILES['attachment']['name']);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
            $filePath = $fileName;
        }
    }

    $stmt = $conn->prepare("UPDATE new_courses SET Title = ?, Description = ?, Price = ?, File = ? WHERE CourseId = ? AND CraftsmanId = ?");
    $stmt->bind_param("ssdssi", $title, $description, $price, $filePath, $courseId, $artisanId);

    if ($stmt->execute()) {
        $successMessage = "✅ تم تحديث بيانات الدورة بنجاح.";
        $course['Title'] = $title;
        $course['Description'] = $description;
        $course['Price'] = $price;
        $course['File'] = $filePath;
    } else {
        $errorMessage = "❌ فشل التحديث: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل دورة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FCF9DE;
            font-family: 'Tajawal', sans-serif;
            padding: 60px;
        }

        .container {
            background-color: #A9B387;
            border-radius: 15px;
            padding: 30px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #8B0000;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
        }

        .form-control {
            margin-bottom: 15px;
        }

        .btn-custom {
            background-color: #5E6F52;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 16px;
        }

        .btn-custom:hover {
            background-color: #4b5943;
        }

        .msg {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .msg.success {
            color: green;
        }

        .msg.error {
            color: red;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            text-decoration: none;
            color: #5E6F52;
            font-weight: bold;
        }

        .file-preview {
            margin: 10px 0;
        }

        .file-preview a {
            text-decoration: underline;
            color: #8B0000;
        }

        .delete-attachment {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            display: inline-block;
            background: none;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>تعديل بيانات الدورة</h2>

    <?php if ($successMessage): ?>
        <div class="msg success"><?= $successMessage ?></div>
    <?php elseif ($errorMessage): ?>
        <div class="msg error"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="save_changes" value="1">

        <label for="title">اسم الدورة:</label>
        <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($course['Title']) ?>" required>

        <label for="description">الوصف:</label>
        <textarea name="description" id="description" class="form-control" required><?= htmlspecialchars($course['Description']) ?></textarea>

        <label for="price">السعر:</label>
        <input type="number" name="price" id="price" class="form-control" value="<?= htmlspecialchars($course['Price']) ?>" required>
        <small class="text-muted">ريال سعودي (﷼)</small>

        <label for="attachment">رفع أو تعديل ملف :</label>
        <input type="file" name="attachment" id="attachment" class="form-control">

        <?php if (!empty($course['File'])): ?>
            <div class="file-preview">
                الملف الحالي: <a href="uploads/<?= $course['File'] ?>" target="_blank">عرض / تحميل</a><br>
                <form method="POST" enctype="multipart/form-data" style="display:inline;">
                    <input type="hidden" name="delete_attachment" value="1">
                    <button type="submit" class="delete-attachment" onclick="return confirm('هل أنت متأكد من حذف الملف؟')">🗑 حذف المرفق الحالي</button>
                </form>
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <button type="submit" class="btn btn-custom">حفظ التعديلات</button>
        </div>
    </form>

    <div class="back-link">
        <a href="artisan_dashboard.php">→ رجوع إلى لوحة التحكم</a>
    </div>
</div>

</body>
</html>
