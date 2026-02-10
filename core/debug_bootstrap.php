<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_error_handler(function($errno,$errstr,$errfile,$errline){
    echo "<pre style='background:#111;color:#0f0;padding:15px'>";
    echo "PHP ERROR:\n$errstr\nFILE: $errfile\nLINE: $errline";
    echo "</pre>";
    return true;
});

set_exception_handler(function($e){
    echo "<pre style='background:#300;color:#fff;padding:15px'>";
    echo "UNCAUGHT EXCEPTION:\n".$e->getMessage()."\n".$e->getFile().":".$e->getLine();
    echo "</pre>";
});
?>
