<?php

require_once('whmcs_config.php');

$mysqli = new mysqli(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
    exit;
}

$result = $mysqli->query("show tables where Tables_in_" . MYSQL_DATABASE . " = '" . TABLE_NAME . "'");
if ($result->num_rows != 0) {
  echo "Table already exist\n";
  exit;
}

$mysqli->query("create table " . TABLE_NAME . "(`invoiceid` int(10) NOT NULL,`created_at` datetime NOT NULL,UNIQUE KEY `idx_invoiceid` (`invoiceid`)) ENGINE=MyISAM");
echo "Database created\n";
