<?php
session_start();

// التحقق من الجلسة وصلاحية الدخول
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

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// تحديد معرف الحرفي
if ($_SESSION['user']['Role'] === 'Craftsman') {
    $artisanId = $_SESSION['user']['UserId']; 
} else {
    $artisanId = $_SESSION['user']['ArtisanId']; 
}

$successMessage = "";

// حذف دورة إن وجدت طلب حذف
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM new_courses WHERE CourseId = ? AND CraftsmanId = ?");
    $stmt->bind_param("ii", $deleteId, $artisanId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $successMessage = "تم حذف الدورة بنجاح.";
    }
    $stmt->close();
}

// ✅ جلب بيانات الحرفي سواء من new_users أو new_artisans
$stmt = $conn->prepare("
    SELECT 
        COALESCE(a.FullName, u.Name, 'بدون اسم') AS DisplayName,
        COALESCE(a.Email, u.Email) AS Email,
        a.Region,
        a.CraftType
    FROM new_users u
    LEFT JOIN new_artisans a ON a.ArtisanId = u.UserId
    WHERE u.UserId = ?
");
$stmt->bind_param("i", $artisanId);
$stmt->execute();
$artisan = $stmt->get_result()->fetch_assoc();
$stmt->close();

// جلب الدورات الخاصة بالحرفي
$stmt = $conn->prepare("SELECT * FROM new_courses WHERE CraftsmanId = ?");
$stmt->bind_param("i", $artisanId);
$stmt->execute();
$courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الحرفي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #FCF9DE;
            font-family: 'Tajawal', sans-serif;
            padding: 80px 20px 100px;
        }
        .container {
            background-color: #A9B387;
            border-radius: 20px;
            padding: 30px;
            max-width: 1000px;
            margin: auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #8B0000;
            font-weight: bold;
            text-align: center;
        }
        .artisan-info, .course-card {
            background-color: #FFF;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-custom, .btn-danger, .btn-secondary {
            border-radius: 10px;
            padding: 8px 18px;
            text-decoration: none;
            margin: 5px 2px;
        }
        .btn-custom {
            background-color: #5E6F52;
            color: white;
        }
        .btn-custom:hover {
            background-color: #4b5943;
        }
        .btn-danger {
            background-color: #c0392b;
            color: white;
        }
        .btn-danger:hover {
            background-color: #a93226;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .top-bar {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .logo {
            display: block;
            margin: 0 auto 30px;
            max-height: 100px;
        }
        .add-btn {
            background-color: #7C9473;
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            display: block;
            width: fit-content;
            margin: 0 auto 30px;
        }
        .add-btn:hover {
            background-color: #68885f;
        }
        .success-msg {
            color: green;
            text-align: center;
            font-weight: bold;
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

<div class="top-bar">
    <a href="login.php" class="btn btn-custom">تسجيل خروج</a>
</div>

<img src="http://localhost/crafts-platform/uploads/sanna.png" alt="شعار صنعة" class="logo">

<div class="container">
    <h2>لوحة تحكم الحرفي</h2>

    <?php if (!empty($successMessage)): ?>
        <div class="success-msg"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <div class="artisan-info">
        <h5><strong>الاسم:</strong> <?= htmlspecialchars($artisan['DisplayName'] ?? '-') ?></h5>
        <h5><strong>البريد:</strong> <?= htmlspecialchars($artisan['Email'] ?? '-') ?></h5>
        <?php if (isset($artisan['Region'])): ?>
            <h5><strong>المنطقة:</strong> <?= htmlspecialchars($artisan['Region']) ?></h5>
        <?php endif; ?>
        <?php if (isset($artisan['CraftType'])): ?>
            <h5><strong>نوع الحرفة:</strong> <?= htmlspecialchars($artisan['CraftType']) ?></h5>
        <?php endif; ?>
    </div>

    <a href="add_course.php" class="add-btn">إضافة دورة جديدة</a>

    <h4 style="color:#8B0000;">الدورات الحالية:</h4>
    <?php if ($courses->num_rows > 0): ?>
        <?php while ($course = $courses->fetch_assoc()): ?>
            <div class="course-card">
                <h5><strong><?= htmlspecialchars($course['Title']) ?></strong></h5>
                <p><strong>الوصف:</strong> <?= nl2br(htmlspecialchars($course['Description'])) ?></p>
                <p><strong>السعر:</strong> <?= htmlspecialchars($course['Price']) ?> <span style="font-family: Arial;">﷼</span></p>
                <a href="view_enrolled.php?id=<?= $course['CourseId'] ?>" class="btn btn-secondary">عرض المسجلين</a>
                <a href="edit_course.php?id=<?= $course['CourseId'] ?>" class="btn btn-custom">تعديل</a>
                <a href="?delete=<?= $course['CourseId'] ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الدورة؟')">حذف</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;">لا توجد دورات بعد.</p>
    <?php endif; ?>
</div>

<a href="login.php" class="arrow-button">→</a>

</body>
</html>
