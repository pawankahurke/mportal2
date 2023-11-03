<?php

ob_start();


$showBrandingInit = $_SESSION['showBrandingInit'];

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Dashboard</title>

  <!-- Styles -->
  <link rel="stylesheet" href="slider.css">

  <!-- Fonts -->
  <link href="../assets/css/family=Montserrat.css" rel="stylesheet">
  <link href="../assets/css/family=Oswald.css" rel="stylesheet">
  <meta name="robots" content="noindex,follow" />
</head>

<body>

  <!-- Navigation -->
  <div class="navigation">
    <!-- <div class="navigation-left">
        <a href="#">Shoes</a>
        <a href="#">Clothes</a>
        <a href="#">Accessories</a>
      </div> -->
    <div class="navigation-center">
      <img src="logo.png" alt="">
    </div>
    <div class="navigation-right">
      <a href="#"><img src="images/shopping-bag.png" alt=""></a>
      <button class="login-btn" id="skipPage" onclick="skipSlider('<?php echo $showBrandingInit; ?>')">Skip</button>
    </div>
  </div>

  <!-- Slider Wrapper -->
  <div class="css-slider-wrapper">
    <input type="radio" name="slider" class="slide-radio1" checked id="slider_1">
    <input type="radio" name="slider" class="slide-radio2" id="slider_2">
    <input type="radio" name="slider" class="slide-radio3" id="slider_3">
    <input type="radio" name="slider" class="slide-radio4" id="slider_4">

    <!-- Slider Pagination -->
    <div class="slider-pagination">
      <label for="slider_1" class="page1"></label>
      <label for="slider_2" class="page2"></label>
      <label for="slider_3" class="page3"></label>
      <label for="slider_4" class="page4"></label>
    </div>

    <!-- Slider #1 -->
    <div class="slider slide-1">
      <div class="slider-content">
        <h4>New Product</h4>
        <h2>Nanoheal1</h2>
        <button type="button" class="buy-now-btn" name="button">$130</button>
      </div>
      <div class="number-pagination">
        <span>1</span>
      </div>
    </div>

    <!-- Slider #2 -->
    <div class="slider slide-2">
      <div class="slider-content">
        <h4>New Product</h4>
        <h2>Nanoheal2</h2>
        <button type="button" class="buy-now-btn" name="button">$130</button>
      </div>
      <div class="number-pagination">
        <span>2</span>
      </div>
    </div>

    <!-- Slider #3 -->
    <div class="slider slide-3">
      <div class="slider-content">
        <h4>New Product</h4>
        <h2>Nanoheal3</h2>
        <button type="button" class="buy-now-btn" name="button">$130</button>
      </div>
      <div class="number-pagination">
        <span>3</span>
      </div>
    </div>

    <!-- Slider #4 -->
    <div class="slider slide-4">

      <div class="slider-content">
        <h4>New Product</h4>
        <h2>Nanoheal4</h2>
        <button type="button" class="buy-now-btn" name="button">$130</button>
      </div>
      <div class="number-pagination">
        <span>4</span>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js" charset="utf-8"></script>
  <script src="../js/signup/app.js" charset="utf-8"></script>
</body>

</html>