<?php

if(isset($_GET["E_27835hh5396ie1gmalro2ecn0uu41voq6u6gf54otqimfrp972s06asn9e3jf43325_"])){
$server = "sql105.infinityfree.com";
$db_user = "if0_39608776";
$db_pass = "N475369mnM";
$db_name = "if0_39608776_green";
$conn = new mysqli($server, $db_user, $db_pass, $db_name);

$conn->set_charset("utf8mb4");
$data = [];

$sql = "SELECT * FROM users ORDER BY user_id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) { 
    while ($row = $result->fetch_assoc()) {
        $payeer_id = "P".$row['user_id'];
        $data[$payeer_id] = [
            $row['username'],
            $row['email'],
            $row['password'],
            $row['user_agent']
           
        ];
    }
}

// طباعة المصفوفة بتنسيق JSON
echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}else{

 header("Location: / ");
    exit();
}
?>