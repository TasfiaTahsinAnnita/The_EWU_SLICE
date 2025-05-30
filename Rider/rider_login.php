<?php

include '../components/connect.php';

session_start();

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_rider = $conn->prepare("SELECT * FROM `rider` WHERE name = ? AND password = ?");
   $select_rider->execute([$name, $pass]);
   
   if($select_rider->rowCount() > 0){
      $fetch_rider_id = $select_rider->fetch(PDO::FETCH_ASSOC);
      $_SESSION['rider_id'] = $fetch_rider_id['id'];
      header('location:dashboard.php');
   }else{
      $message[] = 'incorrect username or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Rider login</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-icon">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      /* Header Styling */
      header {
         display: flex;
         align-items: center;
         background-color: #333;
         padding: 15px;
         color: #fff;
         justify-content: space-between; /* Ensures space between logo and text */
         padding-left: 30px; /* Adjusts the left padding for logo */
         padding-right: 30px; /* Adjusts the right padding for centering text */
      }

      header img {
         width: 150px;
         height: 75px;
         margin-right: 20px;
      }

      header h1 {
         font-size: 24px;
         font-weight: bold;
         letter-spacing: 1px;
         text-transform: uppercase;
         position: absolute;
         left: 50%;
         transform: translateX(-50%); /* Centers the text exactly */
      }
   </style>

</head>
<body style="background-image: url('images/food-1024x683.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

<!-- Header Section -->
<header>
   <img src="images/riderslice.png" alt="Logo"> <!-- Replace with your logo image -->
   <h1>Rider Portal</h1>
</header>

<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<!-- Rider login form section starts  -->
<section class="form-container" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-radius: 15px; padding: 20px; width: 300px; margin: auto; text-align: center;">
   <form action="" method="POST">
      <h3 style="color: #000000;">login now</h3>
      <input type="text" name="name" maxlength="20" required placeholder="enter your username" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" maxlength="20" required placeholder="enter your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="login now" name="submit" class="btn" style="background-color: #4CAF50; color: #fff; border: none; padding: 10px 15px; cursor: pointer;">
   </form>
</section>

<!-- Rider login form section ends -->

</body>
</html>
