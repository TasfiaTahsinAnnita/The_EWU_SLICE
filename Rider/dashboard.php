<?php

include '../components/connect.php';

session_start();

$rider_id = $_SESSION['rider_id'] ?? null;

if (!$rider_id) {
   header('location:rider_login.php');
   exit;
}

// ✅ Fetch rider profile info
$select_profile = $conn->prepare("SELECT * FROM `rider` WHERE id = ?");
$select_profile->execute([$rider_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Rider Dashboard</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-ico">

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body style="background-image: url('images/food-1024x683.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

<?php include '../components/rider_header.php' ?>

<!-- rider dashboard section starts -->

<section class="dashboard">

   <h1 class="heading">dashboard</h1>

   <div class="box-container">

     <div class="box">
   <h3>Welcome Rider!</h3>
   <?php if ($fetch_profile): ?>
      <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
      <a href="update_profile.php" class="btn">Update Profile</a>
   <?php else: ?>
      <p style="color:red;">Ready to deliver happiness?</p>
   <?php endif; ?>
</div>


      <div class="box">
         <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_pendings->execute(['pending']);
            while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
               $total_pendings += $fetch_pendings['total_price'];
            }
         ?>
         <h3><span>$</span><?= $total_pendings; ?><span>/-</span></h3>
         <p>Total Pendings</p>
         <a href="placed_orders.php" class="btn">See Orders</a>
      </div>

      <div class="box">
         <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['completed']);
            while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
               $total_completes += $fetch_completes['total_price'];
            }
         ?>
         <h3><span>$</span><?= $total_completes; ?><span>/-</span></h3>
         <p>Total Completes</p>
         <a href="placed_orders.php" class="btn">See Orders</a>
      </div>

      <div class="box">
         <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $numbers_of_orders = $select_orders->rowCount();
         ?>
         <h3><?= $numbers_of_orders; ?></h3>
         <p>Total Orders</p>
         <a href="placed_orders.php" class="btn">See Orders</a>
      </div>

      <div class="box">
         <?php
            $select_riders = $conn->prepare("SELECT * FROM `rider`");
            $select_riders->execute();
            $numbers_of_riders = $select_riders->rowCount();
         ?>
         <h3><?= $numbers_of_riders; ?></h3>
         <p>Riders</p>
         <a href="riders_accounts.php" class="btn">See Riders</a>
      </div>

      <div class="box">
         <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $numbers_of_messages = $select_messages->rowCount();
         ?>
         <h3><?= $numbers_of_messages; ?></h3>
         <p>New Messages</p>
         <a href="messages.php" class="btn">See Messages</a>
      </div>

   </div>

</section>

<!-- rider dashboard section ends -->

<!-- custom js file link -->
<script src="../js/admin_script.js"></script>

</body>
</html>
