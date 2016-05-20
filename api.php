<?php
/**
 * Számlahegy.hu API calls object
 *
 * @author Péter Képes
 * @version V1.0
 * @copyright Számlahegy.hu, 27 September, 2012
 **/

require_once('classes.php');

if (!defined('DEBUG')) {
  define('SERVER_URL', 'https://ugyfel.szamlahegy.hu/api/v1/invoices');
} else {
  define('SERVER_URL', DEBUG . '/api/v1/invoices');
}

class SzamlahegyApi {
  private $ch;
  private $server_url;

  /**
   * opens a HTTP connection to Szamlahegy server.
   * All commands send with one conection for performance reason
   *
   * @return void
   * @author Péter Képes
   **/
  function openHTTPConnection($server_url = SERVER_URL) {
    // Init connection
    $this->ch = curl_init();
    $this->server_url = $server_url;
    curl_setopt($this->ch,CURLOPT_URL,$this->server_url);
    curl_setopt($this->ch,CURLOPT_POST,true);
    curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json','Accept: application/json'));
  }

  /**
   * closes oened HTTP connection
   *
   * @return void
   * @author Péter Képes
   **/
  function closeHTTPConnection() {
    //close connection
    curl_close($this->ch);
  }

  /**
   * send new invoice to szamlahegy
   * All commands send with one conection for performance reason
   *
   * invoice object from classes.php
   * @return true if sucesed false if something bad happend
   * @author Péter Képes
   **/
  function sendNewInvoice($invoice, $api_key = API_KEY) {
    $atmp = array();
    $atmp['api_key'] = $api_key;
    $atmp['invoice'] = $invoice;

    curl_setopt($this->ch,CURLOPT_POSTFIELDS,json_encode($atmp));

    //execute post
    $result = curl_exec($this->ch);
    $info = curl_getinfo($this->ch);

    $response = array();
    $response['result'] = $result;
    $response['curl_info'] = $info;
    $response['curl_error'] = curl_error($this->ch);

    $error_text = "Hiba a számla generálása közben! #" . $invoice->foreign_id . "\n";

    if ($response['result'] === false) {
      $response['error'] = true;
      $response['error_code'] = 101;
      $response['error_text'] = $error_text . "Curl error: " .
        $response['curl_error'] . "\n" .
        "Server url: " . $this->server_url  . "\n";

    } elseif (is_null($response['result']) ||
            $response['result'] === "" ||
            $response['curl_info']['http_code'] != 201) {
      $response['error'] = true;

      if ( $response['result'] == '{"foreign_id":["must be unique for issuer"]}') {
        $response['error_code'] = 103;
        $response['error_text'] = $error_text .
          "A számlát nem küldjük újra, mert már szerepel a Számlahegyen!";
      } else {
        $response['error_code'] = 102;
        $response['error_text'] = $error_text .
          "Server url: " . $this->server_url  . "\n" .
          "HTTP response code: " . $response['curl_info']['http_code'] . "\n" .
          $response['result'] . "\n";
      }
    } else {
      $response['error'] = false;
      $response['error_code'] = null;
    }

    return $response;
  }
}
