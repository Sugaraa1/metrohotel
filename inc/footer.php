<div class="container-fluid bg-white mt-5">
    <div class="row">
        <div class="col-lg-4 p-4">
            <h3 class="h-font fw-bold fs-3 mb-2">METRO HOTEL</h3>
            <p>Метро Зочид Буудал нь олон жилийн туршлагатай, мэргэжлийн багтай, зочдын сэтгэл ханамжийг эрхэмлэдэг буудал юм. Бид танд орчин үеийн тав тухтай өрөө, чанартай үйлчилгээ үзүүлж, таны амралтыг дурсамжтай болгохыг эрмэлздэг.</p>
        </div>
        <div class="col-lg-4 p-4">
            <h5 class="mb-3"><strong>Холбоосууд</strong></h5>
            <a href="index.php" class="d-inline-block mb-2 text-dark text-decoration-none">Нүүр</a> <br>
            <a href="rooms.php" class="d-inline-block mb-2 text-dark text-decoration-none">Өрөөнүүд</a> <br>
            <a href="facilities.php" class="d-inline-block mb-2 text-dark text-decoration-none">Үйлчилгээ
</a> <br>
            <a href="contact.php" class="d-inline-block mb-2 text-dark text-decoration-none">Холбоо барих</a> <br>
            <a href="about.php" class="d-inline-block mb-2 text-dark text-decoration-none">Бидний тухай
</a>
        </div> 
        <div class="col-lg-4 p-4">
            <h5 class="mb-3"><strong>Биднийг дагаарай</strong></h5>
            <?php
                if($contact_r['tw']!=''){
                    echo<<<data
                    <a href="{$contact_r['tw']}" class="d-inline-block text-dark text-decoration-none mb-2">
                        <i class="bi bi-twitter me-1"></i> Twitter
                    </a><br>
                    data;
                }
            ?>
            <a href="<?php echo $contact_r['fb'] ?>" class="d-inline-block text-dark text-decoration-none mb-2">
                <i class="bi bi-facebook me-1"></i> Facebook
            </a><br>
            <a href="<?php echo $contact_r['insta'] ?>" class="d-inline-block text-dark text-decoration-none">
                <i class="bi bi-instagram me-1"></i> Instagram
            </a><br>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script>
    function alert(type, msg, position='body') {
        let bs_class = (type == 'success') ? 'alert-success' : 'alert-danger';
        let element = document.createElement('div');
        element.innerHTML = `<div class="alert ${bs_class} alert-dismissible fade show" role="alert">
            <strong class="me-2">${msg}</strong> 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;

        if(position=='body'){
            document.body.append(element);
            element.classList.add('custom-alert');
        } else {
            document.getElementById(position).appendChild(element);
        }
        setTimeout(remAlert, 3000);
    }

    function remAlert(){
        document.getElementsByClassName('alert')[0].remove();
    }

    function setActive() {
        let navbar = document.getElementById('nav-bar');
        let a_tags = navbar.getElementsByTagName('a');

        for(i=0; i<a_tags.length; i++) {
            let file = a_tags[i].href.split('/').pop();
            let file_name = file.split('.')[0];

            if(document.location.href.indexOf(file_name) >= 0){
                a_tags[i].classList.add('active');
            }
        }
    }

    // Register form
    let register_form = document.getElementById('register-form');

    register_form.addEventListener('submit', (e)=>{
        e.preventDefault();

        let data = new FormData();
        
        data.append('name', register_form.elements['name'].value);
        data.append('email', register_form.elements['email'].value);
        data.append('phonenum', register_form.elements['phonenum'].value);      
        data.append('address', register_form.elements['address'].value);
        data.append('pincode', register_form.elements['pincode'].value);
        data.append('dob', register_form.elements['dob'].value);
        data.append('pass', register_form.elements['pass'].value);
        data.append('cpass', register_form.elements['cpass'].value); 
        data.append('profile', register_form.elements['profile'].files[0]);
        data.append('register', '');

        var myModal = document.getElementById('registerModal');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/login_register.php", true);

        xhr.onload = function(){
            console.log("Response:", this.responseText); // Debug хийх
            
            if(this.responseText == 'pass_mismatch'){
                alert('error', "Нууц үг таарахгүй байна!");
            }
            else if(this.responseText == 'email_already'){
                alert('error', "Email бүртгэлтэй байна!");
            }
            else if(this.responseText == 'phone_already'){
                alert('error', "Утасны дугаар бүртгэлтэй байна!");
            }
            else if(this.responseText == 'inv_img'){
                alert('error', "Зөвхөн JPG, JPEG, PNG зураг зөвшөөрнө!");
            }
            else if(this.responseText == 'upd_failed'){
                alert('error', "Зураг хуулахад алдаа гарлаа!");
            }
            else if(this.responseText == 'ins_failed'){
                alert('error', "Бүртгэл амжилтгүй боллоо!");
            }
            else if(this.responseText == '1'){
                alert('success', "Амжилттай бүртгэгдлээ! Та нэвтэрч орно уу.");
                register_form.reset();
            }
            else {
                alert('error', "Алдаа: " + this.responseText);
            }             
        }

        xhr.onerror = function(){
            alert('error', "Сервертэй холбогдоход алдаа гарлаа!");
        }

        xhr.send(data);
    });

    // Login form
    let login_form = document.getElementById('login-form');

    login_form.addEventListener('submit', (e)=>{
        e.preventDefault();

        let data = new FormData();
        data.append('email_or_phone', login_form.elements['email_or_phone'].value);
        data.append('pass', login_form.elements['pass'].value);
        data.append('login', '');

        var myModal = document.getElementById('loginModal');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/login_register.php", true);

        xhr.onload = function(){
            if(this.responseText == 'inv_email_mob'){
                alert('error', "Email эсвэл утасны дугаар олдсонгүй!");
            }
            else if(this.responseText == 'invalid_pass'){
                alert('error', "Нууц үг буруу байна!");
            }
            else if(this.responseText == '1'){
                alert('success', "Амжилттай нэвтэрлээ!");
                setTimeout(function(){
                    window.location.href = 'index.php';
                }, 1500);
            }
            else {
                alert('error', "Алдаа гарлаа!");
            }
        }

        xhr.send(data);
    });

    setActive();
</script>