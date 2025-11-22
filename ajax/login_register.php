<?php 
    require('../admin/inc/db_config.php');
    require('../admin/inc/essentials.php');

    if(isset($_POST['register']))
    {
        $data = filteration($_POST);

        // Нууц үг тохирч байгаа эсэхийг шалгах
        if($data['pass'] != $data['cpass']){
            echo 'pass_mismatch';
            exit;
        }

        // Хэрэглэгч өмнө нь бүртгэлтэй эсэхийг шалгах
        $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1", 
                         [$data['email'], $data['phonenum']], "ss");

        if(mysqli_num_rows($u_exist) != 0){
            $u_exist_fetch = mysqli_fetch_assoc($u_exist);
            echo ($u_exist_fetch['email'] == $data['email']) ? 'email_already' : 'phone_already';
            exit;
        }

        // Хэрэглэгчийн зургийг хадгалах
        $img = uploadUserImage($_FILES['profile']);

        if($img == 'inv_img'){
            echo 'inv_img';
            exit;
        }
        else if($img == 'upd_failed'){
            echo 'upd_failed';
            exit; 
        }

        // Нууц үгийг hash хийх
        $enc_pass = password_hash($data['pass'], PASSWORD_BCRYPT);

        // Database руу өгөгдөл оруулах (is_verified = 1, token = NULL)
        $query = "INSERT INTO `user_cred`(`name`, `email`, `address`, `phonenum`, `pincode`, `dob`, `profile`, `password`, `is_verified`, `token`) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $values = [
            $data['name'], 
            $data['email'], 
            $data['address'], 
            $data['phonenum'], 
            $data['pincode'], 
            $data['dob'], 
            $img, 
            $enc_pass, 
            1,  // is_verified
            NULL  // token
        ];

        // Insert хийх
        $result = insert($query, $values, 'ssssssssii');
        
        if($result)
        {
            echo 1; // Амжилттай бүртгэгдлээ
        }
        else{
            echo 'ins_failed';
        }
    }

    if(isset($_POST['login']))
    {
        $data = filteration($_POST);

        // Email эсвэл утасны дугаар болон нууц үгээр нэвтрэх
        $u_exist = select("SELECT * FROM `user_cred` WHERE (`email`=? OR `phonenum`=?) AND `status`=? LIMIT 1",
                         [$data['email_or_phone'], $data['email_or_phone'], 1], "ssi");

        if(mysqli_num_rows($u_exist) == 0){
            echo 'inv_email_mob'; // Хэрэглэгч олдсонгүй
            exit;
        }

        $u_fetch = mysqli_fetch_assoc($u_exist);

        // Нууц үг тохирч байгаа эсэхийг шалгах
        if(!password_verify($data['pass'], $u_fetch['password'])){
            echo 'invalid_pass'; // Нууц үг буруу
            exit;
        }

        // Session эхлүүлэх
        session_start();
        $_SESSION['login'] = true;
        $_SESSION['uId'] = $u_fetch['id'];
        $_SESSION['uName'] = $u_fetch['name'];
        $_SESSION['uPic'] = $u_fetch['profile'];
        $_SESSION['uPhone'] = $u_fetch['phonenum'];
        $_SESSION['uEmail'] = $u_fetch['email'];

        echo 1; // Амжилттай нэвтэрлээ
    }
?>