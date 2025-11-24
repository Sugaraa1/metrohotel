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
                            if(booking.booking_status != 'cancelled') {
                                cancelBtn = `<button class="btn btn-sm btn-danger" onclick="cancelBooking(${booking.booking_id})">Цуцлах</button>`;
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
    </script>
</body>
</html>