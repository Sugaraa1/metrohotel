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
            <!-- ШҮҮЛТҮҮР -->
            <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 ps-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
                    <div class="container-fluid flex-lg-column align-items-stretch">
                        <h4 class="mt-2">ШҮҮЛТҮҮР</h4>
                        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropdown" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="filterDropdown">
                            
                            <!-- СУЛ ӨРӨӨ ШАЛГАХ -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="mb-3" style="font-size: 18px;">СУЛ ӨРӨӨ ШАЛГАХ</h5>
                                <label class="form-label">Ирэх огноо</label>
                                <input type="date" id="check_in_filter" class="form-control shadow-none mb-3"> 
                                <label class="form-label">Явах огноо</label>
                                <input type="date" id="check_out_filter" class="form-control shadow-none">
                            </div>

                            <!-- ЗОЧИД -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="mb-3" style="font-size: 18px;">ЗОЧИД</h5>
                                <div class="d-flex">
                                    <div class="me-3">
                                        <label class="form-label">Том хүн</label>
                                        <input type="number" id="adults_filter" min="1" value="1" class="form-control shadow-none"> 
                                    </div>
                                    <div>
                                        <label class="form-label">Хүүхэд</label>
                                        <input type="number" id="children_filter" min="0" value="0" class="form-control shadow-none"> 
                                    </div>   
                                </div>                              
                            </div>

                            <!-- ҮНЭ -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="mb-3" style="font-size: 18px;">ҮНЭ /шөнө/</h5>
                                <div class="mb-2">
                                    <label class="form-label">Доод</label>
                                    <input type="number" id="min_price" min="0" value="0" class="form-control shadow-none">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Дээд</label>
                                    <input type="number" id="max_price" min="0" value="1000000" class="form-control shadow-none">
                                </div>
                            </div>

                            <!-- ТОВЧУУД -->
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-sm btn-primary shadow-none" onclick="applyFilters()">
                                    <i class="bi bi-funnel"></i> Хайх
                                </button>
                                <button class="btn btn-sm btn-secondary shadow-none" onclick="clearFilters()">
                                    <i class="bi bi-x-circle"></i> Цэвэрлэх
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- ӨРӨӨНИЙ ЖАГСААЛТ -->
            <div class="col-lg-9 col-md-12 px-4">
                <div id="rooms_data">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Ачааллаж байна...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <script>
        // Set min date to today
        let today = new Date().toISOString().split('T')[0];
        document.getElementById('check_in_filter').setAttribute('min', today);
        document.getElementById('check_out_filter').setAttribute('min', today);

        // Update check-out min date when check-in changes
        document.getElementById('check_in_filter').addEventListener('change', function() {
            let checkIn = new Date(this.value);
            checkIn.setDate(checkIn.getDate() + 1);
            let minCheckOut = checkIn.toISOString().split('T')[0];
            document.getElementById('check_out_filter').setAttribute('min', minCheckOut);
        });

        // Load all rooms on page load
        window.onload = function() {
            loadRooms();
        }

        function loadRooms(filters = {}) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms_filter.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                document.getElementById('rooms_data').innerHTML = this.responseText;
            }

            let params = 'get_rooms=1';
            for (let key in filters) {
                params += '&' + key + '=' + encodeURIComponent(filters[key]);
            }

            xhr.send(params);
        }

        function applyFilters() {
            let filters = {
                check_in: document.getElementById('check_in_filter').value,
                check_out: document.getElementById('check_out_filter').value,
                adults: document.getElementById('adults_filter').value,
                children: document.getElementById('children_filter').value,
                min_price: document.getElementById('min_price').value,
                max_price: document.getElementById('max_price').value
            };

        }

        function clearFilters() {
            document.getElementById('check_in_filter').value = '';
            document.getElementById('check_out_filter').value = '';
            document.getElementById('adults_filter').value = '1';
            document.getElementById('children_filter').value = '0';
            document.getElementById('min_price').value = '0';
            document.getElementById('max_price').value = '1000000';
            
            document.querySelectorAll('.facility-filter').forEach(function(checkbox) {
                checkbox.checked = false;
            });

            loadRooms();
        }

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