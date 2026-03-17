<?php
session_start();
echo "<h3>Session Data:</h3>";
echo "<pre>"; print_r($_SESSION); echo "</pre>";

echo "<h3>Cookie Data:</h3>";
echo "<pre>"; print_r($_COOKIE); echo "</pre>";

echo "<h3>Headers:</h3>";
echo "<pre>"; print_r(getallheaders()); echo "</pre>";

echo "<h3>Server Info:</h3>";
echo "Error Log Path: " . ini_get('error_log') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
?>
