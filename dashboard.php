<?php
include 'config.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // รหัสผู้ใช้ที่ล็อกอินอยู่

// ดึงข้อมูลโพสต์ทั้งหมดจากฐานข้อมูล พร้อมข้อมูลผู้ใช้งาน
$query = "SELECT posts.id, posts.content, posts.image, posts.created_at, users.username, posts.user_id 
          FROM posts 
          JOIN users ON posts.user_id = users.id
          ORDER BY posts.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

echo "<h1>Dashboard</h1>";
echo "<a href='create_post.php'>สร้างโพสต์ใหม่</a> | ";
echo "<a href='logout.php' style='color: red;'>Logout</a><br><br>";

// แสดงโพสต์ทั้งหมด
while ($row = $result->fetch_assoc()) {
    echo "<div style='border: 1px solid #ddd; margin-bottom: 10px; padding: 10px;'>";
    echo "<strong>ผู้ใช้: " . htmlspecialchars($row['username']) . "</strong><br>";
    echo "<p>" . htmlspecialchars($row['content']) . "</p>";

    // แสดงรูปภาพ (ถ้ามี)
    if ($row['image']) {
        echo "<img src='" . $row['image'] . "' style='max-width:200px;'><br><br>";
    }

    // ตรวจสอบว่าเป็นโพสต์ของผู้ใช้ที่ล็อกอินหรือไม่
    if ($row['user_id'] == $user_id) {
        echo "<a href='edit_post.php?id=" . $row['id'] . "'>แก้ไข</a> ";
        echo "<a href='delete_post.php?id=" . $row['id'] . "'>ลบ</a>";
    }

    echo "</div>";
}
?>
