<?php
    require('inc/essentials.php');
    require('inc/db_config.php');
    adminLogin();

    if(isset($_GET['approve'])) {
        $frm_data = filteration($_GET);
        $q = "UPDATE `room_reviews` SET `status`=1 WHERE `id`=?";
        $values = [$frm_data['approve']];
        if(update($q,$values,'i')){
            alert('success','Үнэлгээ батлагдлаа');
        } else {
            alert('error','Алдаа гарлаа');
        }
    }

    if(isset($_GET['delete'])) {
        $frm_data = filteration($_GET);
        $q = "DELETE FROM `room_reviews` WHERE `id`=?";
        $values = [$frm_data['delete']];
        if(delete($q,$values,'i')){
            alert('success','Үнэлгээ устгагдлаа');
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
    <title>Admin Panel - Reviews</title>
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
             <h3 class="mb-4">REVIEWS & RATINGS</h3>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                <div class="table-responsive-md" style="height: 450px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead>
                            <tr class="bg-dark text-light">
                            <th scope="col">#</th>
                            <th scope="col">Room</th>
                            <th scope="col">User</th>
                            <th scope="col">Rating</th>
                            <th scope="col" width="30%">Review</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $q = "SELECT r.*, u.name as user_name, rm.name as room_name 
                                      FROM `room_reviews` r
                                      INNER JOIN `user_cred` u ON r.user_id = u.id
                                      INNER JOIN `rooms` rm ON r.room_id = rm.id
                                      ORDER BY r.created_at DESC";
                                $data = mysqli_query($con,$q);
                                $i=1;

                                while($row = mysqli_fetch_assoc($data)){
                                    $status = '';
                                    $action = '';
                                    
                                    if($row['status'] == 0) {
                                        $status = '<span class="badge bg-warning">Хүлээгдэж байна</span>';
                                        $action = "<a href='?approve=$row[id]' class='btn btn-sm rounded-pill btn-success'>Батлах</a>";
                                    } else {
                                        $status = '<span class="badge bg-success">Батлагдсан</span>';
                                    }
                                    
                                    $action .= " <a href='?delete=$row[id]' class='btn btn-sm rounded-pill btn-danger' onclick='return confirm(\"Устгах уу?\")'>Устгах</a>";
                                    
                                    $stars = '';
                                    for($s=0; $s<$row['rating']; $s++) {
                                        $stars .= '<i class="bi bi-star-fill text-warning"></i>';
                                    }
                                    
                                    $date = date('Y-m-d', strtotime($row['created_at']));
                                    
                                    echo<<<query
                                        <tr>
                                            <td>$i</td>
                                            <td>$row[room_name]</td>
                                            <td>$row[user_name]</td>
                                            <td>$stars</td>
                                            <td>$row[review]</td>
                                            <td>$date</td>
                                            <td>$status</td>
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