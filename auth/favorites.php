<?php
session_start();

// تحقق من تسجيل الدخول
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsperson') {
    header("Location: login.php");
    exit();
}

// اتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$userId = $_SESSION['user']['UserId'];

// إذا طلب حذف دورة من المفضلة
if (isset($_GET['remove_fav'])) {
    $courseId = (int) $_GET['remove_fav'];

    $delete = $conn->prepare("DELETE FROM new_favorites WHERE CraftspersonId = ? AND CourseId = ?");
    $delete->bind_param("ii", $userId, $courseId);
    $delete->execute();
    $delete->close();
}

// جلب الدورات المضافة للمفضلة
$query = "
SELECT c.CourseId, c.Title, c.Description
FROM new_favorites f
INNER JOIN new_courses c ON f.CourseId = c.CourseId
WHERE f.CraftspersonId = ?
ORDER BY f.CreatedDate DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>المفضلة - صنعة</title>
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
    margin-bottom: 20px;
    background-color: #c0392b;
    padding: 10px;
    border-radius: 10px;
    color: white;
    font-weight: bold;
    width: 80%;
    margin: 20px auto;
}
.container {
    max-width: 1000px;
    margin: auto;
    background-color: #A9B387;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #8B0000;
    font-weight: bold;
}
.favorite-card {
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
.view-btn {
    background-color: #5E6F52;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
    margin-left: 10px;
}
.view-btn:hover {
    background-color: #4b5943;
}
.remove-btn {
    background-color: #c0392b;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
}
.remove-btn:hover {
    background-color: #922b21;
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

<img src="http://localhost/crafts-platform/uploads/sanna.png" alt="شعار صنعة" class="logo">

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
        <a href="main.php">الدورات المسجلة</a>
        <a href="favorites.php">المفضلة</a>
    </div>
    <a href="login.php" class="logout-btn">تسجيل خروج</a>
</div>

<div class="container">
    <h2>المفضلة</h2>

    <?php if (empty($favorites)): ?>
        <p style="text-align: center; font-size: 20px; font-weight: bold;">لا توجد دورات مضافة للمفضلة بعد.</p>
    <?php else: ?>
        <?php foreach ($favorites as $fav): ?>
            <div class="favorite-card">
                <div class="course-title"><?= htmlspecialchars($fav['Title']) ?></div>
                <div class="course-desc"><?= htmlspecialchars($fav['Description']) ?></div>
                <a href="enroll_course.php?course_id=<?= $fav['CourseId'] ?>" class="view-btn">اختر</a>
                <a href="favorites.php?remove_fav=<?= $fav['CourseId'] ?>" class="remove-btn" onclick="return confirm('هل أنت متأكد من إزالة هذه الدورة من المفضلة؟');"> إزالة</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
