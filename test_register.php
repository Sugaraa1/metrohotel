<?php
// test_register.php файлыг үндсэн фолдер дээр үүсгэж туршина

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

echo "=== Database холболт тест ===<br><br>";

// 1. Connection шалгах
if($con) {
    echo "✓ Database холбогдсон<br>";
} else {
    echo "✗ Database холбогдоогүй: " . mysqli_connect_error() . "<br>";
    exit;
}

// 2. user_cred хүснэгт байгаа эсэхийг шалгах
$check_table = mysqli_query($con, "SHOW TABLES LIKE 'user_cred'");
if(mysqli_num_rows($check_table) > 0) {
    echo "✓ user_cred хүснэгт байна<br>";
} else {
    echo "✗ user_cred хүснэгт байхгүй байна<br>";
    exit;
}

// 3. Хүснэгтийн бүтцийг харуулах
echo "<br>=== user_cred хүснэгтийн бүтэц ===<br>";
$structure = mysqli_query($con, "DESCRIBE user_cred");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while($row = mysqli_fetch_assoc($structure)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table><br>";

// 4. Тест өгөгдөл оруулж үзэх
echo "<br>=== Тест өгөгдөл оруулах ===<br>";

$test_email = "test" . time() . "@test.com";
$test_phone = "99999" . rand(100, 999);

$query = "INSERT INTO `user_cred`(`name`, `email`, `address`, `phonenum`, `pincode`, `dob`, `profile`, `password`, `is_verified`, `token`) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$values = [
    "Test User",
    $test_email,
    "Test Address",
    $test_phone,
    12345,
    "2000-01-01",
    "test.jpg",
    password_hash("test123", PASSWORD_BCRYPT),
    1,
    NULL
];

try {
    $stmt = mysqli_prepare($con, $query);
    if($stmt) {
        mysqli_stmt_bind_param($stmt, 'ssssssssii', ...$values);
        
        if(mysqli_stmt_execute($stmt)) {
            $insert_id = mysqli_insert_id($con);
            echo "✓ Амжилттай нэмэгдлээ! ID: $insert_id<br>";
            echo "Email: $test_email<br>";
            echo "Phone: $test_phone<br>";
            
            // Нэмэгдсэн өгөгдлийг устгах
            mysqli_query($con, "DELETE FROM `user_cred` WHERE `id`='$insert_id'");
            echo "<br>✓ Тест өгөгдөл устгагдлаа<br>";
        } else {
            echo "✗ Execute алдаа: " . mysqli_stmt_error($stmt) . "<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "✗ Prepare алдаа: " . mysqli_error($con) . "<br>";
    }
} catch (Exception $e) {
    echo "✗ Алдаа: " . $e->getMessage() . "<br>";
}

echo "<br>=== Тест дууслаа ===";
?>