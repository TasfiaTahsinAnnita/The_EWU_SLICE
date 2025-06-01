<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to catch any unexpected output
ob_start();

include 'components/connect.php';

// Verify database connection
if (!$conn) {
   die('Database connection failed. Check components/connect.php');
}

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
   exit;
}

// Fetch user profile data if not already set by user_header.php
$fetch_profile = [];
if ($user_id) {
   try {
      $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
      $select_profile->execute([$user_id]);
      if ($select_profile->rowCount() > 0) {
         $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
      }
   } catch (PDOException $e) {
      $message[] = 'Error fetching profile: ' . htmlspecialchars($e->getMessage());
   }
}

$message = [];

if (isset($_POST['submit'])) {
   $name = $_POST['name'] ?? '';
   $name = trim(strip_tags($name));
   if (empty($name)) $name = $fetch_profile['name'] ?? '';

   $number = $_POST['number'] ?? '';
   $number = trim(strip_tags($number));
   if (empty($number)) $number = $fetch_profile['number'] ?? '';

   $email = $_POST['email'] ?? '';
   $email = trim(strip_tags($email));
   if (empty($email)) $email = $fetch_profile['email'] ?? '';

   $method = $_POST['method'] ?? '';
   $method = trim(strip_tags($method));
   if (empty($method)) {
      $message[] = 'Please select a payment method!';
      $method = '';
   }

   $address = $_POST['address'] ?? '';
   $address = trim(strip_tags($address));
   if (empty($address)) $address = $fetch_profile['address'] ?? '';

   $total_products = $_POST['total_products'] ?? '';
   $total_price = $_POST['total_price'] ?? 0;

   try {
      $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $check_cart->execute([$user_id]);

      if ($check_cart->rowCount() > 0) {
         if (empty($address)) {
            $message[] = 'Please add your address!';
         } else {
            // Prepare and execute the order insertion
            $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

            // Delete cart items
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);

            // Store success message in session
            $_SESSION['message'] = 'Order placed successfully!';
            
            // Redirect to orders.php
            header('location:orders.php');
            exit;
         }
      } else {
         $message[] = 'Your cart is empty';
      }
   } catch (PDOException $e) {
      $message[] = 'Error placing order: ' . htmlspecialchars($e->getMessage());
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Header section starts -->
<?php 
if (!file_exists('components/user_header.php')) {
   die('Error: components/user_header.php not found.');
}
include 'components/user_header.php'; 
?>
<!-- Header section ends -->

<div class="heading">
   <h3>Checkout</h3>
   <p><a href="home.php">Home</a> <span> / Checkout</span></p>
</div>

<section class="checkout">
   <h1 class="title">Order Summary</h1>

   <?php
   if (!empty($message)) {
      foreach ($message as $msg) {
         echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
      }
   }
   ?>

   <form action="" method="post">
      <!-- Rest of the form remains unchanged -->
      <div class="cart-items">
         <h3>Cart Items</h3>
         <?php
         $grand_total = 0;
         $cart_items = [];
         try {
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);

            // Fetch toppings list to calculate their cost
            $select_toppings = $conn->prepare("SELECT * FROM `toppings`");
            $select_toppings->execute();
            $toppings_list = $select_toppings->fetchAll(PDO::FETCH_ASSOC);

            if ($select_cart->rowCount() > 0) {
               while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                  // Calculate base item cost
                  $item_total = $fetch_cart['price'] * $fetch_cart['quantity'];

                  // Handle toppings
                  $toppings_json = $fetch_cart['toppings'] ?? '';
                  if (empty($toppings_json) || !is_string($toppings_json)) {
                     $toppings = [];
                  } else {
                     $decoded = json_decode($toppings_json, true);
                     $toppings = (is_array($decoded) && !empty($decoded)) ? $decoded : [];
                  }

                  // Calculate toppings cost
                  $toppings_cost = 0;
                  foreach ($toppings as $topping_id) {
                     foreach ($toppings_list as $topping) {
                        if ($topping['id'] == $topping_id) {
                           $toppings_cost += $topping['price'] * $fetch_cart['quantity'];
                        }
                     }
                  }

                  // Add toppings cost to item total
                  $item_total += $toppings_cost;

                  // Add to cart items for display
                  $cart_items[] = $fetch_cart['name'] . ' ($' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')';

                  // Add to grand total
                  $grand_total += $item_total;
            ?>
            <p>
               <span class="name"><?= htmlspecialchars($fetch_cart['name']); ?></span>
               <span class="price">$<?= number_format($item_total, 2); ?> (Base: $<?= number_format($fetch_cart['price'], 2); ?> x <?= $fetch_cart['quantity']; ?><?php if ($toppings_cost > 0) { echo ', Toppings: $' . number_format($toppings_cost, 2); } ?>)</span>
            </p>
            <?php
               }
               $total_products = !empty($cart_items) && is_array($cart_items) ? implode(', ', $cart_items) : '';
            } else {
               echo '<p class="empty">Your cart is empty!</p>';
               $total_products = '';
            }
         } catch (PDOException $e) {
            echo '<p class="error">Error fetching cart: ' . htmlspecialchars($e->getMessage()) . '</p>';
            $total_products = '';
         }
         ?>
         <p class="grand-total"><span class="name">Grand Total:</span><span class="price">$<?= number_format($grand_total, 2); ?></span></p>
         <a href="cart.php" class="btn">View Cart</a>
      </div>

      <input type="hidden" name="total_products" value="<?= htmlspecialchars($total_products); ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>">
      <input type="hidden" name="number" value="<?= htmlspecialchars($fetch_profile['number'] ?? ''); ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($fetch_profile['email'] ?? ''); ?>">
      <input type="hidden" name="address" value="<?= htmlspecialchars($fetch_profile['address'] ?? ''); ?>">

      <div class="user-info">
         <h3>Your Info</h3>
         <p><i class="fas fa-user"></i><span><?= htmlspecialchars($fetch_profile['name'] ?? 'Unknown'); ?></span></p>
         <p><i class="fas fa-phone"></i><span><?= htmlspecialchars($fetch_profile['number'] ?? 'Not provided'); ?></span></p>
         <p><i class="fas fa-envelope"></i><span><?= htmlspecialchars($fetch_profile['email'] ?? 'Not provided'); ?></span></p>
         <a href="update_profile.php" class="btn">Update Info</a>
         <h3>Delivery Address</h3>
         <p><i class="fas fa-map-marker-alt"></i><span><?php if (empty($fetch_profile['address'])) { echo 'Please enter your address'; } else { echo htmlspecialchars($fetch_profile['address']); } ?></span></p>
         <a href="update_address.php" class="btn">Update Address</a>
         <select name="method" class="box" required>
            <option value="" disabled selected>Select payment method --</option>
            <option value="cash on delivery">Cash on Delivery</option>
            <option value="credit card">Credit Card</option>
            <option value="paytm">Paytm</option>
            <option value="paypal">Paypal</option>
         </select>
         <input type="submit" value="Place Order" class="btn <?php if (empty($fetch_profile['address'])) { echo 'disabled'; } ?>" style="width:100%; background:var(--red); color:var(--white);" name="submit">
      </div>
   </form>
</section>

<!-- Footer section starts -->
<?php 
if (!file_exists('components/footer.php')) {
   die('Error: components/footer.php not found.');
}
include 'components/footer.php'; 
?>
<!-- Footer section ends -->

<!-- Custom JS file link -->
<script src="js/script.js"></script>

</body>
</html>
<?php
ob_end_flush();
?>