<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$message = [];

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$category_name = "All Products";
if ($category_id) {
    $cat_result = mysqli_query($conn, "SELECT name FROM categories WHERE id = $category_id");
    if ($cat_row = mysqli_fetch_assoc($cat_result)) {
        $category_name = $cat_row['name'];
    } else {
        $category_id = 0;
    }
}
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = max(1, (int)($_POST['product_quantity'] ?? 1));

    $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE name='$product_name' AND user_id='$user_id'");
    if (mysqli_num_rows($check_cart) > 0) {
        $message[] = 'Already added to cart';
    } else {
        mysqli_query($conn, "DELETE FROM wishlist WHERE name='$product_name' AND user_id='$user_id'");
        mysqli_query($conn, "INSERT INTO cart (user_id, pid, name, price, quantity, image) 
                             VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')");
        $message[] = 'Product added to cart';
    }
}

$sql = "SELECT * FROM products";
if ($category_id) $sql .= " WHERE category_id=$category_id";
$products = mysqli_query($conn, $sql);
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
      <?php if (mysqli_num_rows($products) > 0): ?>
           <?php while ($prod = mysqli_fetch_assoc($products)): ?>
         <form  method="POST" class="box">
            <a href="view_page.php?pid=<?= $prod['id'] ?>" class="fas fa-eye"></a>
            <div class="price">$<?= $prod['price']; ?>/-</div>
            <img src="uploaded_img/<?= htmlspecialchars($prod['image']); ?>" alt="" class="image">
            <div class="name"><?= htmlspecialchars($prod['name']); ?></div>
            <input type="number" name="product_quantity" value="1" min="1" class="qty">
            <input type="hidden" name="product_id" value="<?= $prod['id']; ?>">
            <input type="hidden" name="product_name" value="<?= htmlspecialchars($prod['name']); ?>">
            <input type="hidden" name="product_price" value="<?= $prod['price']; ?>">
            <input type="hidden" name="product_image" value="<?= htmlspecialchars($prod['image']); ?>">
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