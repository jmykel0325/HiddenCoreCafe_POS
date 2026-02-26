<?php

if(isset($_SESSION['loggedIn'])){

    $username = validate($_SESSION['loggedInUser']['username']);

    $query = "SELECT * FROM cashier_staff WHERE username ='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 0){

        logoutSession();
        redirect('../../login.php', 'Ooops! Access Denied :(');
    }
}else{

    redirect('../../login.php', 'Please, Login to continue...');
}

?>