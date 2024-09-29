<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    // ตรวจสอบการอัปโหลดรูปภาพ
    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/"; // กำหนดโฟลเดอร์สำหรับเก็บรูปภาพ
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // ตรวจสอบประเภทไฟล์ (อนุญาตเฉพาะ jpg, png, jpeg, gif)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            // ย้ายไฟล์ที่อัปโหลดไปยังโฟลเดอร์ "uploads"
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $target_file; // บันทึกเส้นทางไฟล์
            } else {
                echo "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ!";
            }
        } else {
            echo "อนุญาตเฉพาะไฟล์รูปภาพประเภท JPG, JPEG, PNG, และ GIF เท่านั้น!";
        }
    }

    // บันทึกโพสต์ลงในฐานข้อมูล (รวมเส้นทางรูปภาพด้วย)
    $query = "INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $user_id, $content, $image);
    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "มีข้อผิดพลาดในการโพสต์!";
    }
}
?>

<!-- HTML ฟอร์มสร้างโพสต์ใหม่ -->
<form method="POST" action="create_post.php" enctype="multipart/form-data">
    <textarea name="content" placeholder="เนื้อหาโพสต์..." required></textarea><br>
    <label for="image">เลือกรูปภาพ:</label>
    <input type="file" name="image" id="image" accept="image/*"><br><br>
    <button type="submit">โพสต์</button>
</form>
