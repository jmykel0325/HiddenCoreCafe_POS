<?php

header('Content-Type: application/json');
include(__DIR__ . '/../config/dbcon.php');


function getTableColumns($conn, $table) {
    $cols = [];
    $res = mysqli_query($conn, "DESCRIBE `$table`");
    while($row = mysqli_fetch_assoc($res)) $cols[] = $row['Field'];
    return $cols;
}


$table = isset($_GET['table']) ? mysqli_real_escape_string($conn, $_GET['table']) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;


$tables = [];
$res = mysqli_query($conn, "SHOW TABLES");
while($row = mysqli_fetch_array($res)) $tables[] = $row[0];
if(!in_array($table, $tables)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid table']);
    exit;
}


$method = $_SERVER['REQUEST_METHOD'];
$columns = getTableColumns($conn, $table);

switch($method) {
    case 'GET':
        if($id) {
            $q = mysqli_query($conn, "SELECT * FROM `$table` WHERE id=$id");
            echo json_encode(mysqli_fetch_assoc($q));
        } else {
            $q = mysqli_query($conn, "SELECT * FROM `$table`");
            $data = [];
            while($row = mysqli_fetch_assoc($q)) $data[] = $row;
            echo json_encode($data);
        }
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $fields = [];
        $values = [];
        foreach($columns as $col) {
            if($col == 'id') continue;
            if(isset($input[$col])) {
                $fields[] = "`$col`";
                $values[] = "'" . mysqli_real_escape_string($conn, $input[$col]) . "'";
            }
        }
        $sql = "INSERT INTO `$table` (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
        if(mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => mysqli_error($conn)]);
        }
        break;
    case 'PUT':
        if(!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $sets = [];
        foreach($columns as $col) {
            if($col == 'id' || !isset($input[$col])) continue;
            $sets[] = "`$col`='" . mysqli_real_escape_string($conn, $input[$col]) . "'";
        }
        $sql = "UPDATE `$table` SET " . implode(',', $sets) . " WHERE id=$id";
        if(mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => mysqli_error($conn)]);
        }
        break;
    case 'DELETE':
        if(!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
        $sql = "DELETE FROM `$table` WHERE id=$id";
        if(mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => mysqli_error($conn)]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>