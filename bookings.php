<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Метро Зочид Буудал - Миний Захиалгууд</title>
    <?php require('inc/links.php'); ?>
</head>
<body class="bg-light">

    <?php 
    require('inc/header.php'); 
    
    if(!isset($_SESSION['login']) || $_SESSION['login'] != true) {
        redirect('index.php');
    }
    ?>
    
    <div class="container my-5">
        <h2 class="fw-bold mb-4">Миний Захиалгууд</h2>
        
        <div class="row" id="bookings_data">
            <div class="col-12 text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Ачааллаж байна...</span>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalTitle">Үнэлгээ өгөх</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="review_form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Үнэлгээ</label>
                            <div class="rating-input">
                                <input type="radio" name="rating" value="5" id="star5" required>
                                <label for="star5"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="4" id="star4">
                                <label for="star4"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="3" id="star3">
                                <label for="star3"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="2" id="star2">
                                <label for="star2"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="1" id="star1">
                                <label for="star1"><i class="bi bi-star-fill"></i></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Таны сэтгэгдэл</label>
                            <textarea name="review" class="form-control shadow-none" rows="4" required></textarea>
                        </div>
                        <input type="hidden" name="booking_id">
                        <input type="hidden" name="room_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Болих</button>
                        <button type="submit" class="btn btn-primary">Илгээх</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            font-size: 2rem;
        }
        .rating-input input {
            display: none;
        }
        .rating-input label {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }
        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #ffc107;
        }
    </style>

    <script>
        function loadBookings() {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/booking_crud.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                let response = JSON.parse(this.responseText);
                let bookingsDiv = document.getElementById('bookings_data');
                
                if(response.status == 'success') {
                    if(response.bookings.length == 0) {
                        bookingsDiv.innerHTML = `
                            <div class="col-12 text-center">
                                <p>Танд захиалга байхгүй байна.</p>
                                <a href="rooms.php" class="btn btn-primary">Өрөө үзэх</a>
                            </div>
                        `;
                    } else {
                        let html = '';
                        response.bookings.forEach(function(booking) {
                            let statusBadge = '';
                            if(booking.booking_status == 'pending') {
                                statusBadge = '<span class="badge bg-warning">Хүлээгдэж байна</span>';
                            } else if(booking.booking_status == 'confirmed') {
                                statusBadge = '<span class="badge bg-success">Баталгаажсан</span>';
                            } else if(booking.booking_status == 'cancelled') {
                                statusBadge = '<span class="badge bg-danger">Цуцлагдсан</span>';
                            }
                            
                            let paymentBadge = '';
                            if(booking.payment_status == 'paid') {
                                paymentBadge = '<span class="badge bg-success">Төлсөн</span>';
                            } else {
                                paymentBadge = '<span class="badge bg-warning">Төлөөгүй</span>';
                            }
                            
                            let cancelBtn = '';
                            let reviewBtn = '';
                            
                            if(booking.booking_status != 'cancelled') {
                                cancelBtn = `<button class="btn btn-sm btn-danger" onclick="cancelBooking(${booking.booking_id})">Цуцлах</button>`;
                            }
                            
                            // Захиалга confirmed байх үед сэтгэгдэл үлдээх товч харуулах
                            if(booking.booking_status == 'confirmed' && booking.has_review == 0) {
                                reviewBtn = `<button class="btn btn-sm btn-primary mt-2" onclick="openReviewModal(${booking.booking_id}, ${booking.room_id}, '${booking.room_name}')">Үнэлгээ өгөх</button>`;
                            } else if(booking.has_review == 1) {
                                reviewBtn = `<span class="badge bg-success mt-2">Үнэлгээ өгсөн</span>`;
                            }
                            
                            html += `
                                <div class="col-md-12 mb-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h5>${booking.room_name}</h5>
                                                    <p class="mb-1"><strong>Захиалгын дугаар:</strong> ${booking.order_id}</p>
                                                    <p class="mb-1"><strong>Ирэх огноо:</strong> ${booking.check_in}</p>
                                                    <p class="mb-1"><strong>Явах огноо:</strong> ${booking.check_out}</p>
                                                    <p class="mb-1"><strong>Зочид:</strong> ${booking.adults} Том хүн, ${booking.children} Хүүхэд</p>
                                                    <p class="mb-1"><strong>Нийт үнэ:</strong> ₮${booking.total_price}</p>
                                                    <p class="mb-1"><strong>Захиалсан огноо:</strong> ${booking.booking_date}</p>
                                                    ${booking.special_requests ? '<p class="mb-1"><strong>Тусгай хүсэлт:</strong> ' + booking.special_requests + '</p>' : ''}
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <div class="mb-2">
                                                        ${statusBadge}
                                                        ${paymentBadge}
                                                    </div>
                                                    ${cancelBtn}
                                                    ${reviewBtn}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        bookingsDiv.innerHTML = html;
                    }
                } else {
                    bookingsDiv.innerHTML = '<div class="col-12 text-center"><p>Алдаа гарлаа!</p></div>';
                }
            }

            xhr.send('get_bookings=1');
        }

        function cancelBooking(booking_id) {
            if(!confirm('Та энэ захиалгыг цуцлахдаа итгэлтэй байна уу?')) {
                return;
            }
            
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/booking_crud.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                let response = JSON.parse(this.responseText);
                
                if(response.status == 'success') {
                    alert('success', response.message);
                    loadBookings();
                } else {
                    alert('error', response.message);
                }
            }

            xhr.send('cancel_booking=1&booking_id=' + booking_id);
        }

        window.onload = function() {
            loadBookings();
        }

        function openReviewModal(booking_id, room_id, room_name) {
            document.getElementById('reviewModalTitle').innerText = 'Үнэлгээ өгөх - ' + room_name;
            document.getElementById('review_form').elements['booking_id'].value = booking_id;
            document.getElementById('review_form').elements['room_id'].value = room_id;
            
            let reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
            reviewModal.show();
        }

        // Review form submit
        let review_form = document.getElementById('review_form');
        review_form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            formData.append('add_review', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/booking_crud.php", true);

            xhr.onload = function() {
                let response = JSON.parse(this.responseText);
                
                if(response.status == 'success') {
                    alert('success', response.message);
                    
                    let modal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
                    modal.hide();
                    
                    review_form.reset();
                    loadBookings();
                } else {
                    alert('error', response.message);
                }
            }

            xhr.send(formData);
        });
    </script>
</body>
</html>