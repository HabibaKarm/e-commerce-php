<?php
// Start session FIRST
session_start();

// Enable error reporting during development (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load database config
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

$message = [];

// Handle "Add to Wishlist"
if (isset($_POST['add_to_wishlist'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);

    $check_wishlist = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE name = '$product_name' AND user_id = '$user_id'");
    $check_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'");

    if (mysqli_num_rows($check_wishlist) > 0) {
        $message[] = 'Already added to wishlist';
    } elseif (mysqli_num_rows($check_cart) > 0) {
        $message[] = 'Already added to cart';
    } else {
        $insert = mysqli_query($conn, "INSERT INTO `wishlist` (user_id, pid, name, price, image) VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_image')");
        if ($insert) {
            $message[] = 'Product added to wishlist';
        } else {
            $message[] = 'Wishlist error: ' . mysqli_error($conn);
        }
    }
}


if (isset($_POST['add_to_cart'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = (int)$_POST['product_quantity'];
    $product_quantity = max(1, $product_quantity); 

    $check_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'");

    if (mysqli_num_rows($check_cart) > 0) {
        $message[] = 'Already added to cart';
    } else {

        mysqli_query($conn, "DELETE FROM `wishlist` WHERE name = '$product_name' AND user_id = '$user_id'");

        $insert = mysqli_query($conn, "INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')");
        if ($insert) {
            $message[] = 'Product added to cart';
        } else {
            $message[] = 'Cart error: ' . mysqli_error($conn);
        }
    }
}


$select_categories = mysqli_query($conn, "SELECT * FROM `categories` ORDER BY name ASC");
if (!$select_categories) {
    die('Category fetch error: ' . mysqli_error($conn));
}


$select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6");
if (!$select_products) {
    die('Product fetch error: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css"> <!-- ✅ Use forward slash! -->
</head>
<body>
   
<?php include 'header.php'; ?>



<section class="home">
   <div class="content">
      <h3>new collections</h3>
      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
      <a href="about.php" class="btn">discover more</a>
   </div>
</section>

<!-- Categories Section -->
<section class="categories">
   <h1 class="title">shop by category</h1>
   <div class="box-container">
      <?php if (mysqli_num_rows($select_categories) > 0): ?>
         <?php while ($category = mysqli_fetch_assoc($select_categories)): ?>
            <a href="shop.php?category=<?php echo $category['id']; ?>" class="box">
               <i class="fas fa-tag"></i>
               <h3><?php echo htmlspecialchars($category['name']); ?></h3>
            </a>
         <?php endwhile; ?>
      <?php else: ?>
         <p class="empty">No categories available yet.</p>
      <?php endif; ?>
      <div style="text-align: center; margin-top: 1rem; width: 100%;">
         <a href="shop.php" class="btn">View All Categories</a>
      </div>
   </div>
</section>

<!-- Products Section -->
<section class="products">
   <h1 class="title">latest products</h1>
   <div class="box-container">
      <?php if (mysqli_num_rows($select_products) > 0): ?>
         <?php while ($fetch_products = mysqli_fetch_assoc($select_products)): ?>
         <form action="" method="POST" class="box">
            <a href="view_page.php?pid=<?php echo $fetch_products['id']; ?>" class="fas fa-eye"></a>
            <div class="price">$<?php echo $fetch_products['price']; ?>/-</div>
            <img src="uploaded_img/<?php echo htmlspecialchars($fetch_products['image']); ?>" alt="" class="image">
            <div class="name"><?php echo htmlspecialchars($fetch_products['name']); ?></div>
            <input type="number" name="product_quantity" value="1" min="1" class="qty">
            <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_products['name']); ?>">
            <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_products['image']); ?>">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
         <?php endwhile; ?>
      <?php else: ?>
         <p class="empty">No products added yet!</p>
      <?php endif; ?>
   </div>
   <div class="more-btn">
      <a href="shop.php" class="option-btn">load more</a>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>