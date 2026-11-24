<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=whitevillasfinal;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Remove any temporary id=0 row in ardaccounts_ledgers
    $pdo->exec("DELETE FROM `ardaccounts_ledgers` WHERE `id` = 0");

    // Add AUTO_INCREMENT
    $pdo->exec("ALTER TABLE `ardaccounts_ledgers` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT");
    echo "1. ardaccounts_ledgers AUTO_INCREMENT restored.\n";

    $pdo->exec("ALTER TABLE `ardaccounts_settings` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT");
    echo "2. ardaccounts_settings AUTO_INCREMENT restored.\n";

    $pdo->exec("ALTER TABLE `ardaccounts_transactions` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT");
    echo "3. ardaccounts_transactions AUTO_INCREMENT restored.\n";

    $pdo->exec("ALTER TABLE `ardaccounts_journal_entries` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT");
    echo "4. ardaccounts_journal_entries AUTO_INCREMENT restored.\n";

    echo "AUTO_INCREMENT SUCCESSFULLY RESTORED ACROSS ALL TABLES!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
