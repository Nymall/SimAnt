<html><head>
<?php
unlink("saves/" . $_SERVER['REMOTE_ADDR'] . ".csv");
include "index.php";
?>