<?php

require '../../config/function.php';
ensureProductsDeletedAtColumn();

$paraRestultId = isset($_GET['id']) ? $_GET['id'] : null;
if(is_numeric($paraRestultId)){

    $productId = validate($paraRestultId);
    $response = mysqli_query($conn, "UPDATE products SET deleted_at = NULL WHERE id = '$productId' LIMIT 1");

    if($response){
        redirect('products.php', 'Product restored successfully.');
    } else {
        redirect('products.php', 'Something Went Wrong!.');
    }
}
else
{
    redirect('products.php', 'Something Went Wrong!.');
}

?>
