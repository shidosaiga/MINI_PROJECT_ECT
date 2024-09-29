<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $post_id = $_POST['id'];
    $content = $_POST['content'];

    // ตรวจสอบว่าโพสต์นี้เป็นของผู้ใช้ที่เข้าสู่ระบบหรือไม่
    $query = "SELECT * FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $current_image = $row['image']; // เก็บชื่อไฟล์รูปภาพเดิม

        // ตรวจสอบว่าอัปโหลดรูปภาพใหม่หรือไม่
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                // ลบรูปภาพเก่าออกก่อน (ถ้ามี)
                if (!empty($current_image) && file_exists($current_image)) {
                    unlink($current_image); // ลบไฟล์เดิม
                }

                // ย้ายไฟล์ใหม่ไปยังโฟลเดอร์ "uploads"
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $current_image = $target_file; // ตั้งค่าเส้นทางรูปภาพใหม่
                } else {
                    echo "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ!";
                }
            } else {
                echo "อนุญาตเฉพาะไฟล์รูปภาพประเภท JPG, JPEG, PNG, และ GIF เท่านั้น!";
            }
        }

        // อัปเดตโพสต์ในฐานข้อมูล
        $query = "UPDATE posts SET content = ?, image = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $content, $current_image, $post_id, $user_id);

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "แก้ไขโพสต์ล้มเหลว!";
        }
    } else {
        echo "ไม่มีสิทธิ์แก้ไขโพสต์นี้!";
    }
} elseif (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // ดึงข้อมูลโพสต์มาแสดงในฟอร์ม
    $query = "SELECT * FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        ?>

        <!-- HTML ฟอร์มแก้ไขโพสต์ -->
        <form method="POST" action="edit_post.php" enctype="multipart/form-data">
            <textarea name="content" required><?php echo htmlspecialchars($row['content']); ?></textarea><br>
            
            <!-- ฟอร์มเลือกไฟล์รูปภาพ -->
            <?php if (!empty($row['image'])): ?>
                <img src="<?php echo $row['image']; ?>" style="max-width:200px;"><br>
                <label>รูปภาพปัจจุบัน: <?php echo basename($row['image']); ?></label><br>
            <?php endif; ?>
            <label for="image">เลือกรูปภาพใหม่:</label>
            <input type="file" name="image" id="image" accept="image/*"><br><br>

            <input type="hidden" name="id" value="<?php echo $post_id; ?>">
            <button type="submit">บันทึกการแก้ไข</button>
        </form>
        
        <?php
    } else {
        echo "ไม่มีโพสต์ที่เลือก!";
    }
}
?>
