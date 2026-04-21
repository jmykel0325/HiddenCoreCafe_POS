<?php

require '../../config/function.php';
ensureProductsDeletedAtColumn();

$paraRestultId = isset($_GET['id']) ? $_GET['id'] : null;
if (is_numeric($paraRestultId)) {
    $productId = validate($paraRestultId);

    $product = getById('products', $productId);
    if (!$product || $product['status'] != 200) {
        redirect('products.php', 'Invalid product ID.');
        exit;
    }

    $productData = $product['data'];
    if (empty($productData['deleted_at'])) {
        redirect('products.php', 'Only deleted products can be permanently removed.');
        exit;
    }

    $deleteResult = mysqli_query($conn, "DELETE FROM products WHERE id = '$productId' LIMIT 1");
    if ($deleteResult) {
        $imagePath = isset($productData['image']) ? trim((string)$productData['image']) : '';
        if ($imagePath !== '') {
            $absolutePath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagePath);
            if ($absolutePath && file_exists($absolutePath)) {
                @unlink($absolutePath);
            }
        }
        redirect('products.php', 'Product permanently deleted.');
    } else {
        redirect('products.php', 'Failed to permanently delete product.');
    }
} else {
    redirect('products.php', 'Something Went Wrong!.');
}

?>

