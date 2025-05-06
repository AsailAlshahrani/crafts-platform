<?php
session_start();

// التحقق من جلسة تسجيل الدخول
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Craftsperson') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$userId = $_SESSION['user']['UserId'];

// جلب بيانات المستخدم
$profileData = null;
$stmt = $conn->prepare("SELECT firstName, lastName, email, region, interests FROM new_profiles WHERE UserId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $profileData = $result->fetch_assoc();
} else {
    $stmt = $conn->prepare("SELECT Name AS firstName, '' AS lastName, Email AS email, '' AS region, '' AS interests FROM new_users WHERE UserId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profileData = $result->fetch_assoc();
}
$stmt->close();

$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $region = trim($_POST['region']);
    $interests = trim($_POST['interests']);

    $stmt = $conn->prepare("UPDATE new_profiles SET firstName = ?, lastName = ?, email = ?, region = ?, interests = ? WHERE UserId = ?");
    $stmt->bind_param("sssssi", $firstName, $lastName, $email, $region, $interests, $userId);
    if ($stmt->execute()) {
        $successMessage = "✅ تم تحديث البيانات بنجاح.";
        $profileData = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'region' => $region,
            'interests' => $interests
        ];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>ملفك الشخصي - صنعة</title>
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
    max-width: 800px;
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
.form-control {
    margin-bottom: 15px;
    border-radius: 8px;
    padding: 12px;
    text-align: right;
}
.btn-custom {
    background-color: #5E6F52;
    color: white;
    border-radius: 12px;
    padding: 14px 50px;
    font-size: 20px;
    font-weight: bold;
    display: block;
    margin: 40px auto 0;
    transition: 0.3s;
    width: fit-content;
}
.btn-custom:hover {
    background-color: #4b5943;
}
.success-msg {
    text-align: center;
    color: green;
    font-weight: bold;
    margin-bottom: 20px;
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

<!-- الشعار -->
<img src=http://localhost/crafts-platform/uploads/sanna.png  alt="شعار صنعة" class="logo">

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
    <h2>ملفك الشخصي</h2>

    <?php if (!empty($successMessage)): ?>
        <div class="success-msg"><?= $successMessage ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="firstName" class="form-control" placeholder="الاسم" value="<?= htmlspecialchars($profileData['firstName']) ?>" required>
        <input type="text" name="lastName" class="form-control" placeholder="اسم العائلة" value="<?= htmlspecialchars($profileData['lastName']) ?>">
        <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" value="<?= htmlspecialchars($profileData['email']) ?>" required>
        <input type="text" name="region" class="form-control" placeholder="المنطقة" value="<?= htmlspecialchars($profileData['region']) ?>">
        <textarea name="interests" class="form-control" placeholder="الاهتمامات"><?= htmlspecialchars($profileData['interests']) ?></textarea>

        <button type="submit" class="btn-custom"> حفظ التعديلات</button>
        <a href="main.php" class="btn-custom" style="background-color: #8B0000; margin-top: 20px; text-decoration: none;"> العودة لقائمة الدورات</a>


    </form>
</div>

</body>
</html>
