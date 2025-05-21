<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/add_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Menu</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-icon">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .products .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
         gap: 30px;
         padding: 40px;
         background: #f5f6fa;
      }

      .products .box-container .box {
         background: #ffffff;
         border-radius: 15px;
         padding: 20px;
         position: relative;
         color: #333;
         transform: translateY(50px);
         opacity: 0;
         transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.7s ease;
         box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      }

      .products .box-container .box.visible {
         transform: translateY(0);
         opacity: 1;
         animation: glow-border 3s infinite ease-in-out;
      }

      .products .box-container .box:hover {
         transform: translateY(-10px);
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15), 0 0 20px rgba(255, 127, 127, 0.5);
      }

      .products .box-container .box .image-container {
         position: relative;
         width: 100%;
         height: 200px;
         border-radius: 10px;
         overflow: hidden;
      }

      .products .box-container .box img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         transition: transform 0.5s ease, box-shadow 0.5s ease;
      }

      .products .box-container .box:hover img {
         transform: scale(1.08);
         box-shadow: 0 0 15px rgba(0, 245, 212, 0.5);
      }

      .products .box-container .box .cat {
         display: inline-block;
         margin: 10px 0;
         font-size: 14px;
         color: #666;
         text-decoration: none;
         transition: color 0.3s ease;
      }

      .products .box-container .box .cat:hover {
         color: #ff7f7f;
      }

      .products .box-container .box .name {
         font-size: 20px;
         font-weight: 600;
         color: #333;
         margin: 10px 0;
      }

      .products .box-container .box .flex {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-top: 15px;
         gap: 10px;
         flex-wrap: wrap;
      }

      .products .box-container .box .price {
         font-size: 20px;
         font-weight: bold;
         color: #ff7f7f;
         position: relative;
         animation: neon-glow 2s infinite ease-in-out;
      }

      .products .box-container .box .price::after {
         content: '';
         position: absolute;
         bottom: -4px;
         left: 0;
         width: 100%;
         height: 2px;
         background: linear-gradient(90deg, #ff7f7f, #00f5d4);
         animation: glow-underline 2s infinite ease-in-out;
         opacity: 0.6;
      }

      .products .box-container .box .price span {
         font-size: 16px;
      }

      .products .box-container .box .qty {
         width: 70px;
         padding: 10px;
         border: 1px solid #ddd;
         border-radius: 6px;
         background: #f9f9f9;
         color: #333;
         text-align: center;
         font-size: 16px;
         transition: border-color 0.3s ease, box-shadow 0.3s ease;
      }

      .products .box-container .box .qty:focus {
         border-color: #00f5d4;
         box-shadow: 0 0 10px #00f5d4;
      }

      .products .box-container .box .button-group {
         display: flex;
         flex-direction: column;
         gap: 8px;
         align-items: center;
      }

      .products .box-container .box .action-btn {
         padding: 8px 12px;
         background: linear-gradient(135deg, #ff7f7f, #00f5d4);
         border: none;
         border-radius: 8px;
         font-size: 14px;
         color: #fff;
         text-decoration: none;
         text-align: center;
         cursor: pointer;
         box-shadow: 0 2px 6px rgba(0,0,0,0.15);
         transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .products .box-container .box .action-btn:hover {
         transform: scale(1.05);
         box-shadow: 0 0 12px rgba(0, 245, 212, 0.6), 0 0 18px rgba(255, 127, 127, 0.6);
      }

      .products .box-container .box .action-btn:active {
         transform: scale(0.95);
         box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
      }

      @keyframes glow-border {
         0%, 100% { box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
         50% { box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1), 0 0 15px rgba(255, 127, 127, 0.4), 0 0 25px rgba(0, 245, 212, 0.4); }
      }

      @keyframes neon-glow {
         0%, 100% { text-shadow: 0 0 5px #ff7f7f, 0 0 10px #ff7f7f, 0 0 20px #00f5d4; }
         50% { text-shadow: 0 0 10px #ff7f7f, 0 0 20px #ff7f7f, 0 0 30px #00f5d4, 0 0 40px #00f5d4; }
      }

      @keyframes glow-underline {
         0%, 100% { opacity: 0.6; transform: scaleX(1); }
         50% { opacity: 1; transform: scaleX(1.1); }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>our menu</h3>
   <p><a href="home.php">home</a> <span> / menu</span></p>
</div>

<section class="products">

   <h1 class="title">latest dishes</h1>

   <div class="box-container">

      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
      ?>
      <form action="" method="post" class="box">
         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
         <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
         <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
         <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
         <div class="image-container">
            <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
         </div>
         <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
         <div class="name"><?= $fetch_products['name']; ?></div>
         <div class="flex">
            <div class="price"><span>$</span><?= $fetch_products['price']; ?></div>
            <div class="button-group">
               <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="action-btn">Quick View</a>
               <button type="submit" name="add_to_cart" class="action-btn">Add to Cart</button>
            </div>
            <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
         </div>
      </form>
      <?php
            }
         }else{
            echo '<p class="empty">no products added yet!</p>';
         }
      ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
   document.addEventListener('DOMContentLoaded', () => {
      const boxes = document.querySelectorAll('.products .box-container .box');
      boxes.forEach((box, index) => {
         setTimeout(() => {
            box.classList.add('visible');
         }, index * 350);
      });
   });
</script>

</body>
</html>
