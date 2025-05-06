<?php
session_start();
require_once('config.php');

// تحقق من تسجيل الدخول وصلاحية المستخدم
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsperson') {
    header("Location: ../login.php");
    exit();
}

$errorMessage = '';
$courseId = '';
$price = '';
$title = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['course_id'], $_GET['price'], $_GET['title'])) {
        header("Location: main.php");
        exit();
    }

    $courseId = (int) $_GET['course_id'];
    $price = htmlspecialchars($_GET['price']);
    $title = htmlspecialchars($_GET['title']);

    $_SESSION['payment_course'] = [
        'course_id' => $courseId,
        'price' => $price,
        'title' => $title
    ];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['payment_course'])) {
        header("Location: main.php");
        exit();
    }

    $courseId = $_SESSION['payment_course']['course_id'];
    $price = $_SESSION['payment_course']['price'];
    $title = $_SESSION['payment_course']['title'];

    $cardNumber = trim($_POST['card_number'] ?? '');
    $cardHolder = trim($_POST['card_holder'] ?? '');
    $expiryDate = trim($_POST['expiry_date'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');

    if (!preg_match('/^\d{16}$/', $cardNumber)) {
        $errorMessage = "رقم البطاقة يجب أن يكون 16 رقمًا.";
    } elseif (!preg_match('/^[\p{L} ]+$/u', $cardHolder)) {
        $errorMessage = "اسم حامل البطاقة غير صالح.";
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiryDate)) {
        $errorMessage = "تاريخ الانتهاء يجب أن يكون بالشكل MM/YY.";
    } else {
        // التحقق من أن تاريخ البطاقة لم ينتهِ بعد
        [$expMonth, $expYear] = explode('/', $expiryDate);
        $expMonth = (int) $expMonth;
        $expYear = (int) ('20' . $expYear); // تحويل YY إلى YYYY

        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
            $errorMessage = "انتهت صلاحية البطاقة.";
        } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
            $errorMessage = "رمز الأمان يجب أن يكون 3 أو 4 أرقام.";
        } else {
            $userId = $_SESSION['user']['UserId'];

            $checkEnroll = $conn->prepare("SELECT * FROM new_enrollments WHERE CourseId = ? AND CraftspersonId = ?");
            if (!$checkEnroll) {
                die("خطأ في الاستعلام (التحقق): " . $conn->error);
            }
            $checkEnroll->bind_param("ii", $courseId, $userId);
            $checkEnroll->execute();
            $enrollResult = $checkEnroll->get_result();

            if ($enrollResult->num_rows === 0) {
                $insertEnroll = $conn->prepare("INSERT INTO new_enrollments (CourseId, CraftspersonId, EnrollmentDate) VALUES (?, ?, NOW())");
                if (!$insertEnroll) {
                    die("خطأ في الاستعلام (الإدخال): " . $conn->error);
                }
                $insertEnroll->bind_param("ii", $courseId, $userId);
                $insertEnroll->execute();
                $insertEnroll->close();
            }

            $checkEnroll->close();

            $_SESSION['enrolled_course'] = [
                'course_id' => $courseId,
                'title' => $title,
                'price' => $price
            ];

            unset($_SESSION['payment_course']);

            header("Location: /crafts-platform/auth/course_started.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>دفع الدورة - صنعة</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
body {
    background-color: #FCF9DE;
    font-family: 'Tajawal', sans-serif;
    margin: 0;
    padding: 0;
}
.menu-icon {
    position: fixed;
    top: 20px;
    right: 20px;
    cursor: pointer;
    z-index: 1001;
}
.menu-icon div {
    width: 30px;
    height: 4px;
    background-color: #5E6F52;
    margin: 6px 0;
    border-radius: 2px;
}
.sidebar {
    height: 100%;
    width: 0;
    position: fixed;
    top: 0;
    right: 0;
    background-color: #A9B387;
    overflow-x: hidden;
    transition: 0.3s;
    padding-top: 60px;
    z-index: 1000;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.sidebar.open {
    width: 250px;
}
.sidebar .user-info {
    margin-bottom: 40px;
    font-size: 22px;
    color: #fff;
    font-weight: bold;
}
.sidebar a {
    padding: 12px;
    text-decoration: none;
    font-size: 20px;
    color: #333;
    display: block;
    transition: 0.3s;
    font-weight: bold;
    margin-top: 10px;
}
.sidebar a:hover {
    background-color: #5E6F52;
    color: #fff;
    border-radius: 8px;
}
.logout-btn {
    margin-bottom: 20px;
    background-color: #c0392b;
    padding: 10px;
    border-radius: 10px;
    color: white;
    font-weight: bold;
    width: 80%;
    margin: 20px auto;
    text-align: center;
}
.payment-container {
    background-color: #A9B387;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 400px;
    max-width: 90%;
    margin: 100px auto;
}
.payment-container h1 {
    font-size: 30px;
    margin-bottom: 10px;
    font-weight: bold;
    color: #2C3E50;
}
.payment-container p.course-title {
    font-size: 22px;
    margin-bottom: 10px;
    font-weight: bold;
    color: #34495e;
}
.payment-container p.amount {
    font-size: 20px;
    margin-bottom: 20px;
    color: #34495e;
}
.payment-container input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
.payment-container .buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}
.payment-container .buttons .btn {
    width: 48%;
    font-size: 18px;
    font-weight: bold;
    border-radius: 8px;
    background-color: #4b5943;
    color: white;
}
.payment-container .buttons .btn:hover {
    background-color: #3e4d39;
}
.back-btn {
    background-color: #c0392b;
    color: white;
    text-decoration: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 20px;
    font-weight: bold;
    width: 100%;
    display: inline-block;
    margin-top: 20px;
}
.back-btn:hover {
    background-color: #922b21;
}
.alert {
    margin-top: 10px;
}
</style>
</head>

<body>

<div class="menu-icon" onclick="toggleSidebar()">
    <div></div>
    <div></div>
    <div></div>
</div>

<div id="mySidebar" class="sidebar">
    <div>
        <div class="user-info"><?= htmlspecialchars($_SESSION['user']['Name'] ?? 'مستخدم') ?></div>
        <a href="profile.php">ملفك الشخصي</a>
        <a href="my_courses.php">الدورات المسجلة</a>
        <a href="favorites.php">المفضلة</a>
    </div>
    <a href="login.php" class="logout-btn">تسجيل خروج</a>
</div>

<div class="payment-container">
    <h1>دفع الدورة</h1>
    <p class="course-title"><?= $title ?></p>
    <p class="amount">المبلغ المطلوب: <strong><?= $price ?> ﷼</strong></p>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="card_number" placeholder="رقم البطاقة (16 رقمًا)" required>
        <input type="text" name="card_holder" placeholder="اسم حامل البطاقة" required>
        <input type="text" name="expiry_date" placeholder="تاريخ الانتهاء (MM/YY)" required>
        <input type="text" name="cvv" placeholder="رمز الأمان (CVV)" required>

        <div class="buttons">
            <button type="submit" class="btn">ادفع الآن</button>
            <a href="https://wa.me/966555798407" target="_blank" class="btn">تواصل</a>
        </div>
    </form>

    <a href="main.php" class="back-btn">عودة</a>
</div>

<script>
function toggleSidebar() {
    document.getElementById("mySidebar").classList.toggle("open");
}
</script>

</body>
</html>
