<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes1/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = $_GET['id'];

    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        $_SESSION['message'] = "Product deleted successfully.";
    } else {
        $_SESSION['message'] = "Product not found.";
    }
} else {
    $_SESSION['message'] = "Invalid product ID.";
}

header("Location: manage_products.php");
exit();
