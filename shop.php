<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$message = [];

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$category_id = $category_id > 0 ? $category_id : null;
$category_name = "All Products";

if ($category_id) {
    $cat_query = mysqli_prepare($conn, "SELECT name FROM `categories` WHERE id = ?");
    mysqli_stmt_bind_param($cat_query, "i", $category_id);
    mysqli_stmt_execute($cat_query);
    $cat_result = mysqli_stmt_get_result($cat_query);
    if ($cat_data = mysqli_fetch_assoc($cat_result)) {
        $category_name = htmlspecialchars($cat_data['name']);
    } else {
        $category_id = null;
    }
    mysqli_stmt_close($cat_query);
}

if (isset($_POST['add_to_cart'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = max(1, (int)($_POST['product_quantity'] ?? 1));

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

if ($category_id) {
    $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE category_id = '$category_id'");
} else {
    $select_products = mysqli_query($conn, "SELECT * FROM `products`");
}

if (!$select_products) {
    die('Product query error: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<?php if (!empty($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message">
         <span><?= htmlspecialchars($msg) ?></span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="heading">
    <h3>our shop</h3>
    <p>
        <a href="home.php">home</a> / 
        <?php if ($category_id): ?>
            <span>category: <?= $category_name ?></span>
        <?php else: ?>
            <span>all products</span>
        <?php endif; ?>
    </p>
</section>

<section class="products">
   <h1 class="title">
      latest products
      <?php if ($category_id): ?>
         <span style="font-size: 1.2rem; color: #777;"> — <?= $category_name ?></span>
      <?php endif; ?>
   </h1>

   <div class="box-container">
      <?php if (mysqli_num_rows($select_products) > 0): ?>
         <?php while ($fetch_products = mysqli_fetch_assoc($select_products)): ?>
         <form action="" method="POST" class="box">
            <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
            <div class="price">$<?= $fetch_products['price']; ?>/-</div>
            <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="" class="image">
            <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
            <input type="number" name="product_quantity" value="1" min="1" class="qty">
            <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
            <input type="hidden" name="product_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
            <input type="hidden" name="product_price" value="<?= $fetch_products['price']; ?>">
            <input type="hidden" name="product_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
         <?php endwhile; ?>
      <?php else: ?>
         <p class="empty">No products available in this category.</p>
      <?php endif; ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>