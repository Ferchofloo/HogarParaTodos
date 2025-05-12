<?php
$clave = "admin123";
$hashed = password_hash($clave, PASSWORD_DEFAULT);
echo $hashed;
?>
