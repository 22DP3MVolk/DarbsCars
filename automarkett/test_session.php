<?php
session_start();
echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "Not set");
echo "<br>Username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : "Not set");
?>