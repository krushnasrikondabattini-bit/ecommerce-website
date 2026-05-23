<?php
session_start();
include '../includes1/db.php';

// Redirect if not logged in (optional based on your logic)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $id = (int) $_POST['product_id'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header("Location: cart.php");
    exit;
}

// Handle Quantity Update
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $qty) {
        $qty = max(0, (int) $qty);
        if ($qty === 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
    header("Location: cart.php");
    exit;
}

// Fetch product data for items in the cart
$products = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(",", array_keys($_SESSION['cart']));
    $stmt = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$product) {
        $qty = $_SESSION['cart'][$product['id']];
        $product['quantity'] = $qty;
        $product['subtotal'] = $qty * $product['price'];
        $total += $product['subtotal'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table { width: 80%; margin: auto; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        img { width: 50px; }
        .btn { padding: 5px 10px; }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Your Shopping Cart</h1>

    <?php if (empty($products)) : ?>
        <p style="text-align:center;">Your cart is empty. <a href="../index.php">Continue shopping</a></p>
    <?php else : ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p) : ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']); ?></td>
                            <td><img src="../images/<?= htmlspecialchars($p['image']); ?>" alt=""></td>
                            <td>$<?= number_format($p['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantities[<?= $p['id']; ?>]" value="<?= $p['quantity']; ?>" min="0" style="width:60px;">
                            </td>
                            <td>$<?= number_format($p['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Total:</strong></td>
                        <td><strong>$<?= number_format($total, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div style="text-align:center; margin-top: 20px;">
                <button type="submit" name="update_cart" class="btn">Update Cart</button>
                <a href="../index.php" class="btn">Continue Shopping</a>
            </div>
        </form>
    <?php endif; ?>
</body>
</html>