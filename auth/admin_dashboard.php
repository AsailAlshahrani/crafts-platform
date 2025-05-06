<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] ?? '') !== 'Admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "new_crafts_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المدير</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #FCF9DE;
            font-family: 'Tajawal', sans-serif;
            padding: 80px 20px 100px;
            position: relative;
        }

        .container {
            background-color: #A9B387;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-top: 80px;
        }

        h2 {
            color: #8B0000;
            font-size: 38px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .nav-btns {
            text-align: center;
            margin-bottom: 30px;
        }

        .nav-btns a {
            margin: 0 15px;
            padding: 12px 28px;
            background-color: #5E6F52;
            color: white;
            border-radius: 12px;
            font-size: 18px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.15);
        }

        .nav-btns a:hover {
            background-color: #4b5943;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            border-radius: 15px;
            overflow: hidden;
        }

        .table th, .table td {
            text-align: center;
            padding: 14px;
        }

        .table th {
            background-color: #7C9473;
            color: white;
            font-weight: bold;
        }

        .table tr:nth-child(even) {
            background-color: #e5e0c5;
        }

        .table tr:nth-child(odd) {
            background-color: #f7f5df;
        }

        .btn-edit {
            background-color: #5E6F52;
            color: white;
            border-radius: 8px;
            padding: 6px 14px;
        }

        .btn-danger {
            background-color: #c0392b;
            color: white;
            border-radius: 8px;
            padding: 6px 14px;
        }

        .btn-light {
            border-radius: 8px;
            padding: 6px 14px;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #5E6F52;
            color: white;
            padding: 10px 25px;
            font-size: 16px;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            height: 100px;
        }

        .table-section {
            display: none;
            margin-top: 20px;
        }
    </style>
    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.table-section').forEach(sec => sec.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }
    </script>
</head>

<body>

<img src="http://localhost/crafts-platform/uploads/sanna.png" alt="شعار صنعة" class="logo">
<a href="login.php" class="logout-btn">تسجيل خروج</a>

<div class="container">
    <h2>لوحة تحكم المدير</h2>
    <div class="nav-btns">
        <a href="#" onclick="showSection('beneficiaries'); return false;">إدارة المستفيدين</a>
        <a href="#" onclick="showSection('artisans'); return false;">إدارة الحرفيين</a>
        <a href="#" onclick="showSection('courses'); return false;">إدارة الدورات</a>
    </div>

    <!-- ✅ المستفيدين -->
    <div id="beneficiaries" class="table-section">
        <h4>قائمة المستفيدين</h4>
        <table class="table">
            <tr><th>الاسم</th><th>البريد الإلكتروني</th><th>الإجراء</th></tr>
            <?php
            $query = "
                SELECT 
                    COALESCE(CONCAT(p.firstName, ' ', p.lastName), u.Name, 'بدون اسم') AS name,
                    COALESCE(p.email, u.Email) AS email,
                    u.UserId AS id
                FROM new_users u
                LEFT JOIN new_profiles p ON p.UserId = u.UserId
                WHERE u.Role = 'Craftsperson'
                  AND COALESCE(p.email, u.Email) IS NOT NULL
            ";
            $res = $conn->query($query);
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <a href='view_item.php?id={$row['id']}&type=profiles' class='btn btn-light'>عرض</a>
                        <a href='edit_item.php?id={$row['id']}&type=profiles' class='btn btn-edit'>تعديل</a>
                        <a href='delete_item.php?id={$row['id']}&type=profiles' class='btn btn-danger' onclick=\"return confirm('هل أنت متأكد من الحذف؟')\">حذف</a>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>

    <!-- ✅ الحرفيين -->
    <div id="artisans" class="table-section">
        <h4>قائمة الحرفيين</h4>
        <table class="table">
            <tr><th>الاسم</th><th>البريد الإلكتروني</th><th>الإجراء</th></tr>
            <?php
            $query = "SELECT ArtisanId AS id, FullName AS name, Email AS email FROM new_artisans WHERE Email IS NOT NULL";
            $res = $conn->query($query);
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <a href='view_item.php?id={$row['id']}&type=artisans' class='btn btn-light'>عرض</a>
                        <a href='edit_item.php?id={$row['id']}&type=artisans' class='btn btn-edit'>تعديل</a>
                        <a href='delete_item.php?id={$row['id']}&type=artisans' class='btn btn-danger' onclick=\"return confirm('هل أنت متأكد من الحذف؟')\">حذف</a>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>

    <!-- ✅ الدورات -->
    <div id="courses" class="table-section">
        <h4>قائمة الدورات</h4>
        <table class="table">
            <tr><th>اسم الدورة</th><th>السعر</th><th>الإجراء</th></tr>
            <?php
            $query = "SELECT * FROM new_courses";
            $res = $conn->query($query);
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['Title']}</td>
                    <td>{$row['Price']} ريال</td>
                    <td>
                        <a href='view_item.php?id={$row['CourseId']}&type=courses' class='btn btn-light'>عرض</a>
                        <a href='edit_item.php?id={$row['CourseId']}&type=courses' class='btn btn-edit'>تعديل</a>
                        <a href='delete_item.php?id={$row['CourseId']}&type=courses' class='btn btn-danger' onclick=\"return confirm('هل أنت متأكد من الحذف؟')\">حذف</a>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>
</div>
</body>
</html>
