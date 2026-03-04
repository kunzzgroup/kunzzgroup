<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

\System.Management.Automation.Internal.Host.InternalHost = 'localhost';
\ = 'u690174784_kunzz';
\ = 'u690174784_kunzz';
\ = 'Kunzz1688';

try {
    \ = new PDO("mysql:host=\System.Management.Automation.Internal.Host.InternalHost;dbname=\;charset=utf8mb4", \, \);
    \->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check J2 overall stock for 'A&W'
    \ = \->prepare("SELECT price, SUM(in_quantity) - SUM(out_quantity) as qty FROM j2stockedit_data WHERE product_name = 'A&W' GROUP BY price;");
    \->execute();
    \ = \->fetchAll(PDO::FETCH_ASSOC);
    print_r(\);

    // Check specific zero logic
    \ = "0"; // as string, since GET params are strings
    \ = \->prepare("SELECT SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END) - SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END) as available_stock FROM j2stockedit_data WHERE product_name = 'A&W' AND price = ?");
    \->execute([\]);
    \ = \->fetch(PDO::FETCH_ASSOC);
    print_r(\);

} catch (Exception \) {
    echo "Error: " . \->getMessage();
}
