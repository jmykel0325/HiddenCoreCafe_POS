<?php

require '../../config/function.php';

$paraRestultId = isset($_GET['id']) ? $_GET['id'] : null;
if(is_numeric($paraRestultId)){

    $productId = validate($paraRestultId);

    $product = getById('products', $productId);

    if($product['status'] == 200){

        $response = delete('products', $productId);
        if($response)
        {
            $deleteImagec = "../".$product['data']['image'];
            if(file_exists($deleteImage)){
                unlink($deleteImage);
            }

            redirect('products.php', 'Product Deleted Successfully.');
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