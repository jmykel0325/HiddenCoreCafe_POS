<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/dbcon.php';

if(!function_exists('validate')){
    function validate($inputData){

    global $conn;
    $validatedData = mysqli_real_escape_string($conn, $inputData);
    return trim($validatedData);
}
}

if(!function_exists('redirect')){
    function redirect($url, $status){

    $_SESSION['status'] = $status;
    header('Location: '.$url);
    exit(0);
}
}

if(!function_exists('alertMessage')){
    function alertMessage(){

    if(isset($_SESSION['status'])){
         echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h6>'.$_SESSION['status'].'</h6>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['status']);
    }
}
}

if(!function_exists('insert')){
    function insert($tableName, $data){

    global $conn;

    $table = validate($tableName);

    $columns = array_keys($data);
    $values = array_values($data);

    $finalColumn = implode(',', $columns);
    $finalValues = "'".implode("','", $values)."'";

    $query = "INSERT INTO $table ($finalColumn) VALUES ($finalValues)";
    $result = mysqli_query($conn, $query);
    return $result;
}
}

if(!function_exists('update')){
    function update($tableName, $id, $data){

    global $conn;

    $table = validate($tableName);
    $id = validate($id);

    $updateDataString = '';

    foreach($data as $column => $value){
        $updateDataString .= $column.'='."'$value',";
    }

    $finalUpdateData = substr(trim($updateDataString), 0, -1);

    $query = "UPDATE $table SET $finalUpdateData WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    return $result;
}
}

if(!function_exists('getAll')){
    function getAll($tableName, $status = NULL){

    global $conn;

    $table = validate($tableName);
    $status = validate($status);

    if($table === 'products' && ensureProductsDeletedAtColumn()){
        if($status == 'status'){
            $query = "SELECT * FROM $table WHERE status ='0' AND deleted_at IS NULL";
        }
        else{
            $query = "SELECT * FROM $table WHERE deleted_at IS NULL";
        }
    } else {
        if($status == 'status'){
            $query = "SELECT * FROM $table WHERE status ='0'";
        }
        else{
            $query = "SELECT * FROM $table";
        }
    }
    return mysqli_query($conn, $query);
}
}

if(!function_exists('getById')){
    function getById($tableName, $id){

    global $conn;

    $table = validate($tableName);
    $id = validate($id);

    $query = "SELECT * FROM $table WHERE id='$id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if($result){

        if(mysqli_num_rows($result) == 1){
        
            $row = mysqli_fetch_assoc($result);
            $response = [
                'status' => 200,
                'data' => $row,
                'message' => 'Record Found'
            ];
            return $response;

    }else{

            $response = [
                'status' => 404,
                'message' => 'No Data Found'
            ];
            return $response;
        }

    }else{
        $response = [
            'status' => 500,
            'message' => 'Something Went Wrong'
        ];
        return $response;
    }
}
}

if(!function_exists('delete')){
    function delete($tableName, $id){

    global $conn;

    $table = validate($tableName);
    $id = validate($id);

    $query = "DELETE FROM $table WHERE id='$id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    return $result;
 }
}

if(!function_exists('checkParamId')){
    function checkParamId($type){

    if(isset($_GET[$type])){

        if($_GET[$type] != ''){

            return $_GET[$type];
        }else{

            return '<h5>No Id Found</h5>';
        }

    }else{
        return '<h5>No Id Given</h5>';
    }
}
}

if(!function_exists('logoutSession')){
    function logoutSession(){

    unset($_SESSION['loggedIn']);
    unset($_SESSION['loggedInUser']);

}
}

if(!function_exists('jsonResponse')){
    function jsonResponse($status, $status_type, $message){

    $response = [
        'status' => $status,
        'status_type' => $status_type,
        'messsage' => $message,
    ];
    echo json_encode($response);
    return;
}
}

if(!function_exists('ensureProductsDeletedAtColumn')){
    function ensureProductsDeletedAtColumn(){

    global $conn;
    static $checked = false;
    static $available = false;

    if($checked){
        return $available;
    }

    $checked = true;
    $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'deleted_at'");
    if($checkResult && mysqli_num_rows($checkResult) > 0){
        $available = true;
        return true;
    }

    $alterResult = mysqli_query($conn, "ALTER TABLE products ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER status");
    if($alterResult){
        $available = true;
    }

    return $available;
}
}

if(!function_exists('ensureDailyQuotaTable')){
    function ensureDailyQuotaTable(){

    global $conn;
    static $checked = false;
    static $ready = false;

    if($checked){
        return $ready;
    }

    $checked = true;
    $sql = "CREATE TABLE IF NOT EXISTS daily_quota (
        id INT(11) NOT NULL AUTO_INCREMENT,
        quota_date DATE NOT NULL,
        target_cups INT(11) NOT NULL DEFAULT 0,
        created_by INT(11) NULL DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_quota_date (quota_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $ready = mysqli_query($conn, $sql) ? true : false;
    return $ready;
}
}

if(!function_exists('hasOrdersStatusColumn')){
    function hasOrdersStatusColumn(){

    global $conn;
    static $checked = false;
    static $hasColumn = false;

    if($checked){
        return $hasColumn;
    }

    $checked = true;
    $res = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_status'");
    $hasColumn = ($res && mysqli_num_rows($res) > 0);
    return $hasColumn;
}
}

if(!function_exists('getTodaySoldCups')){
    function getTodaySoldCups(){

    global $conn;

    $where = "DATE(o.created_at) = CURDATE()";
    if(hasOrdersStatusColumn()){
        $where .= " AND LOWER(COALESCE(o.order_status, '')) IN ('completed','paid','served')";
    }

    $sql = "SELECT COALESCE(SUM(oi.quantity), 0) AS sold_cups
            FROM orders o
            INNER JOIN order_items oi ON oi.order_id = o.id
            WHERE $where";
    $result = mysqli_query($conn, $sql);
    if(!$result){
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)($row['sold_cups'] ?? 0);
}
}

if(!function_exists('getTodayQuotaTarget')){
    function getTodayQuotaTarget(){

    global $conn;
    if(!ensureDailyQuotaTable()){
        return 0;
    }

    $result = mysqli_query($conn, "SELECT target_cups FROM daily_quota WHERE quota_date = CURDATE() LIMIT 1");
    if(!$result || mysqli_num_rows($result) === 0){
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return max(0, (int)($row['target_cups'] ?? 0));
}
}

if(!function_exists('saveTodayQuotaTarget')){
    function saveTodayQuotaTarget($targetCups, $createdBy = null){

    global $conn;
    if(!ensureDailyQuotaTable()){
        return false;
    }

    $target = max(0, (int)$targetCups);
    $createdBySql = is_null($createdBy) ? "NULL" : (string)((int)$createdBy);

    $sql = "INSERT INTO daily_quota (quota_date, target_cups, created_by)
            VALUES (CURDATE(), $target, $createdBySql)
            ON DUPLICATE KEY UPDATE
              target_cups = VALUES(target_cups),
              created_by = VALUES(created_by),
              updated_at = CURRENT_TIMESTAMP";

    return mysqli_query($conn, $sql) ? true : false;
}
}
