<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

session_start();

// Check availability
if(isset($_POST['check_availability'])) {
    $data = filteration($_POST);
    
    $check_in = $data['check_in'];
    $check_out = $data['check_out'];
    $room_id = $data['room_id'];
    
    // Огноо шалгах
    if($check_in == '' || $check_out == '') {
        echo json_encode(['status' => 'error', 'message' => 'Огноогоо оруулна уу!']);
        exit;
    }
    
    $today = date('Y-m-d');
    if($check_in < $today) {
        echo json_encode(['status' => 'error', 'message' => 'Check-in огноо өнөөдрөөс хойш байх ёстой!']);
        exit;
    }
    
    if($check_out <= $check_in) {
        echo json_encode(['status' => 'error', 'message' => 'Check-out огноо check-in огнооноос хойш байх ёстой!']);
        exit;
    }
    
    // Өрөө сул эсэхийг шалгах
    $check_query = "SELECT * FROM `bookings` 
                    WHERE `room_id` = ? 
                    AND `booking_status` != 'cancelled' 
                    AND (
                        (`check_in` <= ? AND `check_out` > ?) OR
                        (`check_in` < ? AND `check_out` >= ?) OR
                        (`check_in` >= ? AND `check_out` <= ?)
                    )";
    
    $check_result = select($check_query, [$room_id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out], 'issssss');
    
    if(mysqli_num_rows($check_result) > 0) {
        echo json_encode(['status' => 'unavailable', 'message' => 'Энэ өрөө тухайн хугацаанд захиалагдсан байна!']);
    } else {
        // Үнийг тооцоолох
        $room_query = "SELECT `price` FROM `rooms` WHERE `id` = ?";
        $room_result = select($room_query, [$room_id], 'i');
        $room_data = mysqli_fetch_assoc($room_result);
        
        $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
        $total_price = $room_data['price'] * $days;
        
        echo json_encode([
            'status' => 'available', 
            'message' => 'Өрөө сул байна!',
            'total_price' => $total_price,
            'days' => $days
        ]);
    }
    exit;
}

// Book room
if(isset($_POST['book_room'])) {
    if(!isset($_SESSION['login']) || $_SESSION['login'] != true) {
        echo json_encode(['status' => 'error', 'message' => 'Эхлээд нэвтэрнэ үү!']);
        exit;
    }
    
    $data = filteration($_POST);
    
    $user_id = $_SESSION['uId'];
    $room_id = $data['room_id'];
    $check_in = $data['check_in'];
    $check_out = $data['check_out'];
    $adults = $data['adults'];
    $children = $data['children'];
    $arrival_time = $data['arrival_time'];
    $special_requests = $data['special_requests'];
    
    // Үнийг тооцоолох
    $room_query = "SELECT `price` FROM `rooms` WHERE `id` = ?";
    $room_result = select($room_query, [$room_id], 'i');
    $room_data = mysqli_fetch_assoc($room_result);
    
    $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
    $total_price = $room_data['price'] * $days;
    
    // Order ID үүсгэх
    $order_id = 'ORD_' . $user_id . '_' . $room_id . '_' . time();
    
    // Booking хийх
    $query = "INSERT INTO `bookings`(`user_id`, `room_id`, `check_in`, `check_out`, `arrival_time`, `total_price`, `adults`, `children`, `special_requests`, `order_id`) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $values = [$user_id, $room_id, $check_in, $check_out, $arrival_time, $total_price, $adults, $children, $special_requests, $order_id];
    
    $result = insert($query, $values, 'iisssiiiss');
    
    if($result) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Захиалга амжилттай илгээгдлээ!',
            'order_id' => $order_id,
            'booking_id' => mysqli_insert_id($GLOBALS['con'])
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Захиалга илгээхэд алдаа гарлаа!']);
    }
    exit;
}

// Get user bookings
if(isset($_POST['get_bookings'])) {
    if(!isset($_SESSION['login']) || $_SESSION['login'] != true) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $_SESSION['uId'];
    
    $query = "SELECT b.*, r.name as room_name, r.price 
              FROM `bookings` b
              INNER JOIN `rooms` r ON b.room_id = r.id
              WHERE b.user_id = ?
              ORDER BY b.booking_date DESC";
    
    $result = select($query, [$user_id], 'i');
    
    $bookings = [];
    while($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'bookings' => $bookings]);
    exit;
}

// Cancel booking
if(isset($_POST['cancel_booking'])) {
    if(!isset($_SESSION['login']) || $_SESSION['login'] != true) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    $data = filteration($_POST);
    $booking_id = $data['booking_id'];
    $user_id = $_SESSION['uId'];
    
    // Өөрийн booking эсэхийг шалгах
    $check_query = "SELECT * FROM `bookings` WHERE `booking_id` = ? AND `user_id` = ?";
    $check_result = select($check_query, [$booking_id, $user_id], 'ii');
    
    if(mysqli_num_rows($check_result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid booking']);
        exit;
    }
    
    $query = "UPDATE `bookings` SET `booking_status` = 'cancelled' WHERE `booking_id` = ?";
    $result = update($query, [$booking_id], 'i');
    
    if($result) {
        echo json_encode(['status' => 'success', 'message' => 'Захиалга цуцлагдлаа!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Алдаа гарлаа!']);
    }
    exit;
}
?>