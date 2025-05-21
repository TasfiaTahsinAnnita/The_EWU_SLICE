<?php

include '../components/connect.php';

session_start();

$rider_id = $_SESSION['rider_id'];

if(!isset($rider_id)){
   header('location:rider_login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_rider = $conn->prepare("DELETE FROM `rider` WHERE id = ?");
   $delete_rider->execute([$delete_id]);
   header('location:rider_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admins Accounts</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-ico">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body style="background-image: url('images/2016_09_29_12990_1475116504._large.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

<?php include '../components/rider_header.php' ?>

<!-- admins accounts section starts  -->

<section class="accounts">

   <h1 class="heading">riders account</h1>

   <div class="box-container">

   <div class="box">
      <p>register new rider</p>
      <a href="register_rider.php" class="option-btn">register</a>
   </div>

   <?php
      $select_account = $conn->prepare("SELECT * FROM `rider`");
      $select_account->execute();
      if($select_account->rowCount() > 0){
         while($fetch_accounts = $select_account->fetch(PDO::FETCH_ASSOC)){  
   ?>
   <div class="box">
      <p> rider id : <span><?= $fetch_accounts['id']; ?></span> </p>
      <p> username : <span><?= $fetch_accounts['name']; ?></span> </p>
      <div class="flex-btn">
         <a href="rider_accounts.php?delete=<?= $fetch_accounts['id']; ?>" class="delete-btn" onclick="return confirm('delete this account?');">delete</a>
         <?php
            if($fetch_accounts['id'] == $rider_id){
               echo '<a href="update_profile.php" class="option-btn">update</a>';
            }
         ?>
      </div>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">no accounts available</p>';
   }
   ?>

   </div>

</section>

<!-- admins accounts section ends -->




















<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>