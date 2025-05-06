<?php
session_start();

// تأكد من أن المستخدم مسجل دخول
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsperson') {
    header("Location: login.php");
    exit();
}

// اتصال قاعدة البيانات
$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$userId = $_SESSION['user']['UserId'];

// إضافة دورة للمفضلة
if (isset($_GET['add_fav'])) {
    $courseId = (int) $_GET['add_fav'];

    $check = $conn->prepare("SELECT * FROM new_favorites WHERE CraftspersonId = ? AND CourseId = ?");
    $check->bind_param("ii", $userId, $courseId);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO new_favorites (CraftspersonId, CourseId) VALUES (?, ?)");
        $insert->bind_param("ii", $userId, $courseId);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

// استعلام جلب الدورات واسم الحرفي مع استبعاد الدورات المسجلة مسبقاً
$query = "
SELECT 
    c.CourseId,
    c.Title AS CourseTitle,
    c.Description AS CourseDescription,
    c.Price,
    c.CraftsmanId,
    COALESCE(a.FullName, u.Name, 'بدون اسم') AS FullName
FROM new_courses c
LEFT JOIN new_artisans a ON a.ArtisanId = c.CraftsmanId
LEFT JOIN new_users u ON u.UserId = c.CraftsmanId
WHERE NOT EXISTS (
    SELECT 1 FROM new_enrollments e
    WHERE e.CourseId = c.CourseId AND e.CraftspersonId = ?
)
ORDER BY FullName ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$artisans = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['CraftsmanId'] ?? 0;
    if (!isset($artisans[$id])) {
        $artisans[$id] = [
            'FullName' => $row['FullName'],
            'Courses' => []
        ];
    }
    $artisans[$id]['Courses'][] = [
        'CourseId' => $row['CourseId'],
        'Title' => $row['CourseTitle'],
        'Description' => $row['CourseDescription'],
        'Price' => $row['Price']
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>قائمة الدورات</title>
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
    max-width: 1100px;
    margin: auto;
}
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #8B0000;
    font-weight: bold;
}
.search-bar {
    text-align: center;
    margin-bottom: 30px;
}
.search-bar input {
    width: 50%;
    padding: 12px;
    font-size: 18px;
    border: 1px solid #ccc;
    border-radius: 10px;
}
.artisan-card {
    background-color: #A9B387;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
.artisan-name {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}
.course-info {
    background-color: #fff;
    padding: 15px;
    margin-top: 10px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.08);
    position: relative;
}
.course-title {
    font-weight: bold;
    font-size: 20px;
    color: #5E6F52;
}
.course-price {
    color: #8B0000;
    font-weight: bold;
    margin-top: 5px;
}
.course-desc {
    font-size: 16px;
    color: #555;
    margin-top: 5px;
}
.add-fav-btn {
    display: inline-block;
    margin-top: 10px;
    background-color: #8B0000;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
}
.add-fav-btn:hover {
    background-color: #5E6F52;
}
.choose-btn {
    display: inline-block;
    margin-top: 10px;
    margin-right: 10px;
    background-color: #5E6F52;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 16px;
    font-weight: bold;
}
.choose-btn:hover {
    background-color: #4b5943;
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

function searchArtisans() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.artisan-card');

    cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(input) ? 'block' : 'none';
    });
}
</script>
</head>

<body>

<img src="http://localhost/crafts-platform/uploads/sanna.png" alt="شعار صنعة" class="logo">

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
    <h2>قائمة الدورات</h2>

    <div class="search-bar">
        <input type="text" id="searchInput" oninput="searchArtisans()" placeholder="ابحث عن حرفة أو دورة...">
    </div>

    <?php foreach ($artisans as $artisan): ?>
        <div class="artisan-card">
            <div class="artisan-name"><?= htmlspecialchars($artisan['FullName']) ?></div>

            <?php foreach ($artisan['Courses'] as $course): ?>
                <div class="course-info">
                    <div class="course-title"><?= htmlspecialchars($course['Title']) ?></div>
                    <div class="course-desc"><?= htmlspecialchars($course['Description']) ?></div>
                    <div class="course-price"><?= htmlspecialchars($course['Price']) ?> ﷼</div>

                    <div style="margin-top: 10px;">
                        <a href="?add_fav=<?= urlencode($course['CourseId']) ?>" class="add-fav-btn" title="أضف إلى المفضلة">❤️</a>
                        <a href="enroll_course.php?course_id=<?= urlencode($course['CourseId']) ?>" class="choose-btn">اختر</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

</div>

</body>
</html>
