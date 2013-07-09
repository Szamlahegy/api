<?php
/**
 * Számlahegy.hu API calls object
 *
 * @author Péter Képes
 * @version V1.0
 * @copyright Számlahegy.hu, 27 September, 2012
 **/

require_once('classes.php');

define('SERVER_URL', 'http://ugyfel.szamlahegy.hu/api/create');
#define('SERVER_URL', 'http://localhost:3000/api/create');
define('API_KEY', 'aaaaa-bbbbb-ccccc-ddddd');

class SzamlahegyApi {
  private $ch; 
  
  /**
   * opens a HTTP connection to Szamlahegy server. 
   * All commands send with one conection for performance reason
   * 
   * @return void
   * @author Péter Képes
   **/
  function openHTTPConnection() {
    // Init connection
    $this->ch = curl_init();  
    curl_setopt($this->ch,CURLOPT_URL,SERVER_URL);
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
  function sendNewInvoice($invoice) {
    $atmp = array();
    $atmp['api_key'] = API_KEY;
    $atmp['invoice'] = $invoice;

    curl_setopt($this->ch,CURLOPT_POSTFIELDS,json_encode($atmp));

    //execute post
    $result = curl_exec($this->ch);
    $info = curl_getinfo($this->ch);

    if($result === false || is_null($result) || $result === "" || $info['http_code'] != 201) {
        echo 'HTTP response code: ' . $info['http_code'] . "\n";
        echo $result . "\n";
        echo 'Curl error: ' . curl_error($this->ch) . "\n";
        return false;
    }
    else {
        return true;
    }
  }
}