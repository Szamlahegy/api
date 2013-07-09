<?php

require_once('whmcs_lib.php');

if (!isset($argv[1])) {
  echo "Please specify invoice in parameter!\n";
  exit;
}

sendInvoice($argv[1]);
