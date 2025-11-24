<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metro Hotel - HOME</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper@12/swiper-bundle.min.css">

    <?php require('inc/links.php'); ?>

    <link rel="stylesheet"  href="https://cdn.jsdelivr.net/npm/swiper@7/swiper-bundle.min.css"/>
    <style>
        .availability-form{
            margin-top: -50px;
            z-index: 11;
            position: relative;
        }

        @media screen and (max-width: 575px) {
        .availability-form{
            margin-top: 0px;
            padding: 0 35px;
            }
        }   
    </style>
</head>
<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <!-- Carousel -->

    <div class="container-fluid px-lg-4 mt-4">
        <div class="swiper swiper-container">
            <div class="swiper-wrapper">
                <?php
                $res = selectAll('carousel');
                while($row = mysqli_fetch_assoc($res))
                    {
                        $path = CAROUSEL_IMG_PATH;
                        echo <<<data
                        <div class="swiper-slide">
                            <img src="$path$row[image]" class="w-100 d-block">
                        </div>
                        data;
                    }
                ?>
            </div>
        </div>
    </div>

    <!-- CHECK AVAILABLE FORM -->

    <div class="container availability-form">
        <div class="row">
            <div class="col-lg-12 bg-white shadow p-4 rounded">
                <h5 class="mb-4">Өрөө хайх</h5>
                <form id="search_form">
                    <div class="row align-items-end">
                        <div class="col-lg-3 mb-3">
                            <label class="form-label" style="font-weight: 500;">Ирэх огноо</label>
                            <input type="date" name="check_in" class="form-control shadow-none" required>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label" style="font-weight: 500;">Явах огноо</label>
                            <input type="date" name="check_out" class="form-control shadow-none" required>
                        </div>
                        <div class="col-lg-2 mb-3">
                            <label class="form-label" style="font-weight: 500;">Том хүн</label>
                            <select name="adults" class="form-select shadow-none">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <div class="col-lg-2 mb-3">
                            <label class="form-label" style="font-weight: 500;">Хүүхэд</label>
                            <select name="children" class="form-select shadow-none">
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                        <div class="col-lg-2 mb-lg-3 mt-2">
                            <button type="submit" class="btn text-white shadow-none custom-bg w-100">Хайх</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>   
    </div>

    <!-- Search Results -->
    <div id="search_results" class="container mt-4" style="display: none;">
        <h4 class="mb-4">Хайлтын үр дүн</h4>
        <div class="row" id="search_results_content">
        </div>
    </div>

    <!-- Our Rooms -->

    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">МАНАЙ ӨРӨӨНҮҮД</h2>

    <div class="container" id="default_rooms">
        <div class="row">

         <?php 
                $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `removed`=? ORDER BY `id` DESC LIMIT 3",[1,0],'ii');

                while($room_data = mysqli_fetch_assoc($room_res))
                {
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

                    $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
                        INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                        WHERE rfac.room_id = '$room_data[id]'");

                    $facilities_data = "";
                    while($fac_row = mysqli_fetch_assoc($fac_q)){
                        $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                                $fac_row[name]
                            </span>";
                    }

                    $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
                    $thumb_q = mysqli_query($con,"SELECT * FROM `room_image` 
                    WHERE `room_id`='$room_data[id]' 
                    AND `thumb`='1'");

                if(mysqli_num_rows($thumb_q)>0)
                {
                    $thumb_res = mysqli_fetch_assoc($thumb_q);
                    $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
                }

                echo <<<data
                <div class="col-lg-4 col-md-6 my-3">
                <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                    <img src="$room_thumb" class="card-img-top">
                    <div class="card-body">
                        <h5>$room_data[name]</h5>
                        <h6 class="mb-4">₮$room_data[price] / шөнө</h6>
                        <div class="features mb-4">
                            <h6 class="mb-1">Онцлог</h6>
                            $features_data
                        </div>
                        <div class="facilities mb-4">
                            <h6 class="mb-1">Үйлчилгээ</h6>
                            $facilities_data
                        </div>
                        <div class="guests mb-4">
                            <h6 class="mb-1">Зочид</h6>
                            <span class="badge rounded-pill bg-light text-dark text-wrap">
                            $room_data[adult] Том хүн
                            </span>
                            <span class="badge rounded-pill bg-light text-dark text-wrap">
                            $room_data[children] Хүүхэд
                            </span>
                        </div>
                        <div class="rating mb-4">
                            <h6 class="mb-1">Үнэлгээ</h6>
                            <span class="badge rounded-pill bg-light">    
                            <i class="bi bi-star-fill text-warning"></i>  
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            </span>
                        </div>
                        <div class="d-flex justify-content-evenly mb-2">
                            <a href="#" onclick="bookRoom($room_data[id])" class="btn btn-sm text-white custom-bg shadow-none">Захиалах</a>
                            <a href="room_details.php?id=$room_data[id]" class="btn btn-sm  btn-outline-dark shadow-none">Дэлгэрэнгүй</a>
                        </div>                      
                    </div>
                  </div>                 
                </div>

                data;
            }
            ?>  
            <div class="col-lg-12 text-center mt-5">
                <a href="rooms.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Бүх өрөө үзэх >>></a>

            </div>
        </div>
    </div>

    <!-- Our Facilities -->

    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">МАНАЙ ҮЙЛЧИЛГЭЭ</h2>

    <div class="container">
        <div class="row justify-content-evenly px-lg-0 px-md-0 px-5">
            <?php
            $res = mysqli_query($con, "SELECT* FROM `facilities` ORDER BY `id` DESC LIMIT 5");
            $path = FACILITIES_IMG_PATH;

            while($row = mysqli_fetch_assoc($res)){
                echo<<<data
                    <div class="col-lg-2 col-md-2 text-center bg-white rounded shadow py-4 my-3">
                    <img src="$path$row[icon]" width="60px">
                    <h5 class="mt-3">$row[name]</h5>
                    </div>
                data;
            }
            ?>
            <div class="col-lg-12 text-center mt-5">
                <a href="facilities.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Бүх үйлчилгээ үзэх >>></a>
            </div>
        </div>
    </div>

    <!-- Testimonials -->

    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">ҮНЭЛГЭЭ СЭТГЭГДЭЛ</h2>

    <div class="container mt-5">
    <div class="swiper swiper-testimonial">
        <div class="swiper-wrapper mb-5">
        <div class="swiper-slide bg-white p-4">
            <div class="profile d-flex align-items-center mb-3">
                <img src="images/features/user ext.png" width="30px">
                <h6 class="mb-0 ms-2">Random user 1</h6>
            </div>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti magnam facere ad ex maiores quibusdam id iste qui, sed quidem eveniet, ut cumque nemo! Animi reprehenderit incidunt sint reiciendis magnam?
            </p>
            <div class="rating">   
                        <i class="bi bi-star-fill text-warning"></i>  
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                </div>
            </div>
            <div class="swiper-slide bg-white p-4">
            <div class="profile d-flex align-items-center mb-3">
                <img src="images/features/user ext.png" width="30px">
                <h6 class="mb-0 ms-2">Random user 2</h6>
            </div>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti magnam facere ad ex maiores quibusdam id iste qui, sed quidem eveniet, ut cumque nemo! Animi reprehenderit incidunt sint reiciendis magnam?
            </p>
            <div class="rating">   
                        <i class="bi bi-star-fill text-warning"></i>  
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                </div>
            </div>
            <div class="swiper-slide bg-white p-4">
            <div class="profile d-flex align-items-center mb-3">
                <img src="images/features/user ext.png" width="30px">
                <h6 class="mb-0 ms-2">Random user 3</h6>
            </div>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti magnam facere ad ex maiores quibusdam id iste qui, sed quidem eveniet, ut cumque nemo! Animi reprehenderit incidunt sint reiciendis magnam?
            </p>
            <div class="rating">   
                        <i class="bi bi-star-fill text-warning"></i>  
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                </div>
            </div>
            <div class="swiper-slide bg-white p-4">
            <div class="profile d-flex align-items-center mb-3">
                <img src="images/features/user ext.png" width="30px">
                <h6 class="mb-0 ms-2">Random user 4</h6>
            </div>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti magnam facere ad ex maiores quibusdam id iste qui, sed quidem eveniet, ut cumque nemo! Animi reprehenderit incidunt sint reiciendis magnam?
            </p>
            <div class="rating">   
                        <i class="bi bi-star-fill text-warning"></i>  
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                </div>
            </div>
            <div class="swiper-slide bg-white p-4">
            <div class="profile d-flex align-items-center mb-3">
                <img src="images/features/user ext.png" width="30px">
                <h6 class="mb-0 ms-2">Random user 5</h6>
            </div>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti magnam facere ad ex maiores quibusdam id iste qui, sed quidem eveniet, ut cumque nemo! Animi reprehenderit incidunt sint reiciendis magnam?
            </p>
            <div class="rating">   
                        <i class="bi bi-star-fill text-warning"></i>  
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                </div>
            </div>

        </div>
        <div class="swiper-pagination"></div>
    </div>
        <div class="col-lg-12 text-center mt-5">
                <a href="about.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Дэлгэрэнгүй >>></a>
        </div>
    </div>

    <!-- Reach us -->

    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">ХОЛБОО БАРИХ</h2>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 p-4 mb-lg-0 mb-3 bg-white rounded">
                <iframe class="w-100 rounded" height="320px" src="<?php echo $contact_r['iframe'] ?>" loading="lazy"></iframe>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="bg-white p-4 rounded mb-4">
                    <h5>Утас</h5>
                    <i class="bi bi-telephone-fill"></i>
                    <a href="tel:+<?php echo $contact_r['pn1'] ?>" class="d-inline-block mb-2 text-decoration-none text-dark">+<?php echo $contact_r['pn1'] ?>
                    </a>
                    <br>
                    <?php
                        if($contact_r['pn2']!=''){
                            echo<<<data
                            <i class="bi bi-telephone-fill"></i>
                            <a href="tel: +$contact_r[pn2]" class="d-inline-block text-decoration-none text-dark">+$contact_r[pn2]
                            </a>
                            data;
                        }

                    ?>
                </div>
                <div class="bg-white p-4 rounded mb-4">
                    <h5>Биднийг дагаарай</h5>
                    <?php
                        if($contact_r['tw']!=''){
                            echo<<<data
                            <a href="$contact_r[tw]" class="d-inline-block mb-3">
                                <span class="badge bg-light text-dark fs-6 p-2">
                                    <i class="bi bi-twitter me-1"></i> Twitter
                                </span>
                            </a>
                            <br>
                            data;
                        }
                    ?>
                    <a href="<?php echo $contact_r['fb'] ?>" class="d-inline-block mb-3">
                        <span class="badge bg-light text-dark fs-6 p-2"><i class="bi bi-facebook me-1"></i>   Facebook
                        </span>
                    </a>
                    <br>
                    <a href="<?php echo $contact_r['insta'] ?>" class="d-inline-block mb-3">
                        <span class="badge bg-light text-dark fs-6 p-2"><i class="bi bi-instagram me-1"></i> Instagram
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>

<script>
    var swiper = new Swiper(".swiper-container", {
      spaceBetween: 30,
      effect: "fade",
      loop: true,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false,
      }
    });

    var swiperTestimonial = new Swiper(".swiper-testimonial", {
      effect: "coverflow",
      grabCursor: true,
      centeredSlides: true,
      slidesPerView: "auto",
      slidesPerView: "3",
      loop: true,
      coverflowEffect: {
        rotate: 50,
        stretch: 0,
        depth: 100,
        modifier: 1,
        slideShadows: false,
      },
      pagination: {
        el: ".swiper-pagination",
      },
      breakpoints: {
        320: {
            slidesPerView: 1,
        },
        640: {
            slidesPerView: 1,
        },
        768: {
            slidesPerView: 2,
        },
        1024: {
            slidesPerView: 3,
        },
      }
    });

    // Book room function
    function bookRoom(room_id) {
        <?php 
        if(isset($_SESSION['login']) && $_SESSION['login'] == true) {
            echo "window.location.href = 'room_details.php?id=' + room_id;";
        } else {
            echo "alert('error', 'Эхлээд нэвтэрнэ үү!'); let loginModal = new bootstrap.Modal(document.getElementById('loginModal')); loginModal.show();";
        }
        ?>
    }

    // Set min date to today
    let today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="check_in"]').setAttribute('min', today);
    document.querySelector('input[name="check_out"]').setAttribute('min', today);

    // Update check-out min date when check-in changes
    document.querySelector('input[name="check_in"]').addEventListener('change', function() {
        let checkIn = new Date(this.value);
        checkIn.setDate(checkIn.getDate() + 1);
        let minCheckOut = checkIn.toISOString().split('T')[0];
        document.querySelector('input[name="check_out"]').setAttribute('min', minCheckOut);
    });

    // Search form submit
    let search_form = document.getElementById('search_form');
    search_form.addEventListener('submit', function(e) {
        e.preventDefault();
        searchRooms();
    });

    function searchRooms() {
        let formData = new FormData(search_form);
        formData.append('search_rooms', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/search_rooms.php", true);

        xhr.onload = function() {
            let response = JSON.parse(this.responseText);
            
            if(response.status == 'success') {
                displaySearchResults(response.rooms);
                document.getElementById('search_results').style.display = 'block';
                document.getElementById('default_rooms').style.display = 'none';
                
                // Scroll to results
                document.getElementById('search_results').scrollIntoView({behavior: 'smooth'});
                
                alert('success', response.message);
            } else if(response.status == 'not_found') {
                document.getElementById('search_results').style.display = 'block';
                document.getElementById('search_results_content').innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-exclamation-circle" style="font-size: 4rem; color: #6c757d;"></i>
                        <h4 class="mt-3">${response.message}</h4>
                        <button class="btn btn-primary mt-3" onclick="resetSearch()">View All Rooms</button>
                    </div>
                `;
                document.getElementById('default_rooms').style.display = 'none';
                alert('error', response.message);
            } else {
                alert('error', response.message);
            }
        }

        xhr.send(formData);
    }

    function displaySearchResults(rooms) {
        let html = '';
        
        rooms.forEach(function(room) {
            let features = '';
            room.features.forEach(function(feature) {
                features += `<span class="badge rounded-pill bg-light text-dark text-wrap me-1 mb-1">${feature}</span>`;
            });
            
            let facilities = '';
            room.facilities.forEach(function(facility) {
                facilities += `<span class="badge rounded-pill bg-light text-dark text-wrap me-1 mb-1">${facility}</span>`;
            });
            
            html += `
                <div class="col-lg-4 col-md-6 my-3">
                    <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                        <img src="${room.thumbnail}" class="card-img-top">
                        <div class="card-body">
                            <h5>${room.name}</h5>
                            <h6 class="mb-2 text-success">₮${room.total_price} for ${room.nights} night(s)</h6>
                            <p class="mb-4 text-muted small">₮${room.price} per night</p>
                            <div class="features mb-4">
                                <h6 class="mb-1">Features</h6>
                                ${features}
                            </div>
                            <div class="facilities mb-4">
                                <h6 class="mb-1">Facilities</h6>
                                ${facilities}
                            </div>
                            <div class="guests mb-4">
                                <h6 class="mb-1">Guests</h6>
                                <span class="badge rounded-pill bg-light text-dark text-wrap">
                                    ${room.adult} Adults
                                </span>
                                <span class="badge rounded-pill bg-light text-dark text-wrap">
                                    ${room.children} Children
                                </span>
                            </div>
                            <div class="d-flex justify-content-evenly mb-2">
                                <a href="#" onclick="bookRoom(${room.id})" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                                <a href="room_details.php?id=${room.id}" class="btn btn-sm btn-outline-dark shadow-none">More details</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
            <div class="col-12 text-center mt-4">
                <button class="btn btn-outline-dark" onclick="resetSearch()">Clear Search</button>
            </div>
        `;
        
        document.getElementById('search_results_content').innerHTML = html;
    }

    function resetSearch() {
        document.getElementById('search_results').style.display = 'none';
        document.getElementById('default_rooms').style.display = 'block';
        search_form.reset();
        
        // Reset min dates
        let today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="check_in"]').setAttribute('min', today);
        document.querySelector('input[name="check_out"]').setAttribute('min', today);
    }
</script>

</body>
</html>