<?php

require '../../config/function.php';
ensureProductsDeletedAtColumn();

$paraRestultId = isset($_GET['id']) ? $_GET['id'] : null;
if(is_numeric($paraRestultId)){

    $productId = validate($paraRestultId);

    $product = getById('products', $productId);

    if($product['status'] == 200){

        $response = mysqli_query($conn, "UPDATE products SET deleted_at = NOW() WHERE id = '$productId' LIMIT 1");
        if($response)
        {
            redirect('products.php', 'Product moved to deleted items.');
        }
        else
        {
            redirect('products.php', 'Something Went Wrong!.');
        }

    }
    else
        {
        redirect('products.php', $product['message']);
    }

}
else
{
    redirect('products.php', 'Something Went Wrong!.');
}

?>
