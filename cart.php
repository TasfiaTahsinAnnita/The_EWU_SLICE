<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
   exit;
}

// Initialize message array
$message = [];

if (isset($_POST['delete'])) {
   $cart_id = filter_var($_POST['cart_id'], FILTER_SANITIZE_NUMBER_INT);
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$cart_id]);
   $message[] = 'Cart item deleted!';
}

if (isset($_POST['delete_all'])) {
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   $message[] = 'Deleted all from cart!';
}

if (isset($_POST['update_qty'])) {
   $cart_id = filter_var($_POST['cart_id'], FILTER_SANITIZE_NUMBER_INT);
   $qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);
   if ($qty > 0 && $qty <= 99) {
      $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
      $update_qty->execute([$qty, $cart_id]);
      $message[] = 'Cart quantity updated';
   } else {
      $message[] = 'Invalid quantity';
   }
}

if (isset($_POST['update_toppings'])) {
   $cart_id = filter_var($_POST['cart_id'], FILTER_SANITIZE_NUMBER_INT);
   $toppings = isset($_POST['toppings']) && is_array($_POST['toppings']) ? array_map('intval', $_POST['toppings']) : [];
   $toppings_json = json_encode($toppings);
   if (json_last_error() === JSON_ERROR_NONE) {
      $update_toppings = $conn->prepare("UPDATE `cart` SET toppings = ? WHERE id = ?");
      $update_toppings->execute([$toppings_json, $cart_id]);
      $message[] = 'Toppings updated';
   } else {
      $message[] = 'Error encoding toppings';
   }
}

$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cart</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-icon">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>Shopping Cart</h3>
   <p><a href="home.php">home</a> <span> / cart</span></p>
</div>

<section class="products">
   <h1 class="title">Your Cart</h1>

   <?php
   if (!empty($message)) {
      foreach ($message as $msg) {
         echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
      }
   }
   ?>

   <div class="box-container">
      <?php
      try {
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         $select_toppings = $conn->prepare("SELECT * FROM `toppings`");
         $select_toppings->execute();
         $toppings_list = $select_toppings->fetchAll(PDO::FETCH_ASSOC);

         if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {

               // Safe toppings handling
               $toppings_json = $fetch_cart['toppings'];
               if (empty($toppings_json) || !is_string($toppings_json)) {
                  $toppings = [];
               } else {
                  $decoded = json_decode($toppings_json, true);
                  $toppings = is_array($decoded) ? $decoded : [];
               }

               $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
               $toppings_cost = 0;

               foreach ($toppings as $topping_id) {
                  foreach ($toppings_list as $topping) {
                     if ($topping['id'] == $topping_id) {
                        $toppings_cost += $topping['price'] * $fetch_cart['quantity'];
                     }
                  }
               }

               $sub_total += $toppings_cost;
      ?>
      <form action="" method="post" class="box">
         <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
         <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
         <button type="submit" class="fas fa-times" name="delete" onclick="return confirm('Delete this item?');"></button>
         <img src="uploaded_img/<?= htmlspecialchars($fetch_cart['image']); ?>" alt="">
         <div class="name"><?= htmlspecialchars($fetch_cart['name']); ?></div>
         <div class="flex">
            <div class="price"><span>$</span><?= number_format($fetch_cart['price'], 2); ?></div>
            <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" maxlength="2">
            <button type="submit" class="fas fa-edit" name="update_qty"></button>
         </div>
         <div class="toppings">
            <p>Toppings:</p>
            <?php foreach ($toppings_list as $topping) { ?>
               <label>
                  <input type="checkbox" name="toppings[]" value="<?= $topping['id']; ?>"
                     <?= in_array($topping['id'], $toppings) ? 'checked' : ''; ?>>
                  <?= htmlspecialchars($topping['name']); ?> (+$<?= number_format($topping['price'], 2); ?>)
               </label><br>
            <?php } ?>
            <button type="submit" class="fas fa-edit" name="update_toppings">Update Toppings</button>
         </div>
         <div class="sub-total">Sub total: <span>$<?= number_format($sub_total, 2); ?></span></div>
      </form>
      <?php
               $grand_total += $sub_total;
            }
         } else {
            echo '<p class="empty">Your cart is empty</p>';
         }
      } catch (PDOException $e) {
         echo '<p class="error">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
      }
      ?>
   </div>

   <div class="cart-total">
      <p>Cart total: <span>$<?= number_format($grand_total, 2); ?></span></p>
      <a href="checkout.php" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Proceed to checkout</a>
   </div>

   <div class="more-btn">
      <form action="" method="post">
         <button type="submit" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" name="delete_all" onclick="return confirm('Delete all from cart?');">Delete all</button>
      </form>
      <a href="menu.php" class="btn">Continue shopping</a>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
