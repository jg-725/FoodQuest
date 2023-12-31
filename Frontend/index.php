<?php
session_start();

// Checks if the user is logged in. If they are, redirect them to the home page as index.php should not be accessable to logged in users.
if (isset($_SESSION['username']) && isset($_SESSION["userID"])) {
  header("Location: home.php");
  exit();
}
?>



<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
  <head>
    <title>FoodQuest</title>
    <meta charset="utf-8">
    
	<link rel="stylesheet" type="text/css" href="public/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="public/css/main.css" />
    
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
	
    <!-- <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,900"> -->
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <style>.ie-panel{display: none;background: #212121;padding: 10px 0;box-shadow: 3px 3px 5px 0 rgba(0,0,0,.3);clear: both;text-align:center;position: relative;z-index: 1;} html.ie-10 .ie-panel, html.lt-ie-10 .ie-panel {display: block;}
    </style>
  </head>
  <body>
			
  
  
    <div class="ie-panel"><a href="http://windows.microsoft.com/en-US/internet-explorer/"><img src="images/ie8-panel/warning_bar_0000_us.jpg" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."></a></div>
    <div class="preloader">
      <div class="loader">
        <div class="ball"></div>
        <div class="ball"></div>
        <div class="ball"></div>
      </div>
    </div>
    <div class="page">
	
	
      <section class="d-none d-xl-block"><img class="img-responsive" src="images/banner-top-2050x60.png" alt="" width="2050" height="30"/></a></section>
      <div class="position-relative">
        <header class="section page-header" id="programs">
          <!--RD Navbar-->
          <div class="rd-navbar-wrap">
            <nav class="rd-navbar rd-navbar-classic context-dark" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-fixed" data-xl-layout="rd-navbar-static" data-xxl-layout="rd-navbar-static" data-md-device-layout="rd-navbar-fixed" data-lg-device-layout="rd-navbar-fixed" data-xl-device-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static" data-lg-stick-up-offset="10px" data-xl-stick-up-offset="10px" data-xxl-stick-up-offset="10px" data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
              <div class="rd-navbar-main-outer">
                <div class="rd-navbar-main">
                  <!--RD Navbar Panel-->
                  <div class="rd-navbar-panel">
                    <!--RD Navbar Toggle-->
                    <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
                    <!--RD Navbar Brand-->
                    <div class="rd-navbar-brand">
                      <!--Brand--><a class="brand" href="index.php"><img class="brand-logo-dark" src="images/logo-default-363x100.png" alt="" width="181" height="50"/><img class="brand-logo-light" src="images/logo-inverse-363x100.png" alt="" width="181" height="50"/></a>
                    </div>
                  </div>
                  <div class="rd-navbar-nav-wrap">
                    <ul class="rd-navbar-nav">
                
				
 <li class="rd-nav-item"><a class="rd-nav-link" href="login.php">Login</a>
                      </li>
					  
		  <li class="rd-nav-item"><a class="rd-nav-link" href="signup.php">Sign Up</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              
               
                <!--RD Mailform-->
         
         
         
         
         
         
         
              </div>
            </nav>
          </div>
        </header>
        <!--Swiper-->
        <section class="section swiper-container swiper-slider bg-primary" data-autoplay="3500" data-loop="false" data-simulate-touch="false" data-effect="circle-bg" data-speed="750">
          <div class="swiper-bg-text">Food</div>
          <div class="swiper-wrapper">
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Welcome to Plate</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="#1">delivery service</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Plate offers quick delivery of organic &amp; healthy food all over the state.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Why Choose Plate</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-01-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Delivery throughout the Country</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="250">supported locations</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Our food delivery service is available in more than 200 cities for your comfort.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Nationwide Delivery</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-02-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Best dishes &amp; ingredients</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="100%">Delicious Food</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Plate regularly updates the menus to make sure our customers eat the best and the tastiest food.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Organic Ingredients</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-03-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Our clients trust us</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="3k">Positive Reviews</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Our clients’ reviews are the best way to learn more about our food delivery service.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Reviews &amp; Testimonials</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-04-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--Swiper Pagination-->
          <div class="swiper-pagination"></div>
        </section>
      </div>
      <!-- About-->
      <section class="section novi-bg novi-bg-img section-sm bg-gray-100 pb-xl-0" id="about">
        <div class="container">
          <div class="row row-50 flex-wrap-md-reverse flex-lg-wrap align-items-lg-center">
            <div class="col-xl-6 col-lg-6">
              <div class="box-custom-2"><img src="images/shutter-home-01-455x685.png" alt="" width="455" height="342"/>
                <div class="box-custom-2-smal">
                  <p class="box-custom-2-name">Jason Fox</p>
                  <p class="box-custom-2-position">Head Chef</p>
                </div>
              </div>
            </div>
            <div class="col-xl-5 col-lg-6">
              <div class="box-custom-1">
                <h3>About us</h3>
                <h2>healthy food <br> can be delicious</h2>
                <p>Plate was established in 2013, as a reliable food service provider where anyone could order healthy food they like.</p>
                <ul class="list-marked">
                  <li>Choose gluten-free meals from our menu</li>
                  <li>The freshest ingredients for every dish</li>
                  <li>Get great discounts when ordering for 2+ people</li>
                </ul><a class="button button-primary" href="#">read more</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- What we Offer-->
      <section class="section novi-bg novi-bg-img section-md-4 bg-primary">
        <div class="container">
         
          <div class="row row-50 justify-content-center">
            <div class="col-xl-4 col-md-6">
            
                
              </div>
            </div>
            <div class="col-xl-4 col-md-6">
              <!-- Product-->
              
            <div class="col-xl-4 col-md-6">
              <!-- Product-->
              
      </section>

      <!-- How it Works-->
      <section class="section novi-bg novi-bg-img section-md-2 bg-default">
        <div class="container">
          <div class="text-center">
            <h3>how it works</h3>
            <h2>3 steps to healthy eating</h2>
          </div>
          <div class="row row-50 post-classic-counter justify-content-lg-between justify-content-center">
            <div class="col-lg-4 col-sm-6">
              <div class="post-classic novi-bg bg-secondary-1">
                <h3 class="post-classic-title">choose <br> a food package</h3>
                <p class="post-classic-text">First, select the package that you prefer the most.</p>
              </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="post-classic novi-bg bg-secondary-2">
                <h3 class="post-classic-title">customize your menu</h3>
                <p class="post-classic-text">After that, feel free to change and customize your menu.</p>
              </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="post-classic novi-bg bg-secondary-3">
                <h3 class="post-classic-title">Select a delivery <br> address & time</h3>
                <p class="post-classic-text">Finally, let us know where and when to deliver your food.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Advantages-->
      <section class="section novi-bg novi-bg-img section-custom section-lg bg-primary">
        <div class="container">
          <div class="row row-fix">
            <div class="col-lg-7">
              <div class="row row-40">
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-diamond2"></div>
                      <h3 class="box-icon-title">Quality</h3>
                    </div>
                    <p class="box-icon-text">We work with the best suppliers to make sure you get the top-quality dishes, beverages, and service.</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-leaf"></div>
                      <h3 class="box-icon-title">organic</h3>
                    </div>
                    <p class="box-icon-text">All ingredients we use are 100% organic and fresh. Such approach makes our food a lot healthier.</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-chef"></div>
                      <h3 class="box-icon-title">tasty</h3>
                    </div>
                    <p class="box-icon-text">Great and unforgettable taste of our dishes is what attracts more and more clients to Plate.</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-egg2"></div>
                      <h3 class="box-icon-title">Diverse</h3>
                    </div>
                    <p class="box-icon-text">Our team regularly updates the menus to provide you with better food diversity whenever you order.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        

      <!-- Testimonials-->
      

      <section><img class="img-responsive" src="images/banner-bottom-2050x310.jpg" alt="" width="2050" height="155"/></a></section>
      <footer class="section footer-classic">
        <div class="container">
          <div class="row row-50 justify-content-between">
            <div class="col-xl-3 col-md-6">
            
              <!--Brand--><a class="brand" href="index.php"><img class="brand-logo-dark" src="images/logo-default-363x100.png" alt="" width="181" height="50"/><img class="brand-logo-light" src="images/logo-inverse-363x100.png" alt="" width="181" height="50"/></a>
              
              <p class="rights"><span>&copy;&nbsp;</span><span class="copyright-year"></span><span>&nbsp;</span><span>FoodQuest</span><span>.&nbsp;All Rights Reserved. Design by Group10</span></p>
            </div>
            <div class="col-xl-3 col-md-6">
              <p class="footer-classic-title">Contacts</p>
              <ul class="footer-classic-list">
                <li>
                  <ul>
                    <li>
                      <dl class="footer-classic-dl">
                        <dt>Ph.</dt>
                        <dd><a href="tel:#">862-437-2567</a></dd>
                      </dl>
                    </li>
                    <li>
                      <dl class="footer-classic-dl">
                        <dt>Mail.</dt>
                        <dd><a href="mailto:fk82@njit.edu">group10 Project</a></dd>
                      </dl>
                    </li>
                  </ul>
                </li>
                <li><a href="#">235 Washington Ave, Randolph, NJ, 07223</a></li>
                <li>
                  <ul class="group group-sm footer-classic-social-list">
                    <li><a class="link-social" href="#">
                        <div class="icon novi-icon mdi mdi-facebook"></div></a></li>
                    <li><a class="link-social" href="#">
                        <div class="icon novi-icon mdi mdi-instagram"></div></a></li>
                    <li><a class="link-social" href="#">
                        <div class="icon novi-icon mdi mdi-youtube-play"></div></a></li>
                  </ul>
                </li>
              </ul>
            </div>
            
            
          </div>
        </div>
      </footer>
    </div>
    <div class="snackbars" id="form-output-global"></div>
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <!--coded by kraken-->
  </body>
</html>
