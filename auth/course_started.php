<?php
session_start();
require_once('config.php');

if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsperson') {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user']['UserId'])) {
    header("Location: main.php");
    exit();
}

$userId = $_SESSION['user']['UserId'];

$query = "
    SELECT nc.CourseId, nc.Title, nc.Description, nc.File
    FROM new_courses nc
    JOIN new_enrollments e ON e.CourseId = nc.CourseId
    WHERE e.CraftspersonId = ?
    ORDER BY e.EnrollmentDate DESC
    LIMIT 1
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("فشل تحضير الاستعلام: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: main.php");
    exit();
}

$courseData = $result->fetch_assoc();

$title = htmlspecialchars($courseData['Title']);
$description = htmlspecialchars($courseData['Description'] ?? 'لا يوجد وصف لهذه الدورة.');

$attachments = [];
if (!empty($courseData['File'])) {
    $files = explode(',', $courseData['File']);
    foreach ($files as $file) {
        $file = trim($file);
        if (!empty($file)) {
            $attachments[] = [
                'name' => $file,
                'url' => 'download.php?file=' . urlencode($file)
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>بدأت دورتك - صنعة</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    background-color: #FCF9DE;
    font-family: 'Tajawal', sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}
.menu-icon {position: fixed;top: 20px;right: 20px;cursor: pointer;z-index: 1001;}
.menu-icon div {width: 30px;height: 4px;background-color: #5E6F52;margin: 6px 0;border-radius: 2px;}
.sidebar {height: 100%;width: 0;position: fixed;top: 0;right: 0;background-color: #A9B387;overflow-x: hidden;transition: 0.5s;padding-top: 60px;z-index: 1000;text-align: center;display: flex;flex-direction: column;justify-content: space-between;}
.sidebar.open {width: 250px;}
.sidebar .user-info {margin-bottom: 40px;font-size: 22px;color: #fff;font-weight: bold;}
.sidebar a {padding: 12px;text-decoration: none;font-size: 20px;color: #333;display: block;font-weight: bold;margin-top: 10px;}
.sidebar a:hover {background-color: #5E6F52;color: #fff;border-radius: 8px;}
.logout-btn {background-color: #c0392b;padding: 10px;border-radius: 10px;color: white;font-weight: bold;width: 80%;margin: 20px auto;text-align: center;text-decoration: none;}
.main-content {padding: 20px;margin-top: 60px;display: flex;flex-direction: column;align-items: center;}
.header-message {font-size: 32px;font-weight: bold;color: #4B5943;margin-bottom: 30px;}
.course-details-container {background-color: #fff;padding: 30px;border-radius: 15px;box-shadow: 0 2px 10px rgba(0,0,0,0.1);margin-bottom: 30px;width: 80%;max-width: 700px;text-align: right;}
.course-details-container h2 {color: #2C3E50;font-size: 28px;font-weight: bold;margin-bottom: 20px;}
.course-details-container .details-item {margin-bottom: 10px;font-size: 18px;color: #555;}
.attachments-container {background-color: #f9f9f9;padding: 20px;border-radius: 10px;box-shadow: 0 1px 5px rgba(0,0,0,0.05);width: 80%;max-width: 700px;text-align: right;}
.attachments-container h3 {color: #2C3E50;font-size: 24px;font-weight: bold;margin-bottom: 15px;}
.attachments-container ul {list-style: none;padding: 0;}
.attachments-container li {margin-bottom: 10px;}
.attachments-container a.download-btn {
    display: inline-block;
    padding: 10px 15px;
    background-color: #5E6F52;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 16px;
    transition: background-color 0.3s ease;
}
.attachments-container a.download-btn:hover {
    background-color: #4b5943;
}
.back-btn {
    background-color: #c0392b;
    color: white;
    padding: 14px 30px;
    font-size: 18px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    margin-top: 25px;
}
.back-btn:hover {
    background-color: #922b21;
}
</style>
</head>

<body>

<div class="menu-icon" onclick="toggleSidebar()">
    <div></div><div></div><div></div>
</div>

<div id="mySidebar" class="sidebar">
    <div>
        <div class="user-info"><?= htmlspecialchars($_SESSION['user']['Name']) ?></div>
        <a href="profile.php">ملفك الشخصي</a>
        <a href="my_courses.php">الدورات المسجلة</a>
        <a href="favorites.php">المفضلة</a>
    </div>
    <div style="margin-top: auto;">
        <a href="login.php" class="logout-btn">تسجيل خروج</a>
    </div>
</div>

<div class="main-content">
    <div class="header-message">🎉 رحلتك التعليمية بدأت!</div>

    <div class="course-details-container">
        <h2><?= $title ?></h2>
        <div class="details-item"><strong>الوصف:</strong> <?= $description ?></div>
    </div>

    <div class="attachments-container">
        <h3>الملفات المرفقة</h3>
        <?php if (!empty($attachments)): ?>
            <ul>
                <?php foreach ($attachments as $attachment): ?>
                    <li>
                        <a class="download-btn" href="<?= htmlspecialchars($attachment['url']) ?>">
                            📎 <?= htmlspecialchars(basename($attachment['name'])) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: #888;">لا توجد ملفات مرفقة مع هذه الدورة.</p>
        <?php endif; ?>
    </div>

    <a href="main.php" class="back-btn">الرجوع لقائمة الدورات</a>
</div>

<script>
function toggleSidebar() {
    document.getElementById("mySidebar").classList.toggle("open");
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
