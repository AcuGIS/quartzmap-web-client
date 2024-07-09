<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>QuartzMap | Register</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link rel="apple-touch-icon" sizes="180x180" href="assets/integration/img/favicons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="assets/integration/img/favicons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/integration/img/favicons/favicon-16x16.png">
<link rel="manifest" href="assets/integration/img/favicons/site.webmanifest">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/integration/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/integration/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/integration/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/integration/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/integration/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/integration/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/integration/css/style.css" rel="stylesheet">

</head>

<body>

  <!-- ======= Header ======= -->
  
  <div class="container">
<p>&nbsp;</p>

    <div class="row justify-content-center">
      <div class="col-12 col-xxl-11">
        <div class="card border-light-subtle shadow-sm">
          <div class="row g-0">
            <div class="col-12 col-md-6">
              <img class="img-fluid rounded-start w-100 h-100 object-fit-cover" loading="lazy" src="assets/images/login_page.png" alt="Welcome back you've been missed!">
            </div>
            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
              <div class="col-12 col-lg-11 col-xl-10">
                <div class="card-body p-3 p-md-4 p-xl-5">
                  <div class="row">
                    <div class="col-12">
                      <div class="mb-5">
                        <div class="text-center mb-4">
                          <a href="#!">
                            <img src="assets/integration/img/login_box.png" alt="QuartzMap Registration" width="125" height="125">
                          </a>
                        </div>
                        <div align="center">A verification link will be sent to you.</div><br>
                  <form method="post" action="admin/action/signup.php">
										
										<?php if(!empty($_GET['err'])){ ?>
											<div class="alert alert-danger" role="alert" style="width: 80%"><?=$_GET['err']?></div>
										<?php } else if(!empty($_GET['msg'])){ ?>
											<div class="alert alert-success" role="alert" style="width: 80%"><?=$_GET['msg']?></div>
										<?php } ?>
										
                    <div class="row gy-3 overflow-hidden">
											<div class="col-12">
                        <div class="form-floating mb-3">
                          <input type="text" class="form-control" name="name" id="name" placeholder="John Doe" required>
                          <label for="name" class="form-label">Name</label>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="form-floating mb-3">
                          <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required>
                          <label for="email" class="form-label">Email</label>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="form-floating mb-3">
                          <input type="password" class="form-control" name="password" id="password" value="" placeholder="Password" required>
                          <label for="password" class="form-label">Password</label>
                        </div>
                      </div>
                      <div class="col-12">
                        
                      </div>
                      <div class="col-12">
                        <div class="d-grid">
                          <button class="btn btn-dark btn-lg" type="submit" value="Sign Up" name="submit">Sign up</button>
                        </div>
                      </div>
                    </div>
                  </form>
                  <div class="row">
                    <div class="col-12" align="center"><br>
<p>Have an account? <a href="https://try.quartzmap.com/login.php" class="link-secondary text-decoration-none">Sign In</a></p>

                      <!--<div class="d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-md-center mt-5"> -->

                        <p>QuartzMap - <a href="https://www.acugis.com" class="link-secondary text-decoration-none">From AcuGIS</a></p>
                      </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

		
		
		
		
		
		
		
		
		
   

</body>

</html>