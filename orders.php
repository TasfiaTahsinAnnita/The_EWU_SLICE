<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:home.php');
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-icon">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .orders .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
         gap: 30px;
         padding: 30px;
      }

      .orders .box-container .box {
         background: linear-gradient(145deg, #1e1e2f, #2a2a4a);
         border-radius: 15px;
         box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
         padding: 25px;
         position: relative;
         color: #fff;
         transform: scale(0.8);
         opacity: 0;
         transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.5s ease;
         overflow: hidden;
      }

      .orders .box-container .box.visible {
         transform: scale(1);
         opacity: 1;
      }

      .orders .box-container .box::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: radial-gradient(circle at 50% 50%, rgba(102, 126, 234, 0.3) 0%, transparent 70%);
         opacity: 0.3;
         animation: particle-glow 5s infinite ease-in-out;
         pointer-events: none;
      }

      .orders .box-container .box:hover::before {
         opacity: 0.5;
         animation-duration: 3s;
      }

      .orders .box-container .box p {
         margin: 12px 0;
         font-size: 16px;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .orders .box-container .box p span {
         font-weight: 500;
         color: #e0e0ff;
         max-width: 60%;
         text-align: right;
      }

      .status-container {
         display: flex;
         align-items: center;
         margin: 20px 0;
         position: relative;
      }

      .status-orb {
         width: 50px;
         height: 50px;
         position: relative;
         margin-right: 15px;
      }

      .status-orb .orb-core {
         width: 100%;
         height: 100%;
         border-radius: 50%;
         position: absolute;
         animation: neon-pulse 2s infinite ease-in-out;
      }

      .status-orb .orb-particle {
         position: absolute;
         width: 6px;
         height: 6px;
         background: #fff;
         border-radius: 50%;
         animation: orbit 2s infinite linear;
         top: 50%;
         left: 50%;
      }

      .status-orb .orb-particle:nth-child(2) { animation-delay: -0.5s; }
      .status-orb .orb-particle:nth-child(3) { animation-delay: -1s; }

      .status-pending .orb-core {
         background: #ff6b6b;
         box-shadow: 0 0 20px #ff6b6b, 0 0 40px #ff6b6b;
      }

      .status-processing .orb-core {
         background: #ffa500;
         box-shadow: 0 0 20px #ffa500, 0 0 40px #ffa500;
      }

      .status-shipped .orb-core {
         background: #1e90ff;
         box-shadow: 0 0 20px #1e90ff, 0 0 40px #1e90ff;
      }

      .status-delivered .orb-core {
         background: #32cd32;
         box-shadow: 0 0 20px #32cd32, 0 0 40px #32cd32;
      }

      .status-text {
         font-weight: bold;
         text-transform: capitalize;
         padding: 8px 16px;
         border-radius: 15px;
         color: #fff;
         background: rgba(0, 0, 0, 0.3);
         position: relative;
         z-index: 1;
         animation: text-glow 2s infinite ease-in-out;
      }

      .status-pending .status-text {
         box-shadow: 0 0 15px #ff6b6b;
      }

      .status-processing .status-text {
         box-shadow: 0 0 15px #ffa500;
      }

      .status-shipped .status-text {
         box-shadow: 0 0 15px #1e90ff;
      }

      .status-delivered .status-text {
         box-shadow: 0 0 15px #32cd32;
      }

      @keyframes particle-glow {
         0%, 100% { opacity: 0.3; transform: scale(1); }
         50% { opacity: 0.5; transform: scale(1.1); }
      }

      @keyframes neon-pulse {
         0%, 100% { transform: scale(1); opacity: 0.8; }
         50% { transform: scale(1.2); opacity: 1; }
      }

      @keyframes text-glow {
         0%, 100% { box-shadow: 0 0 15px currentColor; }
         50% { box-shadow: 0 0 25px currentColor, 0 0 40px currentColor; }
      }

      @keyframes orbit {
         0% { transform: translate(-50%, -50%) rotate(0deg) translateX(20px) rotate(0deg); }
         100% { transform: translate(-50%, -50%) rotate(360deg) translateX(20px) rotate(-360deg); }
      }
   </style>
</head>
<body>
   
<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>orders</h3>
   <p><a href="html.php">home</a> <span> / orders</span></p>
</div>

<section class="orders">

   <h1 class="title">your orders</h1>

   <div class="box-container">

   <?php
      if($user_id == ''){
         echo '<p class="empty">please login to see your orders</p>';
      }else{
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
               // Map payment_status to detailed statuses (adjust as needed)
               $status = $fetch_orders['payment_status'] == 'pending' ? 'pending' : 'delivered';
   ?>
   <div class="box">
      <div class="content">
         <p>Placed on: <span><?= $fetch_orders['placed_on']; ?></span></p>
         <p>Name: <span><?= $fetch_orders['name']; ?></span></p>
         <p>Email: <span><?= $fetch_orders['email']; ?></span></p>
         <p>Number: <span><?= $fetch_orders['number']; ?></span></p>
         <p>Address: <span><?= $fetch_orders['address']; ?></span></p>
         <p>Payment Method: <span><?= $fetch_orders['method']; ?></span></p>
         <p>Your Orders: <span><?= $fetch_orders['total_products']; ?></span></p>
         <p>Total Price: <span>$<?= $fetch_orders['total_price']; ?>/-</span></p>
         <div class="status-container status-<?= $status ?>">
            <div class="status-orb">
               <div class="orb-core"></div>
               <div class="orb-particle"></div>
               <div class="orb-particle"></div>
               <div class="orb-particle"></div>
            </div>
            <span class="status-text"><?= $status ?></span>
         </div>
      </div>
   </div>
   <?php
      }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
      }
   ?>

   </div>

</section>

<!-- footer section starts  -->
<?php include 'components/footer.php'; ?>
<!-- footer section ends -->

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
   document.addEventListener('DOMContentLoaded', () => {
      const boxes = document.querySelectorAll('.orders .box-container .box');
      boxes.forEach((box, index) => {
         setTimeout(() => {
            box.classList.add('visible');
         }, index * 500); // Staggered bounce-in animation
      });
   });
</script>

</body>
</html>