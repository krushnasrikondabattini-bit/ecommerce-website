<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes1/db.php';

$messages = [];

// Add Single Product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];

    move_uploaded_file($_FILES['image']['tmp_name'], "../images/$image");

    $stmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $description, $image]);

    $messages[] = "✅ Single product added successfully!";
}

// Bulk Upload Products from CSV
if (isset($_POST['upload_csv'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== false) {
        $inserted = 0;
        $skipped = 0;

        fgetcsv($handle); // skip header

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $name = $data[0] ?? '';
            $price = $data[1] ?? '';
            $description = $data[2] ?? '';
            $image = $data[3] ?? '';

            if ($name && $price && $description && $image) {
                $stmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $price, $description, $image]);
                $inserted++;
            } else {
                $skipped++;
            }
        }
        fclose($handle);
        $messages[] = "✅ Bulk upload complete: $inserted products added, $skipped rows skipped.";
    } else {
        $messages[] = "❌ Failed to open the CSV file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            width: 60%;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        input, textarea {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #45a049;
        }
        .messages {
            text-align: center;
            margin-bottom: 20px;
        }
        .messages p {
            font-weight: bold;
            color: green;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        hr {
            margin: 40px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Single Product</h2>
        <div class="messages">
            <?php foreach ($messages as $msg): ?>
                <p><?= $msg ?></p>
            <?php endforeach; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" id="price" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="image">Image:</label>
            <input type="file" name="image" id="image" required>

            <button type="submit" name="add_product">Add Product</button>
        </form>

        <hr>

        <h2>Add Bulk Products (CSV Upload)</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="csv_file">CSV File:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>

            <button type="submit" name="upload_csv">Upload Products from CSV</button>
        </form>

        <div class="back-link">
            <a href="manage_products.php">← Back to Manage Products</a>
        </div>
    </div>
</body>
</html>