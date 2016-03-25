<?php
/**
 * Számlahegy.hu API objects
 *
 * @author Péter Képes
 * @version V1.0
 * @copyright Számlahegy.hu, 27 September, 2012
 **/

class Invoice {
  public $customer_name;      // Vevő neve
  public $customer_detail;    // Vevő egyéb adata, pl. bankszámla
  public $customer_city;      // Vevő városa
  public $customer_address;   // Vevő címe
  public $customer_country;   // Vevő országa (pl.: HU)
  public $customer_vatnr;     // Vevő adószáma (Ha az ország HU akkor a formátum ellenőrzött)
  public $payment_method;     // Fizetés módja (B: utalás, C: készpénz)
  public $payment_date;       // Fizetésu határidő
  public $perform_date;       // Teljesítési dátum
  public $header;             // Számla felső részén szabad szöveges rész
  public $footer;             // Számla alsó részén szabad szöveges rész
  public $customer_zip;       // Vevő irányítószáma
  public $kind;               // Számla típusa (N: normal, S: sztorno, D: díjbekérő, T: Teszt)
  public $tag;                // Tetszőleges szöveges mező, keresni lehet rá a felületn
  public $paid_at;            // Fizetés dátuma (ha ki van töltve, akkor a számlán megjelenik a 'Fizetve' felirat)
  public $customer_email;     // Vevő mail címe, ahova az elektronikus számlát küldeni kell
  public $foreign_id;                 // Szabad szöveges azonosító. Egyedi kell legyen, különben hibás a számla (biztonsági figyelés, nehogy kétszer küldjünk egy számlát)
  public $signed;                     // Elektronikus aláírás? Y/N
  public $customer_contact_name;      // Vevő kapcsolattartó neve, e-mail címzéshez
  public $invoice_rows_attributes;    // A számla sorai (array). Nem lehet üres, minimum 1 sor kell!
}

class InvoiceRow {
  public $productnr;          // SZJ vagy vámtarifa szám
  public $name;               // Termék neve
  public $detail;             // Termék részletes leírása
  public $quantity;           // Mennyiség
  public $quantity_type;      // Mennyiségi egység
  public $price_slab;         // Egységár
  public $tax;                // Adókulcs, például 27
  public $brutto_priority;    // Brutto vagy netto alapján megy a kalkuláció?
}
