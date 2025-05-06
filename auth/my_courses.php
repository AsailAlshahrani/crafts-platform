<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsperson') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$userId = $_SESSION['user']['UserId'];

// جلب الدورات المسجل فيها المستخدم
$stmt = $conn->prepare("
    SELECT c.CourseId, c.Title, c.Description, e.EnrollmentDate
    FROM new_enrollments e
    INNER JOIN new_courses c ON e.CourseId = c.CourseId
    WHERE e.CraftspersonId = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الدورات المسجلة - صنعة</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
body {
    background-color: #FCF9DE;
    font-family: 'Tajawal', sans-serif;
    transition: margin-right 0.3s;
    padding-top: 80px;
}
.logo {
    display: block;
    margin: 0 auto 20px;
    height: 90px;
}
.menu-icon {
    position: fixed;
    top: 20px;
    right: 20px;
    cursor: pointer;
    z-index: 999;
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
}
.user-info {
    margin-bottom: 40px;
    font-size: 22px;
    color: #fff;
    font-weight: bold;
}
.logout-btn {
    background-color: #c0392b;
    padding: 10px;
    border-radius: 10px;
    color: white;
    font-weight: bold;
    width: 80%;
    margin: 20px auto;
    display: block;
}
.container {
    background-color: #A9B387;
    padding: 30px;
    border-radius: 20px;
    max-width: 1000px;
    margin: auto;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #8B0000;
    font-weight: bold;
    font-size: 32px;
}
.course-card {
    background-color: #fff;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    text-align: right;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.course-title {
    font-size: 24px;
    font-weight: bold;
    color: #5E6F52;
}
.course-desc {
    margin: 10px 0;
    font-size: 18px;
    color: #333;
}
.enroll-date {
    font-size: 16px;
    color: #555;
}
.btn-custom {
    background-color: #5E6F52;
    color: white;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 18px;
    font-weight: bold;
    margin-top: 15px;
    text-decoration: none;
    display: inline-block;
}
.btn-custom:hover {
    background-color: #4b5943;
}
.btn-new {
    background-color: #8B0000;
    margin: 30px auto 0;
    display: block;
    width: fit-content;
    padding: 12px 30px;
}
</style>
<script>
function toggleSidebar() {
    document.getElementById("mySidebar").classList.toggle("open");
    if (document.getElementById("mySidebar").classList.contains("open")) {
        document.body.style.marginRight = "250px";
    } else {
        document.body.style.marginRight = "0";
    }
}
</script>
</head>

<body>

<img src=http://localhost/crafts-platform/uploads/sanna.png alt="شعار صنعة" class="logo">

<!-- القائمة -->
<div class="menu-icon" onclick="toggleSidebar()">
    <div></div>
    <div></div>
    <div></div>
</div>

<div id="mySidebar" class="sidebar">
    <div>
        <div class="user-info"><?= htmlspecialchars($_SESSION['user']['Name'] ?? '-') ?></div>
        <a href="profile.php">ملفك الشخصي</a>
        <a href="my_courses.php">الدورات المسجلة</a>
        <a href="favorites.php">المفضلة</a>
    </div>
    <a href="login.php" class="logout-btn">تسجيل خروج</a>
</div>

<div class="container">
    <h2>الدورات المسجلة</h2>

    <?php if (empty($courses)): ?>
        <p style="text-align: center;">لا توجد دورات مسجلة حتى الآن.</p>
    <?php else: ?>
        <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <div class="course-title"><?= htmlspecialchars($course['Title']) ?></div>
                <div class="course-desc"><?= htmlspecialchars($course['Description']) ?></div>
                <div class="enroll-date">تاريخ التسجيل: <?= htmlspecialchars(date('Y-m-d', strtotime($course['EnrollmentDate']))) ?></div>
                <a href="course_started.php?course_id=<?= $course['CourseId'] ?>" class="btn-custom"> متابعة الدورة</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="main.php" class="btn-custom btn-new">➕ تسجيل في دورة جديدة</a>
</div>

</body>
</html>
