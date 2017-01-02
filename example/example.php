<?php
/**
 * Számlahegy.hu API example
 *
 * @author Péter Képes
 * @version V1.0
 * @copyright Számlahegy.hu, 2017
 **/

require_once(__DIR__ . '/../api.php');

$api_key = $argv[1];

$invoice = new Invoice();
$invoice->customer_name = 'Példaprogram teszt vevő';
$invoice->customer_detail = 'Ide lehet bármit írni, pl. bankszámlát';
$invoice->customer_city = 'Budapest';
$invoice->customer_address = 'Keveháza u. 1-3.';
$invoice->customer_zip = '1115';
$invoice->customer_country = 'HU';
$invoice->customer_vatnr = '12345678-1-12';
$invoice->customer_contact_name = 'Mici Mackó';
$invoice->customer_email = 'mailto@example.com';
$invoice->payment_method = 'B';
$invoice->payment_date = '2017.01.01';
$invoice->perform_date = '2017.01.01';
$invoice->header = 'Szabad szöveges fejléc mező, nem kötelező';
$invoice->footer = 'Szabad szöveges lábléc mező, nem kötelező';
$invoice->kind = 'T';
$invoice->tag = 'example';
$invoice->paid_at = '2017.01.01';
$invoice->foreign_id = '123457';
$invoice->signed = 'N';
$invoice->currency = 'HUF';

$invoice_row_1 = new InvoiceRow();
$invoice_row_1->productnr = 'SKU0001';
$invoice_row_1->name = 'Teszt termék 1';
$invoice_row_1->detail = 'Teszt 1 leírása';
$invoice_row_1->quantity = '2';
$invoice_row_1->quantity_type = 'db';
$invoice_row_1->price_slab = '1200';
$invoice_row_1->tax = '27';
$invoice_row_1->brutto_priority = 'N';
$invoice_row_1->foreign_id = '1234';

$invoice_row_2 = new InvoiceRow();
$invoice_row_2->productnr = 'SKU0002';
$invoice_row_2->name = 'Teszt termék 2';
$invoice_row_2->detail = 'Teszt 2 leírása';
$invoice_row_2->quantity = '3';
$invoice_row_2->quantity_type = 'óra';
$invoice_row_2->price_slab = '2300';
$invoice_row_2->tax = '18';
$invoice_row_2->brutto_priority = 'N';
$invoice_row_2->foreign_id = '1235';

$invoice_rows = array();
$invoice_rows[] = $invoice_row_1;
$invoice_rows[] = $invoice_row_2;

$invoice->invoice_rows_attributes = $invoice_rows;

$api = new SzamlahegyApi();
$api->openHTTPConnection();
$response = $api->sendNewInvoice($invoice, $api_key);
$api->closeHTTPConnection();

var_dump($response);
