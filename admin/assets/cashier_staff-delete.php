<?php

require '../../config/function.php';

$paraRestultId = isset($_GET['id']) ? $_GET['id'] : null;
if(is_numeric($paraRestultId)){

    $adminId = validate($paraRestultId);

    $admin = getById('cashier_staff', $adminId);
    if($admin['status'] == 200){

        $adminDeleteRes = delete('cashier_staff', $adminId);
        if($adminDeleteRes){
            redirect('admins.php', 'Cashier/Staff Deleted Successfully.');
        }
        else{
            redirect('admins.php', 'Something Went Wrong!.');
        }

    }else{
        redirect('admins.php', $admin['message']);
    }

}else{
    redirect('admins.php', 'Something Went Wrong!.');
}

?>