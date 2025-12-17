<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
   exit();
}

// ✅ Safely fetch categories without mysqli_fetch_all()
$categories = [];
$select_categories = mysqli_query($conn, "SELECT * FROM `categories` ORDER BY name ASC");
if ($select_categories && mysqli_num_rows($select_categories) > 0) {
    while ($row = mysqli_fetch_assoc($select_categories)) {
        $categories[] = $row;
    }
}

if (isset($_POST['add_product'])) {

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = mysqli_real_escape_string($conn, $_POST['price']);
   $details = mysqli_real_escape_string($conn, $_POST['details']);
   $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;

  $check_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name'");
   if (!$check_name) {
       $message[] = 'Database error during name check.';
   } elseif (mysqli_num_rows($check_name) > 0) {
       $message[] = 'Product name already exists!';
   } else {
       $cat_value = ($category_id === NULL) ? "NULL" : "'$category_id'";

       $insert_query = "INSERT INTO `products` (name, details, price, image, category_id) 
                        VALUES ('$name', '$details', '$price', '$image', $cat_value)";

       $insert_product = mysqli_query($conn, $insert_query);

       if (!$insert_product) {
           $message[] = 'Failed to add product: ' . mysqli_error($conn);
       } else {
           if ($image_size > 2000000) {
               $message[] = 'Image size is too large (max 2MB)!';
           } else {
               if (move_uploaded_file($image_tmp_name, $image_folder)) {
                   $message[] = 'Product added successfully!';
               } else {
                   $message[] = 'Failed to upload image!';
               }
           }
       }
   }
}

if (isset($_GET['delete'])) {
   $delete_id = (int)$_GET['delete'];

   $select_img = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'");
   $fetch_img = $select_img ? mysqli_fetch_assoc($select_img) : false;

   if ($fetch_img && !empty($fetch_img['image']) && file_exists('uploaded_img/' . $fetch_img['image'])) {
       unlink('uploaded_img/' . $fetch_img['image']);
   }

   mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'");
   mysqli_query($conn, "DELETE FROM `wishlist` WHERE pid = '$delete_id'");
   mysqli_query($conn, "DELETE FROM `cart` WHERE pid = '$delete_id'");

   header('location:admin_products.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'admin_header.php'; ?>


?>

<section class="add-products">
   <form action="" method="POST" enctype="multipart/form-data">
      <h3>add new product</h3>
      <input type="text" class="box" required placeholder="enter product name" name="name">
      <input type="number" min="0" class="box" required placeholder="enter product price" name="price">
      <select name="category_id" class="box" required>
         <option value="">-- select category --</option>
         <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
               <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
         <?php else: ?>
            <option value="">No categories available</option>
         <?php endif; ?>
      </select>

      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>
      <input type="file" accept="image/jpg, image/jpeg, image/png" required class="box" name="image">
      <input type="submit" value="add product" name="add_product" class="btn">
   </form>
</section>

<!-- Product List -->
<section class="show-products">
   <div class="box-container">
      <?php
         $select_products = mysqli_query($conn, "SELECT p.*, c.name AS category_name FROM `products` p LEFT JOIN `categories` c ON p.category_id = c.id");
         if (!$select_products) {
            echo '<p class="empty">Error loading products.</p>';
         } elseif (mysqli_num_rows($select_products) > 0) {
            while ($product = mysqli_fetch_assoc($select_products)) {
      ?>
      <div class="box">
         <div class="price">$<?= htmlspecialchars($product['price']) ?>/-</div>
         <img class="image" width="100%" src="uploaded_img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
         <div class="name"><?= htmlspecialchars($product['name']) ?></div>
         <div class="details"><?= htmlspecialchars($product['details']) ?></div>
         <div class="category">
            Category: <strong><?= $product['category_name'] ? htmlspecialchars($product['category_name']) : 'Uncategorized' ?></strong>
         </div>
         <a href="admin_update_product.php?update=<?= (int)$product['id'] ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?= (int)$product['id'] ?>" class="delete-btn" onclick="return confirm('Delete this product?');">delete</a>
      </div>
      <?php
            }
         } else {
            echo '<p class="empty">No products added yet!</p>';
         }
      ?>
   </div>
</section>

<script src="js/admin_script.js"></script>

</body>
</html>