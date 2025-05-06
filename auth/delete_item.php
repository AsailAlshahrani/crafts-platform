<?php
session_start();
$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$validTypes = ['profiles', 'artisans', 'courses'];

if (!in_array($type, $validTypes) || $id <= 0) {
    die("طلب غير صالح.");
}

$success = "";
$error = "";

// حذف بعد التأكيد
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    $conn->begin_transaction();
    try {
        if ($type === 'profiles') {
            // حذف من profiles ثم users
            $stmt1 = $conn->prepare("DELETE FROM new_profiles WHERE UserId = ?");
            $stmt1->bind_param("i", $id);
            $stmt1->execute();

            $stmt2 = $conn->prepare("DELETE FROM new_users WHERE UserId = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
        } elseif ($type === 'artisans') {
            // حذف من artisans ثم users
            $stmt1 = $conn->prepare("DELETE FROM new_artisans WHERE ArtisanId = ?");
            $stmt1->bind_param("i", $id);
            $stmt1->execute();

            $stmt2 = $conn->prepare("DELETE FROM new_users WHERE UserId = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
        } elseif ($type === 'courses') {
            $stmt = $conn->prepare("DELETE FROM new_courses WHERE CourseId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }

        $conn->commit();
        $success = "✅ تم الحذف بنجاح.";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "❌ فشل الحذف: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حذف <?= $type ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FCF9DE;
            font-family: 'Tajawal', sans-serif;
            padding: 80px 20px;
        }

        .container {
            background-color: #A9B387;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #8B0000;
            margin-bottom: 20px;
        }

        .btn-danger, .btn-secondary {
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
        }

        .btn-danger {
            background-color: #c0392b;
            color: white;
        }

        .btn-secondary {
            background-color: #5E6F52;
            color: white;
            margin-right: 10px;
        }

        .btn-secondary:hover {
            background-color: #4b5943;
        }

        .message {
            margin-top: 20px;
            font-weight: bold;
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
    <h2>حذف <?= $type === 'profiles' ? 'المستفيد' : ($type === 'artisans' ? 'الحرفي' : 'الدورة') ?></h2>

    <?php if (empty($success) && empty($error)): ?>
        <p>هل أنت متأكد أنك تريد حذف هذا العنصر؟ لا يمكن التراجع بعد الحذف.</p>
        <form method="POST">
            <button type="submit" name="confirm" class="btn btn-danger">نعم، احذف</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">إلغاء</a>
        </form>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="message success"><?= $success ?></div>
        <br>
        <a href="admin_dashboard.php" class="btn btn-secondary">رجوع إلى لوحة التحكم</a>
    <?php elseif (!empty($error)): ?>
        <div class="message error"><?= $error ?></div>
    <?php endif; ?>
</div>

</body>
</html>
