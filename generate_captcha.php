<?php
// generate_captcha.php
session_start();
$_SESSION['captcha'] = rand(1000, 9999);
echo $_SESSION['captcha'];
