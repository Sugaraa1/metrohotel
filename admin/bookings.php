<?php
    require('inc/essentials.php');
    require('inc/db_config.php');
    adminLogin();

    if(isset($_GET['confirm'])) {
        $frm_data = filteration($_GET);
        $q = "UPDATE `bookings` SET `booking_status`='confirmed' WHERE `booking_id`=?";
        $values = [$frm_data['confirm']];
        if(update($q,$values,'i')){
            alert('success','Захиалга баталгаажлаа');
        } else {
            alert('error','Алдаа гарлаа');
        }
    }

    if(isset($_GET['cancel'])) {
        $frm_data = filteration($_GET);
        $q = "UPDATE `bookings` SET `booking_status`='cancelled' WHERE `booking_id`=?";
        $values = [$frm_data['cancel']];
        if(update($q,$values,'i')){
            alert('success','Захиалга цуцлагдлаа');
        } else {
            alert('error','Алдаа гарлаа');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Bookings</title>
    <?php require('inc/links.php'); ?>

<style>
    #dashboard-menu{
    position: fixed;
    height: 100%;
}
@media screen and (max-width: 992px) {
    #dashboard-menu{
        height: auto;
        width: 100%;
        z-index: 11;
    }
    #main-content{
    margin-top: 60px;
    }
}
</style>

</head>
<body class="bg-white">

<?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
             <h3 class="mb-4">ЗАХИАЛГУУД</h3>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                <div class="table-responsive-md" style="height: 450px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead>
                            <tr class="bg-dark text-light">
                            <th scope="col">#</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">User</th>
                            <th scope="col">Room</th>
                            <th scope="col">Check-in</th>
                            <th scope="col">Check-out</th>
                            <th scope="col">Price</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $q = "SELECT b.*, u.name as user_name, u.email, r.name as room_name 
                                      FROM `bookings` b
                                      INNER JOIN `user_cred` u ON b.user_id = u.id
                                      INNER JOIN `rooms` r ON b.room_id = r.id
                                      ORDER BY b.booking_date DESC";
                                $data = mysqli_query($con,$q);
                                $i=1;

                                while($row = mysqli_fetch_assoc($data)){
                                    $status = '';
                                    $action = '';
                                    
                                    if($row['booking_status'] == 'pending') {
                                        $status = '<span class="badge bg-warning">Хүлээгдэж байна</span>';
                                        $action = "<a href='?confirm=$row[booking_id]' class='btn btn-sm btn-success mb-1'>Батлах</a><br>";
                                        $action .= "<a href='?cancel=$row[booking_id]' class='btn btn-sm btn-danger' onclick='return confirm(\"Цуцлах уу?\")'>Цуцлах</a>";
                                    } else if($row['booking_status'] == 'confirmed') {
                                        $status = '<span class="badge bg-success">Баталгаажсан</span>';
                                        $action = "<a href='?cancel=$row[booking_id]' class='btn btn-sm btn-danger' onclick='return confirm(\"Цуцлах уу?\")'>Цуцлах</a>";
                                    } else {
                                        $status = '<span class="badge bg-danger">Цуцлагдсан</span>';
                                        $action = '-';
                                    }
                                    
                                    $date = date('Y-m-d', strtotime($row['booking_date']));
                                    
                                    echo<<<query
                                        <tr>
                                            <td>$i</td>
                                            <td>$row[order_id]</td>
                                            <td>
                                                <strong>$row[user_name]</strong><br>
                                                <small>$row[email]</small>
                                            </td>
                                            <td>$row[room_name]</td>
                                            <td>$row[check_in]</td>
                                            <td>$row[check_out]</td>
                                            <td>₮$row[total_price]</td>
                                            <td>$status</td>
                                            <td>$date</td>
                                            <td>$action</td>
                                        </tr>
                                    query;
                                    $i++;
                                }
                            ?>
                        </tbody>
                    </table>
                </div>

                </div>
            </div>

        </div>
    </div>
</div>

    <?php require('inc/scripts.php'); ?>

</body>
</html>