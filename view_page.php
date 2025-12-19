<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$message = [];

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
      
        $insert = mysqli_query($conn, "INSERT INTO `cart` (user_id, pid, name, price, quantity, image) 
                    VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')");
        
        if ($insert) {
            $message[] = 'Product added to cart';
        } else {
            $message[] = 'Cart error. Please try again.';
        }
    }
}


$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
if ($pid <= 0) {
    die('<p class="empty">Invalid product ID.</p>');
}

$select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$pid'");
if (!$select_products || mysqli_num_rows($select_products) === 0) {
    die('<p class="empty">Product not found!</p>');
}
$fetch_products = mysqli_fetch_assoc($select_products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>quick view</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>



<section class="quick-view">
    <h1 class="title">product details</h1>

    <form action="" method="POST">
        <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="" class="image">
        <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
        <div class="price">$<?= $fetch_products['price']; ?>/-</div>
        <div class="details"><?= nl2br(htmlspecialchars($fetch_products['details'])); ?></div>
        <input type="number" name="product_quantity" value="1" min="1" class="qty">
        <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
        <input type="hidden" name="product_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
        <input type="hidden" name="product_price" value="<?= $fetch_products['price']; ?>">
        <input type="hidden" name="product_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
        <input type="submit" value="add to cart" name="add_to_cart" class="btn">
    </form>

    <div class="more-btn">
        <a href="home.php" class="option-btn">go to home page</a>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>