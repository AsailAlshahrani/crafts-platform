<?php
session_start();

$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

$data = [];
$validTypes = ['users', 'profiles', 'artisans', 'courses'];
if (!in_array($type, $validTypes) || $id <= 0) {
    die("طلب غير صالح.");
}

if ($type === 'users') {
    $query = "SELECT UserId, Name, Email FROM new_users WHERE UserId = $id";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
} elseif ($type === 'profiles') {
    $query = "SELECT UserId, firstName, lastName, email, region, interests FROM new_profiles WHERE UserId = $id";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
} elseif ($type === 'artisans') {
    $query = "SELECT ArtisanId, FullName, Email, Region, CraftType, CraftDescription FROM new_artisans WHERE ArtisanId = $id";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
} elseif ($type === 'courses') {
    $query = "SELECT CourseId, Title, Description, CraftsmanId, Price FROM new_courses WHERE CourseId = $id";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
}

if (empty($data)) {
    die("لم يتم العثور على بيانات لهذا العنصر.");
}

function getCraftsmanName($craftsmanId, $conn) {
    $q = "SELECT FullName FROM new_artisans WHERE ArtisanId = " . intval($craftsmanId);
    $res = $conn->query($q);
    $r = $res->fetch_assoc();
    return $r['FullName'] ?? 'غير معروف';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عرض التفاصيل</title>
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
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        h2 {
            color: #8B0000;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
        }

        .detail-item {
            background-color: #FFF;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .detail-item strong {
            color: #8B0000;
        }

        .btn-back {
            margin-top: 20px;
            background-color: #5E6F52;
            color: white;
            padding: 10px 25px;
            text-decoration: none;
            border-radius: 12px;
            display: block;
            text-align: center;
        }

        .btn-back:hover {
            background-color: #4b5943;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>تفاصيل العنصر</h2>
    <div class="detail-item">
        <?php if ($type === 'courses'): ?>
            <p><strong>اسم الدورة:</strong> <?= htmlspecialchars($data['Title']) ?></p>
            <p><strong>الوصف:</strong> <?= htmlspecialchars($data['Description']) ?></p>
            <p><strong>السعر:</strong> <?= htmlspecialchars($data['Price']) ?> ريال</p>

        <?php elseif ($type === 'users'): ?>
            <p><strong>الاسم:</strong> <?= htmlspecialchars($data['Name']) ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($data['Email']) ?></p>

        <?php elseif ($type === 'profiles'): ?>
            <p><strong>الاسم الأول:</strong> <?= htmlspecialchars($data['firstName']) ?></p>
            <p><strong>الاسم الأخير:</strong> <?= htmlspecialchars($data['lastName']) ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($data['email']) ?></p>
            <p><strong>المنطقة:</strong> <?= htmlspecialchars($data['region']) ?></p>
            <p><strong>الاهتمامات:</strong> <?= htmlspecialchars($data['interests']) ?></p>

        <?php elseif ($type === 'artisans'): ?>
            <p><strong>الاسم:</strong> <?= htmlspecialchars($data['FullName']) ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($data['Email']) ?></p>
            <p><strong>المنطقة:</strong> <?= htmlspecialchars($data['Region']) ?></p>
            <p><strong>نوع الحرفة:</strong> <?= htmlspecialchars($data['CraftType']) ?></p>
            <p><strong>وصف الحرفة:</strong> <?= htmlspecialchars($data['CraftDescription']) ?></p>
        <?php endif; ?>
    </div>
    <a href="admin_dashboard.php" class="btn-back">رجوع إلى لوحة التحكم</a>
</div>

</body>
</html>
