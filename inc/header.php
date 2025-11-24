<?php
    require('admin/inc/db_config.php');
    require('admin/inc/essentials.php');
    $contact_q = "SELECT * FROM `contact_details` WHERE `sr_no`=?";
    $values =[1];
    $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));

    // Session шалгах
    session_start();
?>

<nav id="nav-bar" class="navbar navbar-expand-lg navbar-light bg-white px-lg-3 py-lg-2 shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand me-5 fw-bold fs-3 h-font" href="index.php">МЕТРО ЗОЧИД БУУДАЛ</a>
        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link me-2" href="index.php">Нүүр</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="rooms.php">Өрөөнүүд</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="facilities.php">Үйлчилгээ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="contact.php">Холбоо барих</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">Бидний тухай</a>
                </li>
            </ul>
            <div class="d-flex">
                <?php
                    if(isset($_SESSION['login']) && $_SESSION['login'] == true) {
                        echo <<<data
                        <div class="dropdown">
                            <button class="btn btn-outline-dark dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> {$_SESSION['uName']}
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="profile.php">Миний мэдээлэл</a></li>
                                <li><a class="dropdown-item" href="bookings.php">Миний захиалгууд</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Гарах</a></li>
                            </ul>
                        </div>
                        data;
                    } else {
                        echo <<<data
                        <button type="button" class="btn btn-outline-dark shadow-none me-lg-3 me-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Нэвтрэх
                        </button>
                        <button type="button" class="btn btn-outline-dark shadow-none" data-bs-toggle="modal" data-bs-target="#registerModal">
                            Бүртгүүлэх
                        </button>
                        data;
                    }
                ?>
            </div>
        </div>
    </div>
</nav>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="login-form">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-circle fs-3 me-2"></i>Нэвтрэх
                    </h5>
                    <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">И-мэйл эсвэл утасны дугаар</label>
                        <input name="email_or_phone" type="text" class="form-control shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Нууц үг</label>
                        <input name="pass" type="password" class="form-control shadow-none" required>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <button type="submit" class="btn btn-dark shadow-none">НЭВТРЭХ</button>
                        <a href="javascript:void(0)" class="text-secondary text-decoration-none">Нууц үг мартсан?</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="register-form">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-lines-fill fs-3 me-2"></i>Бүртгүүлэх
                    </h5>
                    <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span class="badge bg-secondary mb-3 text-wrap lh-base">
                        Анхааруулга: Таны мэдээлэл таны иргэний үнэмлэхтэй тохирч байх ёстой.
                    </span>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Нэр</label>
                                <input name="name" type="text" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">И-мэйл</label>
                                <input name="email" type="email" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Утасны дугаар</label>
                                <input name="phonenum" type="number" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Зураг</label>
                                <input name="profile" type="file" accept=".jpg, .jpeg, .png, .webp" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-12 p-0 mb-3">
                                <label class="form-label">Хаяг</label>
                                <textarea name="address" class="form-control shadow-none" rows="1" required></textarea>
                            </div>
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Шуудангийн код</label>
                                <input name="pincode" type="number" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Төрсөн огноо</label>
                                <input name="dob" type="date" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Нууц үг</label>
                                <input name="pass" type="password" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Нууц үг давтах</label>
                                <input name="cpass" type="password" class="form-control shadow-none" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-center my-1">
                        <button type="submit" class="btn btn-dark shadow-none">БҮРТГҮҮЛЭХ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>