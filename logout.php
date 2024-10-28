<?php
session_start();
session_unset();
session_destroy();

header("Location: spinner_logout.php");
exit;
?>