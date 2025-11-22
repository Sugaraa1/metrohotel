<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

if(isset($_POST['search_rooms'])) {
    $data = filteration($_POST);
    
    $check_in = $data['check_in'];
    $check_out = $data['check_out'];
    $adults = $data['adults'];
    $children = $data['children'];
    
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
    
    // Боломжит өрөөнүүдийг хайх
    // 1. Идэвхтэй, устгагдаагүй өрөөнүүд
    // 2. Хүрэлцэхүйц хүн багтаамжтай
    // 3. Тухайн хугацаанд захиалгагүй
    
    $query = "SELECT r.*, 
              (SELECT COUNT(*) FROM `bookings` b 
               WHERE b.room_id = r.id 
               AND b.booking_status != 'cancelled' 
               AND (
                   (b.check_in <= ? AND b.check_out > ?) OR
                   (b.check_in < ? AND b.check_out >= ?) OR
                   (b.check_in >= ? AND b.check_out <= ?)
               )
              ) as booked_count
              FROM `rooms` r
              WHERE r.status = 1 
              AND r.removed = 0
              AND r.adult >= ?
              AND r.children >= ?
              HAVING booked_count < r.quantity
              ORDER BY r.price ASC";
    
    $result = select($query, [
        $check_in, $check_in, 
        $check_out, $check_out, 
        $check_in, $check_out,
        $adults, $children
    ], 'sssssiii');
    
    $rooms = [];
    while($row = mysqli_fetch_assoc($result)) {
        // Get room thumbnail
        $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
        $thumb_q = mysqli_query($con, "SELECT * FROM `room_image` 
                    WHERE `room_id`='$row[id]' AND `thumb`='1'");
        
        if(mysqli_num_rows($thumb_q) > 0) {
            $thumb_res = mysqli_fetch_assoc($thumb_q);
            $row['thumbnail'] = ROOMS_IMG_PATH.$thumb_res['image'];
        } else {
            $row['thumbnail'] = $room_thumb;
        }
        
        // Get features
        $fea_q = mysqli_query($con, "SELECT f.name FROM `features` f 
                    INNER JOIN `room_features` rfea ON f.id = rfea.features_id 
                    WHERE rfea.room_id = '$row[id]'");
        
        $features = [];
        while($fea_row = mysqli_fetch_assoc($fea_q)) {
            $features[] = $fea_row['name'];
        }
        $row['features'] = $features;
        
        // Get facilities
        $fac_q = mysqli_query($con, "SELECT f.name FROM `facilities` f 
                    INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                    WHERE rfac.room_id = '$row[id]'");
        
        $facilities = [];
        while($fac_row = mysqli_fetch_assoc($fac_q)) {
            $facilities[] = $fac_row['name'];
        }
        $row['facilities'] = $facilities;
        
        // Calculate total price
        $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
        $row['total_price'] = $row['price'] * $days;
        $row['nights'] = $days;
        
        $rooms[] = $row;
    }
    
    if(count($rooms) > 0) {
        echo json_encode([
            'status' => 'success', 
            'rooms' => $rooms,
            'message' => count($rooms) . ' өрөө олдлоо!'
        ]);
    } else {
        echo json_encode([
            'status' => 'not_found', 
            'message' => 'Уучлаарай, таны сонгосон хугацаанд сул өрөө олдсонгүй.'
        ]);
    }
    exit;
}
?>