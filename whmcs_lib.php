<?php

// TODO 
//  - lekérdezést kiszedni kölün függvénybe
//  - bind a lekérdezésekben
//  - össze lehet rakni több lekérdezést egybe join-al

require_once('whmcs_config.php');
require_once('classes.php');
require_once('api.php');

function sendInvoices($invoices) {
  global $mysqli;
  
  if ($invoices->num_rows == 0) {
    echo "There is no invoice to generate!\n";
    exit;
  }
  
  $szamlahegyApi = new SzamlahegyApi();
  $szamlahegyApi->openHTTPConnection();

  while ($invoice = $invoices->fetch_object()){
    echo "--- Processing invoice #" . $invoice->id . "\n";

    $client = null;
    $vatnr = null;
    $postalAddress = null;

    $client = $mysqli->query("SELECT * FROM tblclients c WHERE c.id = " . $invoice->userid);
    $client = $client->fetch_object();

    $custom = $mysqli->query("SELECT * FROM tblcustomfieldsvalues c WHERE c.fieldid = " . CUSTOMFIELDID_VAT . " and c.relid = " . $invoice->userid);
    
    if ($custom->num_rows > 0 && !is_null($client->companyname) && $client->companyname !== "") {
      $custom = $custom->fetch_object();
      $vatnr = $custom->value;
    } else {
      $vatnr = null;
    }

    $custom = $mysqli->query("SELECT * FROM tblcustomfieldsvalues c WHERE c.fieldid = " . CUSTOMFIELDID_POSTAL . " and c.relid = " . $invoice->userid);
    if ($custom->num_rows > 0) {
      $custom = $custom->fetch_object();
      $postalAddress = $custom->value;
    } else {
      $postalAddress = null;
    }
    
    // Billing contact
    if ($client->billingcid !=0 && !is_null($client->billingcid)) {
      $client = $mysqli->query("SELECT * FROM tblcontacts c WHERE c.id = " . $client->billingcid);
      $client = $client->fetch_object();
    }
    
    $invoiceRowsResult = $mysqli->query("SELECT * FROM tblinvoiceitems i WHERE i.invoiceid = " . $invoice->id);

    $i = new Invoice();
    if (is_null($client->companyname) || $client->companyname === "") {
      $i->customer_name = $client->lastname . ' ' . $client->firstname;
    } else {
      if ($client->country != 'HU' || checkCompanyName($client->companyname)) {
        $i->customer_name = $client->companyname;
      } else {
        echo "Invalid company name: " . $client->companyname . "\n";
        echo "Client id #" . $invoice->userid . "\n";
        continue;
      }
    }

    //$i->customer_detail;
    $i->customer_city = $client->city;
    $i->customer_address = $client->address1 . ' ' . $client->address2;
    $i->customer_vatnr = $vatnr;
    $i->payment_method = PAYMENT_METHOD;
    //$i->invoice_date = $invoice->date;
    $i->payment_date = $invoice->datepaid;
    $i->perform_date = $invoice->datepaid;
    //$i->header;
    $i->footer = 'Díjbekérő számla iktatószáma: #' . $invoice->id . "</br>\n";
    if (isset($postalAddress) && $postalAddress !== '') {
      $i->footer .= 'Postázási cím: ' . $postalAddress . "</br>\n";
    }

    $i->customer_zip = $client->postcode;
    //$i->kind;
    $i->tag = $invoice->id;
    $i->foreign_id = $invoice->id;
    $i->paid_at = $invoice->datepaid;
    $i->customer_email = $client->email;
    $i->customer_contact_name = $client->lastname . ' ' . $client->firstname;
    $i->customer_country = $client->country; 
    $rows = array();
    $price = 0;
    
    while ($invoiceRow = $invoiceRowsResult->fetch_object()){
      $row = new InvoiceRow();
      $row->productnr = HOSTING_PRODUCTNR;
      $row->name = $invoiceRow->description;
      $row->quantity = 1;
      $row->quantity_type = QUANTITY_TYPE;
      $row->price_slab = intval($invoiceRow->amount);
      $row->tax = HOSTING_TAX;
      $rows[] = $row;
      $price += $row->quantity * $row->price_slab;
    }
    
    // Process credit
    if ($invoice->credit != 0) {
      $row = new InvoiceRow();
      $row->productnr = HOSTING_PRODUCTNR;
      $row->name = CREDIT_PRODUCT_NAME;
      $row->quantity = 1;
      $row->quantity_type = QUANTITY_TYPE;
      $row->price_slab = intval($invoice->credit / (HOSTING_TAX / 100 + 1) * -1);
      $row->tax = HOSTING_TAX;
      $rows[] = $row;
      $price += $row->quantity * $row->price_slab;
    }
    
    $i->invoice_rows_attributes = $rows;
    
    if ($price > 0) {
      if ($szamlahegyApi->sendNewInvoice($i)) {
        $mysqli->query("INSERT into invoice_sent (invoiceid,created_at) values (". $invoice->id . ",now())");
        echo "Invoice generation successed: #" . $invoice->id . "\n\n";
      } else {
        echo "Error during invoice generation #" . $invoice->id . "\n";
        echo "Client id #" . $invoice->userid . "\n\n";
      }
    } else {
      echo "Invoice price is zero! #" . $invoice->id . "\n";
      echo "Client id #" . $invoice->userid . "\n\n";
      $mysqli->query("INSERT into invoice_sent (invoiceid,created_at) values (". $invoice->id . ",now())");
    }
  }

  $szamlahegyApi->closeHTTPConnection();
}

function connectToMySql() {
  global $mysqli;
  $mysqli = new mysqli(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);
  if ($mysqli->connect_errno) {
      echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
      return false;
  }
  return $mysqli;
}

function sendAllNewInvoices() {
  global $mysqli;
  if (!$mysqli = connectToMySql()) {
    exit;
  }
   
  $invoices = $mysqli->query("SELECT * FROM tblinvoices i WHERE i.id not in (select invoiceid from invoice_sent) and i.datepaid >= '" . START_DATE . "' order by i.datepaid limit 0," . PROCESS_LIMIT);

  sendInvoices($invoices);
}

function sendInvoice($id) {
  global $mysqli;
  if (!$mysqli = connectToMySql()) {
    exit;
  }
   
  $generated = $mysqli->query("SELECT * FROM invoice_sent i WHERE i.invoiceid = " . $id);
  
  if ($generated->num_rows != 0) {
    echo "Invoice already generated!\n";
    exit;
  }

  $invoices = $mysqli->query("SELECT * FROM tblinvoices i WHERE i.id = " . $id);
  sendInvoices($invoices);
}

function checkCompanyName($name) {
  // A rövidítések előtt szándékosan van SPACE!
  $companyFormats = array(' kft', ' bt', ' zrt', ' nyrt', ' ev', ' e.v.', 'egyesület', 'mozgalom', 'Önkormányzat', 'iskola', ' khe', 'intézet', 'Ügyvédi Iroda', 'szövetség', 'alapítvány', 'Óvoda', 'ügyvéd', 'szakszervezet', 'szövetkezet', 'Football Club', 'egyéni vállalkozó', 'plébánia', ' szervezet', 'klebelsberg', 'alapítvány', 'polgárőrség', 'közösség', "kamara", "református", "klub", "club", "gyülekezet", "konzulátus", "társaság", " ec", "egyéni cég", "szolgáltató központ", " kkt", "közkereseti társaság", "ifjúsági otthon", " kik", " se.", "polgármesteri hivatal", "egyetem", " kha", "kereskedelmi képviselet", "gimnázium", "kollégium", "Lelkigyakorlatos Ház", "Nemzetőrség", "csapat", "felügyelőség", "oktatási központ", "színház", "KLIK ", "könyvtár", "múzeum", "község");
  
  for ($i = 0; $i<count($companyFormats); $i++) {
    if (stripos($name, $companyFormats[$i]) !== false) {
      return true;
    }
  }
  return false;
}



