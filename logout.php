<?php
session_start();
session_unset();
session_destroy();
header("Location: test211.php");
exit();
?>