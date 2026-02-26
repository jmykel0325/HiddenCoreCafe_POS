<?php

include('../../config/function.php');

if(isset($_POST['SaveCashier/Staff'])){
    if (!empty($_POST['first_name']) && !empty($_POST['middle_name']) && !empty($_POST['last_name']) 
        && !empty($_POST['email']) && !empty($_POST['username']) && !empty($_POST['password']) 
        && !empty($_POST['position'])) {

        $first_name = validate($_POST['first_name']);
        $middle_name = validate($_POST['middle_name']);
        $last_name = validate($_POST['last_name']);
        $email = validate($_POST['email']);
        $username = validate($_POST['username']);
        $password = validate($_POST['password']);
        $position = validate($_POST['position']);

        // Check if username already exists
        $usernameCheck = mysqli_query($conn, "SELECT * FROM cashier_staff WHERE username='$username'");
        if($usernameCheck && mysqli_num_rows($usernameCheck) > 0){
            redirect('admins-create.php', 'Username already exists. Please choose another.');
            exit;
        }

        $bcrypt_password = password_hash($password, PASSWORD_BCRYPT);

        $data = [
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'email' => $email,
            'username' => $username,
            'password' => $bcrypt_password,
            'position' => $position
        ];
        
        $result = insert('cashier_staff', $data);

        if($result){
            redirect('admins.php', 'Cashier/Staff Created Successfully'); 
        } else {
            redirect('admins-create.php', 'Something Went Wrong!');
        }
    } else {
        redirect('admins-create.php', 'Please fill in all required fields.');
    }
}


if(isset($_POST['updateCashier/Staff'])){ 
    if (!empty($_POST['adminId']) && !empty($_POST['first_name']) && !empty($_POST['middle_name']) 
        && !empty($_POST['last_name']) && !empty($_POST['email']) && !empty($_POST['username']) 
        && !empty($_POST['position'])) {

        $adminId = validate($_POST['adminId']);
        $adminData = getById('cashier_staff', $adminId);
        
        if($adminData['status'] != 200 ){
            redirect('cashier_staff-edit.php?id='.$adminId, 'Invalid Admin ID.');
        }

        $first_name = validate($_POST['first_name']);
        $middle_name = validate($_POST['middle_name']);
        $last_name = validate($_POST['last_name']);
        $email = validate($_POST['email']);
        $username = validate($_POST['username']);
        $password = validate($_POST['password']);
        $position = validate($_POST['position']);
        
        if($password != ''){
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        } else {
            $hashedPassword = $adminData['data']['password'];
        }

        $data = [
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'email' => $email,
            'username' => $username,
            'password' => $hashedPassword,
            'position' => $position 
        ];
        
        $result = update('cashier_staff', $adminId, $data);

        if($result){
            redirect('cashier_staff-edit.php?id='.$adminId, 'Cashier/Staff Updated Successfully'); 
        } else {
            redirect('cashier_staff-edit.php?id='.$adminId, 'Something Went Wrong!');
        }
    } else {
        redirect('admins-create.php', 'Please fill in all required fields.');
    }
}


if (isset($_POST['saveCategory'])){

    $name = validate($_POST['name']);
    $description = validate($_POST['description']);
    $status = isset($_POST['status']) == true ? 1:0;

    $data = [
        'name' => $name,
        'description' => $description,
        'status' => $status
    ];
    $result = insert('categories', $data);

    if($result){
        redirect('categories.php', 'Category Created Successfully'); 
    } else {
        redirect('categories-create.php', 'Something Went Wrong!');
    }   
}

if(isset($_POST['updateCategory'])){

    $categoryId = validate($_POST['categoryId']);

    $name = validate($_POST['name']);
    $description = validate($_POST['description']);
    $status = isset($_POST['status']) == true ? 1:0;

    $data = [
        'name' => $name,
        'description' => $description,
        'status' => $status
    ];
    $result = update('categories', $categoryId, $data);

    if($result){
        redirect('categories-edit.php?id='.$categoryId, 'Category Updated Successfully'); 
    } else {
        redirect('categories-edit.php?id='.$categoryId, 'Something Went Wrong!');
    }
}

if(isset($_POST['saveProduct']))
{
    $category_id = validate($_POST['category_id']);
    $name = validate($_POST['name']);
   

    $price = validate($_POST['price']);
    $quantity = validate($_POST['quantity']);
    $status = isset($_POST['status']) == true ? 1:0;

    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK)
    {
        $path = "../assets/upload/products";
        $image_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    
        $filename = time().'.'.$image_ext;
    
        if(!is_dir($path)) {
            mkdir($path, 0777, true); 
        }
    
        if(move_uploaded_file($_FILES['image']['tmp_name'], $path."/".$filename)) {
            $finalImage = "assets/upload/products/".$filename;
        } else {
            redirect('products-create.php', 'Failed to upload image.');
            exit;
        }
    }
    else
    {
        $finalImage = '';
    }

    $data = [
        'category_id' =>  $category_id,
        'name' => $name,			
        
        'price' => $price,
        'quantity' => $quantity,
        'image' => $finalImage,
        'status' => $status
    ];
    $result = insert('products', $data);

    if($result){
        redirect('products.php', 'Product Created Successfully'); 
    } else {
        redirect('products-create.php', 'Something Went Wrong!');
    }   
}

if(isset($_POST['updateProduct']))
{
    $product_id = validate($_POST['product_id']);

    $productData = getById('products', $product_id);
    if(!$productData){
        redirect('products.php','No such product found');
    }

    $category_id = validate($_POST['category_id']); 
    $name = validate($_POST['name']);
    

    $price = validate($_POST['price']);
    $quantity = validate($_POST['quantity']);
    $status = isset($_POST['status']) == true ? 1:0;

    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK)
    {
        $path = "../assets/upload/products";
        $image_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    
        $filename = time().'.'.$image_ext;
    
        if(!is_dir($path)) {
            mkdir($path, 0777, true); 
        }
    
        if(move_uploaded_file($_FILES['image']['tmp_name'], $path."/".$filename)) {
            $finalImage = "assets/upload/products/".$filename;

            $deletImage = "../".$productData['data']['image'];
            if(file_exists($deletImage)){
                unlink($deletImage);

            }

        } 
        else 
        {
            redirect('products-create.php', 'Failed to upload image.');
            exit;
        }
    }
    else
    {
        $finalImage = $productData['data']['image'];
    }

    $data = [
        'category_id' =>  $category_id,
        'name' => $name,			
    
        'price' => $price,
        'quantity' => $quantity,
        'image' => $finalImage,
        'status' => $status
    ];

    $result = update ('products',$product_id, $data);

    if($result){
        redirect('products-edit.php?id='.$product_id, 'Product Updated Successfully'); 
    } else {
        redirect('products-edit.php?id='.$product_id, 'Something Went Wrong!');
    }    
        
}

?>