<?php
$serverName = "FERCHO"; // o nombre del servidor SQL
$connectionOptions = [
    "Database" => "HogarParaTodos",
    "Uid" => "sa",
    "PWD" => "123456"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
    
}else{
    // echo "wazaaa funca alaverga puta mierda maten a ala evelyn";
}
?>
