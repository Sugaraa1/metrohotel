<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

if(isset($_POST['get_rooms'])) {
    $data = filteration($_POST);

    
    
    // Base query
    $where_conditions = ["r.status = 1", "r.removed = 0"];
    $params = [];
    $types = "";
    
    // Filter by adults and children
    if(isset($data['adults']) && $data['adults'] != '') {
        $where_conditions[] = "r.adult >= ?";
        $params[] = $data['adults'];
        $types .= "i";
    }
    
    if(isset($data['children']) && $data['children'] != '') {
        $where_conditions[] = "r.children >= ?";
        $params[] = $data['children'];
        $types .= "i";
    }
    
    // Filter by price
    if(isset($data['min_price']) && $data['min_price'] != '') {
        $where_conditions[] = "r.price >= ?";
        $params[] = $data['min_price'];
        $types .= "i";
    }
    
    if(isset($data['max_price']) && $data['max_price'] != '') {
        $where_conditions[] = "r.price <= ?";
        $params[] = $data['max_price'];
        $types .= "i";
    }
    
    // Build query
    $query = "SELECT DISTINCT r.* FROM `rooms` r";
    
    // Filter by facilities
    if(isset($data['facilities']) && $data['facilities'] != '') {
        $facilities = explode(',', $data['facilities']);
        $facility_placeholders = implode(',', array_fill(0, count($facilities), '?'));
        
        $query .= " INNER JOIN `room_facilities` rf ON r.id = rf.room_id
                    WHERE rf.facilities_id IN ($facility_placeholders) AND ";
        
        foreach($facilities as $fac) {
            $params[] = $fac;
            $types .= "i";
        }
        
        $query .= implode(' AND ', $where_conditions);
        
        // Count facilities per room to ensure all selected facilities are present
        $query .= " GROUP BY r.id HAVING COUNT(DISTINCT rf.facilities_id) = " . count($facilities);
    } else {
        if(count($where_conditions) > 0) {
            $query .= " WHERE " . implode(' AND ', $where_conditions);
        }
    }
    
    // Filter by availability dates
    if(isset($data['check_in']) && $data['check_in'] != '' && isset($data['check_out']) && $data['check_out'] != '') {
        $check_in = $data['check_in'];
        $check_out = $data['check_out'];
        
        // Subquery to check if room is available
        $query .= " AND r.id NOT IN (
            SELECT room_id FROM `bookings` 
            WHERE booking_status != 'cancelled' 
            AND (
                (check_in <= ? AND check_out > ?) OR
                (check_in < ? AND check_out >= ?) OR
                (check_in >= ? AND check_out <= ?)
            )
        )";
        
        $params[] = $check_in;
        $params[] = $check_in;
        $params[] = $check_out;
        $params[] = $check_out;
        $params[] = $check_in;
        $params[] = $check_out;
        $types .= "ssssss";
    }
    
    $query .= " ORDER BY r.id DESC";
    
    // Execute query
    if(count($params) > 0) {
        $room_res = select($query, $params, $types);
    } else {
        $room_res = mysqli_query($con, $query);
    }
    
    $output = "";
    
    if(mysqli_num_rows($room_res) > 0) {
        while($room_data = mysqli_fetch_assoc($room_res)) {
            // Get features
            $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f 
                INNER JOIN `room_features` rfea ON f.id = rfea.features_id 
                WHERE rfea.room_id = '$room_data[id]'");
            
            $features_data = "";
            while($fea_row = mysqli_fetch_assoc($fea_q)){
                $features_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                        $fea_row[name]
                    </span>";
            }
            
            // Get facilities
            $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
                INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                WHERE rfac.room_id = '$room_data[id]'");
            
            $facilities_data = "";
            while($fac_row = mysqli_fetch_assoc($fac_q)){
                $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                        $fac_row[name]
                    </span>";
            }
            
            // Get thumbnail
            $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
            $thumb_q = mysqli_query($con,"SELECT * FROM `room_image` 
            WHERE `room_id`='$room_data[id]' AND `thumb`='1'");
            
            if(mysqli_num_rows($thumb_q)>0) {
                $thumb_res = mysqli_fetch_assoc($thumb_q);
                $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
            }
            
            // Get average rating
            $rating_q = mysqli_query($con,"SELECT AVG(rating) as avg_rating, COUNT(*) as total 
                FROM `room_reviews` 
                WHERE `room_id`='$room_data[id]' AND `status`=1");
            $rating_data = mysqli_fetch_assoc($rating_q);
            $avg_rating = round($rating_data['avg_rating'], 1);
            $total_reviews = $rating_data['total'];
            
            $rating_html = '';
            if($total_reviews > 0) {
                for($i=1; $i<=5; $i++) {
                    if($i <= floor($avg_rating)) {
                        $rating_html .= '<i class="bi bi-star-fill text-warning"></i>';
                    } else if($i - 0.5 <= $avg_rating) {
                        $rating_html .= '<i class="bi bi-star-half text-warning"></i>';
                    } else {
                        $rating_html .= '<i class="bi bi-star text-warning"></i>';
                    }
                }
                $rating_html .= " <small>($avg_rating / $total_reviews үнэлгээ)</small>";
            } else {
                $rating_html = '<small class="text-muted">Үнэлгээ байхгүй</small>';
            }
            
            // Build card HTML
            $output .= "
            <div class='card mb-4 border-0 shadow'>
                <div class='row g-0 p-3 align-items-center'>
                    <div class='col-md-5 mb-lg-0 mb-md-0 mb-3'>
                        <img src='$room_thumb' class='img-fluid rounded'>
                    </div>
                    <div class='col-md-5 px-lg-3 px-md-3 px-0'>
                        <h5 class='mb-3'>$room_data[name]</h5>
                        <div class='features mb-3'>
                            <h6 class='mb-1'>Онцлог</h6>
                            $features_data
                        </div>
                        <div class='facilities mb-3'>
                            <h6 class='mb-1'>Үйлчилгээ</h6>
                            $facilities_data
                        </div>
                        <div class='guests'>
                            <h6 class='mb-1'>Зочид</h6>
                            <span class='badge rounded-pill bg-light text-dark text-wrap'>
                                $room_data[adult] Том хүн
                            </span>
                            <span class='badge rounded-pill bg-light text-dark text-wrap'>
                                $room_data[children] Хүүхэд
                            </span>
                        </div>
                        <div class='rating mt-3'>
                            <h6 class='mb-1'>Үнэлгээ</h6>
                            $rating_html
                        </div>
                    </div>
                    <div class='col-md-2 mt-lg-0 mt-md-0 mt-4 text-center'>
                        <h6 class='mb-4'>₮".number_format($room_data['price'])." / шөнө</h6>
                        <a href='#' onclick='bookRoom($room_data[id])' class='btn btn-sm w-100 text-white custom-bg shadow-none mb-2'>Захиалах</a>
                        <a href='room_details.php?id=$room_data[id]' class='btn btn-sm w-100 btn-outline-dark shadow-none'>Дэлгэрэнгүй</a>
                    </div>
                </div>
            </div>";
        }
    } else {
        $output = "
        <div class='card border-0 shadow-sm'>
            <div class='card-body text-center py-5'>
                <i class='bi bi-emoji-frown' style='font-size: 3rem; color: #999;'></i>
                <h4 class='mt-3'>Өрөө олдсонгүй</h4>
                <p class='text-muted'>Таны шүүлтүүрт тохирох өрөө байхгүй байна. Өөр нөхцөл сонгоно уу.</p>
                <button class='btn btn-primary' onclick='clearFilters()'>Шүүлтүүр цэвэрлэх</button>
            </div>
        </div>";
    }
    
    echo $output;
}
?>