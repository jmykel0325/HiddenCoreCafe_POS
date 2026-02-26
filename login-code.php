<?php

require 'config/function.php';

if(isset($_POST['loginBtn'])){

    $username = validate($_POST['username']);
    $password = validate($_POST['password']);

    if($username != '' && $password != ''){

        $query = "SELECT * FROM cashier_staff WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($conn, $query);
        if($result){

            if(mysqli_num_rows($result) == 1){

                $row = mysqli_fetch_assoc($result);
                $hashedPassword = $row['password'];

                if(!password_verify($password, $hashedPassword)){
                    redirect('login.php','Invalid Password');
                }

                $_SESSION['loggedIn'] = true;
                $_SESSION['loggedInUser'] = [
                    'user_id' => $row['id'],
                    'username' => $row['username'],

                ];

                redirect('admin/assets/dashboard.php','Logged In Successfully.');

            }else{
                redirect('login.php','Invalid Username');
            }
        }else{
            redirect('login.php','Something went wrong!');
        }
    }else{
        redirect('login.php','Please fill all fields!'); 
    }
}

?>