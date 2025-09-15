<?php
// logout.php - فقط برای لوکال
session_start();
session_destroy();
header('Location: login.php');
exit;
