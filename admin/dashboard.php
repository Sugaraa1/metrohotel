<?php
    require('inc/essentials.php');
    require('inc/db_config.php');
    adminLogin();

    // Нийт статистик
    $total_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings`"))['total'];
    $pending_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE `booking_status`='pending'"))['total'];
    $confirmed_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE `booking_status`='confirmed'"))['total'];
    $cancelled_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE `booking_status`='cancelled'"))['total'];
    
    $total_users = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `user_cred`"))['total'];
    $total_rooms = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `rooms` WHERE `removed`=0"))['total'];
    $total_queries = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `user_queries` WHERE `seen`=0"))['total'];
    $pending_reviews = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `room_reviews` WHERE `status`=0"))['total'];

    // Нийт орлого
    $total_revenue = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(`total_price`) as total FROM `bookings` WHERE `booking_status`='confirmed'"))['total'];
    if(!$total_revenue) $total_revenue = 0;

    // Өнөөдрийн захиалга
    $today = date('Y-m-d');
    $today_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE DATE(`booking_date`)='$today'"))['total'];

    // Топ 5 их захиалагдсан өрөө
    $top_rooms_query = "SELECT r.name, r.id, COUNT(b.booking_id) as booking_count, SUM(b.total_price) as revenue
                        FROM `rooms` r
                        LEFT JOIN `bookings` b ON r.id = b.room_id AND b.booking_status != 'cancelled'
                        WHERE r.removed = 0
                        GROUP BY r.id
                        ORDER BY booking_count DESC
                        LIMIT 5";
    $top_rooms = mysqli_query($con, $top_rooms_query);

    // Захиалга байхгүй өрөөнүүд
    $no_booking_rooms_query = "SELECT r.name, r.id, r.price
                               FROM `rooms` r
                               LEFT JOIN `bookings` b ON r.id = b.room_id
                               WHERE r.removed = 0 AND b.booking_id IS NULL
                               ORDER BY r.name";
    $no_booking_rooms = mysqli_query($con, $no_booking_rooms_query);

    // Сүүлийн 7 хоногийн захиалгын график
    $last_7_days = [];
    for($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE DATE(`booking_date`)='$date'"))['total'];
        $last_7_days[] = ['date' => date('m/d', strtotime($date)), 'count' => $count];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashboard</title>
    <?php require('inc/links.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    #dashboard-menu{
        position: fixed;
        height: 100%;
    }
    @media screen and (max-width: 992px) {
        #dashboard-menu{
            height: auto;
            width: 100%;
        }
        #main-content{
            margin-top: 60px;
        }
    }
    .dashboard-card {
        transition: transform 0.3s;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }
</style>

</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">ХЯНАХ САМБАР</h3>

                <!-- Статистик картууд -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card border-0 shadow dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar-check stat-icon text-primary"></i>
                                <h3 class="mt-2 mb-0"><?php echo $total_bookings; ?></h3>
                                <p class="text-muted mb-0">Нийт захиалга</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card border-0 shadow dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-people stat-icon text-success"></i>
                                <h3 class="mt-2 mb-0"><?php echo $total_users; ?></h3>
                                <p class="text-muted mb-0">Бүртгэлтэй хэрэглэгч</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card border-0 shadow dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-door-open stat-icon text-info"></i>
                                <h3 class="mt-2 mb-0"><?php echo $total_rooms; ?></h3>
                                <p class="text-muted mb-0">Нийт өрөө</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card border-0 shadow dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-currency-dollar stat-icon text-warning"></i>
                                <h3 class="mt-2 mb-0">₮<?php echo number_format($total_revenue); ?></h3>
                                <p class="text-muted mb-0">Нийт орлого</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Захиалгын статус -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Хүлээгдэж байна</h6>
                                        <h3 class="mb-0"><?php echo $pending_bookings; ?></h3>
                                    </div>
                                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Баталгаажсан</h6>
                                        <h3 class="mb-0"><?php echo $confirmed_bookings; ?></h3>
                                    </div>
                                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Цуцлагдсан</h6>
                                        <h3 class="mb-0"><?php echo $cancelled_bookings; ?></h3>
                                    </div>
                                    <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Өнөөдрийн захиалга</h6>
                                        <h3 class="mb-0"><?php echo $today_bookings; ?></h3>
                                    </div>
                                    <i class="bi bi-calendar-day text-primary" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <!-- Сүүлийн 7 хоногийн график -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Сүүлийн 7 хоногийн захиалга</h5>
                                <canvas id="bookingChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Анхааруулга -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Анхааруулга</h5>
                                <div class="list-group list-group-flush">
                                    <?php if($pending_bookings > 0): ?>
                                    <a href="bookings.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-exclamation-circle text-warning me-2"></i>Шинэ захиалга</span>
                                        <span class="badge bg-warning rounded-pill"><?php echo $pending_bookings; ?></span>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($total_queries > 0): ?>
                                    <a href="user_queries.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-envelope text-info me-2"></i>Уншаагүй мессеж</span>
                                        <span class="badge bg-info rounded-pill"><?php echo $total_queries; ?></span>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($pending_reviews > 0): ?>
                                    <a href="reviews.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-star text-warning me-2"></i>Батлах үнэлгээ</span>
                                        <span class="badge bg-warning rounded-pill"><?php echo $pending_reviews; ?></span>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($pending_bookings == 0 && $total_queries == 0 && $pending_reviews == 0): ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Бүх зүйл хэвийн байна!</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Топ өрөөнүүд -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Топ 5 их захиалагдсан өрөө</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Өрөө</th>
                                                <th>Захиалга</th>
                                                <th>Орлого</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if(mysqli_num_rows($top_rooms) > 0) {
                                                while($room = mysqli_fetch_assoc($top_rooms)) {
                                                    $booking_count = $room['booking_count'] ? $room['booking_count'] : 0;
                                                    $revenue = $room['revenue'] ? number_format($room['revenue']) : '0';
                                                    echo "<tr>
                                                        <td><a href='rooms.php' class='text-decoration-none'>{$room['name']}</a></td>
                                                        <td><span class='badge bg-primary'>{$booking_count}</span></td>
                                                        <td>₮{$revenue}</td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='3' class='text-center text-muted'>Өгөгдөл байхгүй</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Захиалга байхгүй өрөөнүүд -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Захиалга байхгүй өрөөнүүд</h5>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Өрөөний нэр</th>
                                                <th>Үнэ/шөнө</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if(mysqli_num_rows($no_booking_rooms) > 0) {
                                                while($room = mysqli_fetch_assoc($no_booking_rooms)) {
                                                    echo "<tr>
                                                        <td>
                                                            <a href='rooms.php' class='text-decoration-none'>{$room['name']}</a>
                                                            <span class='badge bg-danger ms-2'>Захиалга 0</span>
                                                        </td>
                                                        <td>₮" . number_format($room['price']) . "</td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='2' class='text-center text-success'>
                                                    <i class='bi bi-check-circle me-2'></i>Бүх өрөө захиалагдсан!
                                                </td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if(mysqli_num_rows($no_booking_rooms) > 0): ?>
                                <div class="alert alert-warning mb-0 mt-3">
                                    <small><i class="bi bi-exclamation-triangle me-1"></i> 
                                    Эдгээр өрөөнүүд огт захиалагдаагүй байна. Үнэ буюу үйлчилгээг шалгана уу.</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>

    <script>
        // График зурах
        const ctx = document.getElementById('bookingChart').getContext('2d');
        const chartData = <?php echo json_encode($last_7_days); ?>;
        
        const bookingChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [{
                    label: 'Захиалга',
                    data: chartData.map(d => d.count),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>