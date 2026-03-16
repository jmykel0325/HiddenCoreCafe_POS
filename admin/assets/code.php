<?php

include('../../config/function.php');

if(!function_exists('ensureProductsSizeColumn')){
    function ensureProductsSizeColumn($conn){
        static $checked = false;
        static $available = false;

        if ($checked) {
            return $available;
        }

        $checked = true;
        $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'size'");
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $available = true;
            return true;
        }

        $alterResult = mysqli_query($conn, "ALTER TABLE products ADD COLUMN size ENUM('12oz','16oz') NOT NULL DEFAULT '12oz' AFTER name");
        if ($alterResult) {
            $available = true;
        }

        return $available;
    }
}

if(!function_exists('ensureProductsSizePriceColumns')){
    function ensureProductsSizePriceColumns($conn){
        static $checked = false;
        static $available = false;

        if ($checked) {
            return $available;
        }

        $checked = true;
        $has12 = false;
        $has16 = false;

        $check12 = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'price_12oz'");
        if ($check12 && mysqli_num_rows($check12) > 0) {
            $has12 = true;
        }

        $check16 = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'price_16oz'");
        if ($check16 && mysqli_num_rows($check16) > 0) {
            $has16 = true;
        }

        if (!$has12) {
            mysqli_query($conn, "ALTER TABLE products ADD COLUMN price_12oz DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER size");
        }

        if (!$has16) {
            mysqli_query($conn, "ALTER TABLE products ADD COLUMN price_16oz DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price_12oz");
        }

        $verify12 = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'price_12oz'");
        $verify16 = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'price_16oz'");
        $available = ($verify12 && mysqli_num_rows($verify12) > 0) && ($verify16 && mysqli_num_rows($verify16) > 0);

        return $available;
    }
}

if(isset($_POST['SaveCashier/Staff'])){
    if (!empty($_POST['first_name']) && !empty($_POST['last_name']) 
        && !empty($_POST['email']) && !empty($_POST['username']) && !empty($_POST['password']) 
        && !empty($_POST['position'])) {

        $first_name = validate($_POST['first_name']);
        $middle_name = validate($_POST['middle_name'] ?? '');
        $last_name = validate($_POST['last_name']);
        $email = validate($_POST['email']);
        $username = validate($_POST['username']);
        $password = validate($_POST['password']);
        $position = validate($_POST['position']);
        $allowedPositions = ['Cashier', 'Staff', 'Owner'];

        if (!in_array($position, $allowedPositions, true)) {
            redirect('admins-create.php', 'Invalid position selected.');
            exit;
        }

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
    if (!empty($_POST['adminId']) && !empty($_POST['first_name']) 
        && !empty($_POST['last_name']) && !empty($_POST['email']) && !empty($_POST['username']) 
        && !empty($_POST['position'])) {

        $adminId = validate($_POST['adminId']);
        $adminData = getById('cashier_staff', $adminId);
        
        if($adminData['status'] != 200 ){
            redirect('cashier_staff-edit.php?id='.$adminId, 'Invalid Admin ID.');
        }

        $first_name = validate($_POST['first_name']);
        $middle_name = validate($_POST['middle_name'] ?? '');
        $last_name = validate($_POST['last_name']);
        $email = validate($_POST['email']);
        $username = validate($_POST['username']);
        $password = validate($_POST['password'] ?? '');
        $position = validate($_POST['position']);
        $allowedPositions = ['Cashier', 'Staff', 'Owner'];

        if (!in_array($position, $allowedPositions, true)) {
            redirect('cashier_staff-edit.php?id='.$adminId, 'Invalid position selected.');
            exit;
        }

        $isSelfUpdate = isset($_SESSION['loggedInUser']['user_id']) && (int)$_SESSION['loggedInUser']['user_id'] === (int)$adminId;
        $currentPosition = trim((string)($adminData['data']['position'] ?? ''));
        if ($isSelfUpdate && strcasecmp($currentPosition, 'Owner') === 0) {
            // Prevent owner from changing their own role/position.
            $position = $currentPosition;
        }
        
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
            if ($isSelfUpdate) {
                $_SESSION['loggedInUser']['username'] = $username;
                $_SESSION['loggedInUser']['position'] = $position;
            }
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
    $price_12oz = validate($_POST['price_12oz'] ?? '');
    $price_16oz = validate($_POST['price_16oz'] ?? '');
    $quantity = validate($_POST['quantity']);
    $status = isset($_POST['status']) == true ? 1:0;

    if ($price_12oz === '' || $price_16oz === '') {
        redirect('products-create.php', 'Please provide both 12oz and 16oz prices.');
        exit;
    }

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
        'size' => '12oz',
        'price' => $price_12oz,

        'quantity' => $quantity,
        'image' => $finalImage,
        'status' => $status
    ];

    if (ensureProductsSizeColumn($conn) && ensureProductsSizePriceColumns($conn)) {
        $data['price_12oz'] = $price_12oz;
        $data['price_16oz'] = $price_16oz;
    }

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
    $price_12oz = validate($_POST['price_12oz'] ?? '');
    $price_16oz = validate($_POST['price_16oz'] ?? '');
    $quantity = validate($_POST['quantity']);
    $status = isset($_POST['status']) == true ? 1:0;

    if ($price_12oz === '' || $price_16oz === '') {
        redirect('products-edit.php?id='.$product_id, 'Please provide both 12oz and 16oz prices.');
        exit;
    }

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
        'size' => '12oz',
        'price' => $price_12oz,
    
        'quantity' => $quantity,
        'image' => $finalImage,
        'status' => $status
    ];

    if (ensureProductsSizeColumn($conn) && ensureProductsSizePriceColumns($conn)) {
        $data['price_12oz'] = $price_12oz;
        $data['price_16oz'] = $price_16oz;
    }

    $result = update ('products',$product_id, $data);

    if($result){
        redirect('products-edit.php?id='.$product_id, 'Product Updated Successfully'); 
    } else {
        redirect('products-edit.php?id='.$product_id, 'Something Went Wrong!');
    }    
        
}

?>
