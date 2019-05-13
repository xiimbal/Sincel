<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    echo "false";
}else{
    echo "";
}
?>
