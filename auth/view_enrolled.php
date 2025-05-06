<?php
session_start();

// التأكد من أن المستخدم حرفي
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsman') {
    header("Location: login.php");
    exit();
}

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// جلب معرف الحرفي صاحب الدورة
$stmtOwner = $conn->prepare("SELECT CraftsmanId FROM new_courses WHERE CourseId = ?");
$stmtOwner->bind_param("i", $courseId);
$stmtOwner->execute();
$resultOwner = $stmtOwner->get_result();
$courseOwnerId = $resultOwner->fetch_assoc()['CraftsmanId'] ?? 0;
$stmtOwner->close();

// جلب بيانات المسجلين في الدورة
$enrollments = [];
$stmt = $conn->prepare("
    SELECT 
        e.EnrollmentDate,
        CONCAT(p.firstName, ' ', p.lastName) AS fullName,
        p.email
    FROM new_enrollments e
    LEFT JOIN new_profiles p ON p.UserId = e.CraftspersonId
    WHERE e.CourseId = ? AND e.CraftspersonId != ?
");
$stmt->bind_param("ii", $courseId, $courseOwnerId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $enrollments[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>المسجلين في الدورة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #FCF9DE;
            font-family: 'Tajawal', sans-serif;
            padding: 60px 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 40px 30px;
            max-width: 1000px;
            margin: auto;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        h2 {
            text-align: center;
            color: #4a4a4a;
            margin-bottom: 30px;
        }
        .table th {
            background-color: #7C9473;
            color: white;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .table td {
            text-align: center;
            vertical-align: middle;
            background-color: #f9f9f9;
        }
        .no-enrollments {
            text-align: center;
            color: #777;
            font-size: 18px;
            margin-top: 20px;
        }
        .btn-back {
            display: block;
            margin: 30px auto 0;
            background-color: #5E6F52;
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn-back:hover {
            background-color: #4b5943;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>قائمة المسجلين في الدورة</h2>

    <?php if (count($enrollments) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>الاسم الكامل</th>
                        <th>البريد الإلكتروني</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrolled): ?>
                        <tr>
                            <td><?= htmlspecialchars($enrolled['fullName']) ?></td>
                            <td><?= htmlspecialchars($enrolled['email']) ?></td>
                            <td><?= htmlspecialchars($enrolled['EnrollmentDate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="no-enrollments">لا يوجد مستفيدين مسجلين في هذه الدورة بعد.</p>
    <?php endif; ?>

    <a href="artisan_dashboard.php" class="btn-back">→ رجوع إلى لوحة التحكم</a>
</div>

</body>
</html>
