<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Метро Зочид Буудал - ӨРӨӨНИЙ ДЭЛГЭРЭНГҮЙ</title>
    <?php require('inc/links.php'); ?>
    </head>
<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <?php 
        if(!isset($_GET['id'])){
            redirect('rooms.php');
        }

        $data = filteration($_GET);

        $room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?",[$data['id'],1,0],'iii');

        if(mysqli_num_rows($room_res)==0){
            redirect('rooms.php');
        }

        $room_data = mysqli_fetch_assoc($room_res); 

    ?>
    
    <div class="container">
        <div class="row">

                <div class="col-12 my-5 mb-4 px-4">
                    <h2 class="fw-bold"><?php echo $room_data['name'] ?></h2>
                    <div style="font-size: 14px;">
                        <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                        <span class="text-secondary"> > </span>
                        <a href="rooms.php" class="text-secondary text-decoration-none">ROOMS</a>
                    </div>
                </div>

                <div class="col-lg-7 col-md-12 px-4">
                    <div id="roomCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php 
                                $room_img = ROOMS_IMG_PATH."thumbnail.jpg";
                                $img_q = mysqli_query($con,"SELECT * FROM `room_image` 
                                WHERE `room_id`='$room_data[id]'");

                                if(mysqli_num_rows($img_q)>0)
                                {
                                    $active_class = 'active';   

                                    while($img_res = mysqli_fetch_assoc($img_q))
                                    {
                                    echo"
                                    <div class='carousel-item $active_class'>
                                    <img src='".ROOMS_IMG_PATH.$img_res['image']."' class='d-block w-100 rounded'>
                                    </div>
                                    ";
                                    $active_class = '';
                                    }
                               }
                                else{
                                    echo"<div class='carousel-item active'>
                                    <img src='$room_img' class='d-block w-100'>
                                    </div>";
                                }


                            ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        </div>
                </div>

            <div class="col-lg-5 col-md-12 px-4">
                <div class="card mb-4 border-0 shadow-sm rounded-3">
                    <div class="card-body">
                    <?php 
                        
                        echo<<<price
                            <h4>₮$room_data[price] per night</h4>
                        price;

                        echo<<<rating
                            <div class="mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                        rating;

                        $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f 
                        INNER JOIN `room_features` rfea 
                         ON f.id = rfea.features_id 
                         WHERE rfea.room_id = '$room_data[id]'");
                        $features_data = "";
                            while($fea_row = mysqli_fetch_assoc($fea_q)){
                            $features_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                            $fea_row[name]
                            </span>";
                        }

                        echo<<<features
                        <div class="mb-3">
                            <h6 class="mb-1">Features</h6>
                            $features_data
                            </div>
                        features;

                        $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
                         INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                         WHERE rfac.room_id = '$room_data[id]'");

                        $facilities_data = "";
                        while($fac_row = mysqli_fetch_assoc($fac_q)){
                        $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                            $fac_row[name]
                            </span>";
                        }

                        echo<<<facilities
                        <div class="mb-3">
                            <h6 class="mb-1">Facilities</h6>
                            $facilities_data
                            </div>
                        facilities;

                        echo<<<guests
                        <div class="mb-3">
                             <h6 class="mb-1">Guests</h6>
                             <span class="badge rounded-pill bg-light text-dark text-wrap">
                                 $room_data[adult] Adults
                             </span>
                             <span class="badge rounded-pill bg-light text-dark text-wrap">
                                 $room_data[children] Children
                             </span>
                         </div>
                        guests;

                        echo<<<area
                        <div class="mb-3">
                            <h6 class="mb-1">Area</h6>
                            <span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                            $room_data[area] MK²
                            </div>
                        area;

                        echo<<<book
                        <button class="btn w-100 text-white custom-bg shadow-none mb-1" onclick="checkLogin($room_data[id])">Book Now</button>
                        book;

                    ?>
                    </div>         
                </div>                   
            </div>

            <div class="col-12 mt-4 px-4">
                <div class="mb-5">
                    <h5>Description</h5>
                    <p>
                        <?php echo $room_data['description'] ?>
                    </p>
                </div>

                
            <div>
                <h5 class="mb-3">Үнэлгээ & Сэтгэгдэл</h5>
                
                <?php
                    // Дундаж үнэлгээ тооцоолох
                    $rating_q = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                                FROM `room_reviews` 
                                WHERE `room_id` = ? AND `status` = 1";
                    $rating_res = select($rating_q, [$room_data['id']], 'i');
                    $rating_data = mysqli_fetch_assoc($rating_res);
                    
                    $avg_rating = round($rating_data['avg_rating'], 1);
                    $total_reviews = $rating_data['total_reviews'];
                    
                    if($total_reviews > 0) {
                        echo '<div class="mb-4 p-3 bg-light rounded">';
                        echo '<div class="d-flex align-items-center">';
                        echo '<h2 class="mb-0 me-3">' . $avg_rating . '</h2>';
                        echo '<div>';
                        
                        // Одны үнэлгээ харуулах
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= floor($avg_rating)) {
                                echo '<i class="bi bi-star-fill text-warning"></i>';
                            } else if($i - 0.5 <= $avg_rating) {
                                echo '<i class="bi bi-star-half text-warning"></i>';
                            } else {
                                echo '<i class="bi bi-star text-warning"></i>';
                            }
                        }
                        
                        echo '<p class="mb-0 mt-1">' . $total_reviews . ' үнэлгээ</p>';
                        echo '</div></div></div>';
                    } else {
                        echo '<p class="text-muted">Одоогоор үнэлгээ байхгүй байна.</p>';
                    }
                    
                    // Бүх сэтгэгдлүүдийг харуулах
                    $review_q = "SELECT r.*, u.name as user_name, u.profile 
                                FROM `room_reviews` r
                                INNER JOIN `user_cred` u ON r.user_id = u.id
                                WHERE r.room_id = ? AND r.status = 1
                                ORDER BY r.created_at DESC";
                    $review_res = select($review_q, [$room_data['id']], 'i');
                    
                    if(mysqli_num_rows($review_res) > 0) {
                        while($review = mysqli_fetch_assoc($review_res)) {
                            $profile_pic = USERS_IMG_PATH . $review['profile'];
                            $date = date('Y-m-d', strtotime($review['created_at']));
                            
                            echo '<div class="mb-4 pb-3 border-bottom">';
                            echo '<div class="d-flex align-items-center mb-2">';
                            echo '<img src="' . $profile_pic . '" width="40px" height="40px" class="rounded-circle me-2" style="object-fit: cover;">';
                            echo '<div>';
                            echo '<h6 class="mb-0">' . $review['user_name'] . '</h6>';
                            echo '<small class="text-muted">' . $date . '</small>';
                            echo '</div></div>';
                            
                            echo '<div class="mb-2">';
                            for($i = 0; $i < $review['rating']; $i++) {
                                echo '<i class="bi bi-star-fill text-warning"></i>';
                            }
                            for($i = $review['rating']; $i < 5; $i++) {
                                echo '<i class="bi bi-star text-warning"></i>';
                            }
                            echo '</div>';
                            
                            echo '<p class="mb-0">' . $review['review'] . '</p>';
                            echo '</div>';
                        }
                    }
                ?>
            </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Room - <?php echo $room_data['name'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="booking_form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-in</label>
                                <input type="date" name="check_in" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-out</label>
                                <input type="date" name="check_out" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Adults</label>
                                <select name="adults" class="form-select shadow-none" required>
                                    <?php 
                                        for($i=1; $i<=$room_data['adult']; $i++) {
                                            echo "<option value='$i'>$i</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Children</label>
                                <select name="children" class="form-select shadow-none" required>
                                    <?php 
                                        for($i=0; $i<=$room_data['children']; $i++) {
                                            echo "<option value='$i'>$i</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Arrival Time (optional)</label>
                                <input type="time" name="arrival_time" class="form-control shadow-none">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Special Requests (optional)</label>
                                <textarea name="special_requests" class="form-control shadow-none" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <div id="availability_result" class="alert" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="checkAvailability()">Check Availability</button>
                        <button type="submit" id="confirm_booking_btn" class="btn btn-success" style="display:none;">Confirm Booking</button>
                    </div>
                    <input type="hidden" name="room_id" value="<?php echo $room_data['id'] ?>">
                </form>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <script>
        // Login шалгах
        function checkLogin(room_id) {
            <?php 
            if(isset($_SESSION['login']) && $_SESSION['login'] == true) {
                echo "openBookingModal();";
            } else {
                echo "alert('error', 'Эхлээд нэвтэрнэ үү!');";
            }
            ?>
        }

        function openBookingModal() {
            let bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
            bookingModal.show();
            
            // Set min date to today
            let today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="check_in"]').setAttribute('min', today);
            document.querySelector('input[name="check_out"]').setAttribute('min', today);
        }

        // Check availability
        function checkAvailability() {
            let form = document.getElementById('booking_form');
            let formData = new FormData(form);
            formData.append('check_availability', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/booking_crud.php", true);

            xhr.onload = function() {
                let response = JSON.parse(this.responseText);
                let resultDiv = document.getElementById('availability_result');
                
                resultDiv.style.display = 'block';
                
                if(response.status == 'available') {
                    resultDiv.className = 'alert alert-success';
                    resultDiv.innerHTML = response.message + '<br><strong>Total Price: ₮' + response.total_price + ' (' + response.days + ' nights)</strong>';
                    document.getElementById('confirm_booking_btn').style.display = 'inline-block';
                } else if(response.status == 'unavailable') {
                    resultDiv.className = 'alert alert-warning';
                    resultDiv.innerHTML = response.message;
                    document.getElementById('confirm_booking_btn').style.display = 'none';
                } else {
                    resultDiv.className = 'alert alert-danger';
                    resultDiv.innerHTML = response.message;
                    document.getElementById('confirm_booking_btn').style.display = 'none';
                }
            }

            xhr.send(formData);
        }

        // Book room
        let booking_form = document.getElementById('booking_form');
        booking_form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            formData.append('book_room', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/booking_crud.php", true);

            xhr.onload = function() {
                let response = JSON.parse(this.responseText);
                
                if(response.status == 'success') {
                    alert('success', response.message);
                    
                    let modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                    modal.hide();
                    
                    booking_form.reset();
                    document.getElementById('availability_result').style.display = 'none';
                    document.getElementById('confirm_booking_btn').style.display = 'none';
                    
                    // Redirect to bookings page
                    setTimeout(function() {
                        window.location.href = 'bookings.php';
                    }, 1500);
                } else {
                    alert('error', response.message);
                }
            }

            xhr.send(formData);
        });

        // Update check-out min date when check-in changes
        document.querySelector('input[name="check_in"]').addEventListener('change', function() {
            let checkIn = new Date(this.value);
            checkIn.setDate(checkIn.getDate() + 1);
            let minCheckOut = checkIn.toISOString().split('T')[0];
            document.querySelector('input[name="check_out"]').setAttribute('min', minCheckOut);
        });
    </script>
</body>
</html>