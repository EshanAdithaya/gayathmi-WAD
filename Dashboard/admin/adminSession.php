<?php 
if(!isset($_SESSION["loggedin"]) || $_SESSION["is_admin"] !== 1) {
    header("location: ../../../login.php");
    exit;
}


?>