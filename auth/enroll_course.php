<?php
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsperson') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

if (!isset($_GET['course_id'])) {
    header("Location: main.php");
    exit();
}

$courseId = (int) $_GET['course_id'];

$query = "
SELECT 
    c.Title, 
    c.Price, 
    c.Description, 
    COALESCE(a.FullName, u.Name, 'بدون اسم') AS FullName, 
    a.Region
FROM new_courses c
LEFT JOIN new_artisans a ON c.CraftsmanId = a.ArtisanId
LEFT JOIN new_users u ON c.CraftsmanId = u.UserId
WHERE c.CourseId = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: main.php");
    exit();
}

$course = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>معلومات الدورة - صنعة</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
body {
    background-color: #FCF9DE;
    font-family: 'Tajawal', sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
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
.sidebar-content {
    padding: 20px;
    flex-grow: 1;
}
.user-info {
    font-size: 22px;
    color: #fff;
    font-weight: bold;
    margin-bottom: 30px;
}
.sidebar a {
    padding: 12px;
    text-decoration: none;
    font-size: 20px;
    color: #333;
    display: block;
    font-weight: bold;
    margin-top: 10px;
}
.sidebar a:hover {
    background-color: #5E6F52;
    color: #fff;
    border-radius: 8px;
}
.logout-btn {
    background-color: #c0392b;
    padding: 12px;
    margin: 20px auto;
    width: 80%;
    border-radius: 10px;
    color: white;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    display: block;
}
.close-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 30px;
    background: none;
    border: none;
    color: white;
    cursor: pointer;
}
.close-btn:hover {
    color: #333;
}
.container {
    width: 1000px;
    max-width: 95%;
    margin: 30px auto;
    background-color: #A9B387;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    position: relative;
}
header {
    background-color: #7B8C6A;
    padding: 15px 30px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
}
.menu-btn {
    background: none;
    border: none;
    color: white;
    font-size: 32px;
    cursor: pointer;
}
main {
    padding: 40px;
    text-align: center;
}
main h1 {
    font-size: 36px;
    color: #4B5D41;
    margin-bottom: 30px;
    font-weight: bold;
}
.card {
    background-color: #f5f7f4;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.course-title {
    font-size: 38px;
    font-weight: bold;
    color: #2C3E50;
    margin-bottom: 20px;
}
.artisan-details {
    margin-bottom: 25px;
}
.artisan-name {
    font-size: 22px;
    color: #34495e;
    font-weight: 600;
}
.description {
    font-size: 20px;
    color: #555;
    margin-bottom: 25px;
    line-height: 1.7;
}
.price {
    font-size: 26px;
    color: #c0392b;
    font-weight: bold;
    margin-bottom: 25px;
}
.price span {
    font-size: 28px;
}
.buttons {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 20px;
}
.buttons .btn {
    padding: 14px 30px;
    font-size: 20px;
    border-radius: 8px;
    font-weight: bold;
}
.pay-btn {
    background-color: #5E6F52;
    color: white;
}
.pay-btn:hover {
    background-color: #4b5943;
}
.contact-btn {
    background-color: #5E6F52;
    color: white;
}
.contact-btn:hover {
    background-color: #4b5943;
}
.back-btn {
    display: block;
    margin: 30px auto 0;
    text-align: center;
    width: 250px;
    background-color: #c0392b;
    color: white;
    padding: 14px 0;
    border-radius: 8px;
    font-size: 20px;
    font-weight: bold;
    text-decoration: none;
}
.back-btn:hover {
    background-color: #922b21;
}
</style>

<script>
function toggleSidebar() {
    document.getElementById("mySidebar").classList.toggle("open");
}

document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            document.getElementById('mySidebar').classList.remove('open');
        });
    });
});
</script>

</head>

<body>

<div class="menu-icon" onclick="toggleSidebar()">
    <div></div>
    <div></div>
    <div></div>
</div>

<div id="mySidebar" class="sidebar">
    <button onclick="toggleSidebar()" class="close-btn">&times;</button>
    <div class="sidebar-content">
        <div class="user-info">
            <?= htmlspecialchars($_SESSION['user']['Name'] ?? '-') ?>
        </div>
        <a href="profile.php">ملفك الشخصي</a>
        <a href="my_courses.php">الدورات المسجلة</a>
        <a href="favorites.php">المفضلة</a>
    </div>
    <a href="login.php" class="logout-btn">تسجيل خروج</a>
</div>

<div class="container">
    <main>
        <h1>تفاصيل الدورة</h1>
        <div class="card">
            <div class="info">
                <h2 class="course-title"><?= htmlspecialchars($course['Title']) ?></h2>
                <div class="artisan-details">
                    <p class="artisan-name"><?= htmlspecialchars($course['FullName']) ?> - <?= htmlspecialchars($course['Region']) ?></p>
                </div>
                <p class="description"><?= htmlspecialchars($course['Description']) ?></p>
                <p class="price">السعر: <span><?= htmlspecialchars($course['Price']) ?> ﷼</span></p>
            </div>

            <div class="buttons">
                <a href="payment.php?course_id=<?= urlencode($courseId) ?>&price=<?= urlencode($course['Price']) ?>&title=<?= urlencode($course['Title']) ?>" class="btn pay-btn">ادفع الآن</a>
                <a href="https://wa.me/966555798407" target="_blank" class="btn pay-btn">تواصل</a>
            </div>

            <a href="main.php" class="back-btn">عودة لقائمة الدورات</a>
        </div>
    </main>
</div>

</body>
</html>
