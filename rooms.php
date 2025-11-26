<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Метро Зочид Буудал - ӨРӨӨНҮҮД</title>
    <?php require('inc/links.php'); ?>
    </head>
<body class="bg-light">

    <?php require('inc/header.php'); ?>
    
    <div class="my-5 px-4">
        <h2 class="fw-bold h-font text-center">МАНАЙ ӨРӨӨНҮҮД</h2>
        <div class="h-line bg-dark"></div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 ps-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
                    <div class="container-fluid flex-lg-column align-items-stretch">
                        <h4 class="mt-2">ШҮҮЛТҮҮР</h4>
                        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropdown" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="filterDropdown">
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="mb-3" style="font-size: 18px;">СУЛ ӨРӨӨ ШАЛГАХ</h5>
                                <label class="form-label">Ирэх огноо</label>
                            <input type="date" class="form-control shadow-none mb-3"> 
                             <label class="form-label">Явах огноо</label>
                            <input type="date" class="form-control shadow-none">
                            </div>
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="mb-3" style="font-size: 18px;">ҮЙЛЧИЛГЭЭ</h5>
                                <div class="mb-2">
                                <input type="checkbox" id="f1"class="form-check-input  shadow-none me-1">  
                                <label class="form-check-label" for="f1">Үйлчилгээ 1</label>                            
                                </div>
                                 <div class="mb-2">
                                <input type="checkbox" id="f2"class="form-check-input  shadow-none me-1">  
                                <label class="form-check-label" for="f2">Үйлчилгээ 2</label>                          
                                </div>
                                 <div class="mb-2">
                                <input type="checkbox" id="f3"class="form-check-input  shadow-none me-1">  
                                <label class="form-check-label" for="f3">Үйлчилгээ 3</label>                              
                                </div>
                            </div>
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="mb-3" style="font-size: 18px;">ЗОЧИД</h5>
                                <div class="d-flex">
                                 <div class="me-3">
                                    <label class="form-label">Том хүн</label>
                                    <input type="number" class="form-control shadow-none"> 
                                </div>
                                <div>
                                    <label class="form-label">Хүүхэд</label>
                                    <input type="number" class="form-control shadow-none"> 
                                </div>   
                                </div>                              
                            </div>
                            <div class="col-lg-2 mb-lg-3 mt-2">
                            <button type="submit" class="btn text-white shadow-none custom-bg w-100">Хайх</button>
                        </div>
                        </div>
                    </div>
                </nav>
            </div>

            <div class="col-lg-9 col-md-12 px-4">

            <?php 
                $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `removed`=?",[1,0],'ii');

                while($room_data = mysqli_fetch_assoc($room_res))
                {
                    //get features of room

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

                    //get facilities of room

                    $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
                        INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                        WHERE rfac.room_id = '$room_data[id]'");

                    $facilities_data = "";
                    while($fac_row = mysqli_fetch_assoc($fac_q)){
                        $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                                $fac_row[name]
                            </span>";

                    }

                    //get thumbnail of image

                    $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
                    $thumb_q = mysqli_query($con,"SELECT * FROM `room_image` 
                    WHERE `room_id`='$room_data[id]' 
                    AND `thumb`='1'");

                if(mysqli_num_rows($thumb_q)>0)
                {
                    $thumb_res = mysqli_fetch_assoc($thumb_q);
                    $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
                }
                //get average rating
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

                //print room card
                echo <<<data

                <div class="card mb-4 border-0 shadow">
                    <div class="row g-0 p-3 align-items-center">
                        <div class="col-md-5 mb-lg-0 mb-md-0 mb-3">
                        <img src="$room_thumb" class="img-fluid rounded">
                        </div>
                        <div class="col-md-5 px-lg-3 px-md-3 px-0">
                        <h5 class="mb-3">$room_data[name]</h5>
                        <div class="features mb-3">
                            <h6 class="mb-1">Онцлог</h6>
                            $features_data
                        </div>
                        <div class="facilities mb-3">
                            <h6 class="mb-1">Үйлчилгээ</h6>
                            $facilities_data
                        </div>
                        <div class="guests">
                            <h6 class="mb-1">Зочид</h6>
                            <span class="badge rounded-pill bg-light text-dark text-wrap">
                                $room_data[adult] Том хүн
                            </span>
                            <span class="badge rounded-pill bg-light text-dark text-wrap">
                                $room_data[children] Хүүхэд
                            </span>
                        </div>
                        <div class="rating mt-3">
                            <h6 class="mb-1">Үнэлгээ</h6>
                            $rating_html
                        </div>
                        </div>
                        <div class="col-md-2 mt-lg-0 mt-md-0 mt-4 text-center">
                            <h6 class="mb-4">₮$room_data[price] / шөнө</h6>
                            <a href="#" onclick="bookRoom($room_data[id])" class="btn btn-sm w-100 text-white custom-bg shadow-none mb-2">Захиалах</a>
                            <a href="room_details.php?id=$room_data[id]" class="btn btn-sm w-100 btn-outline-dark shadow-none">Дэлгэрэнгүй</a>
                        </div>
                    </div>
                </div>

                data;
            }
            ?>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <script>
        function bookRoom(room_id) {
            <?php 
            if(isset($_SESSION['login']) && $_SESSION['login'] == true) {
                echo "window.location.href = 'room_details.php?id=' + room_id;";
            } else {
                echo "alert('error', 'Эхлээд нэвтэрнэ үү!'); let loginModal = new bootstrap.Modal(document.getElementById('loginModal')); loginModal.show();";
            }
            ?>
        }
    </script>

</body>
</html>