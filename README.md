# szamlahegy-api

A [Számlahegy online számlázó](https://szamlahegy.hu) program API könyvtára. Segítségével egyszerűen
létrehozhatók számlák bármely PHP programból. (A számla készítéshez Számlahegy regisztráció szükséges!)

## Funkciók

* Hiteles elektronikius számla létrehozása
* [Elektronikus számla](https://szamlahegy.hu) küldése e-mail-ben
* Nyomtatott számla készítése
* Díjbekérő készítése
* Teszt üzemmód, teszt számlák készítése
* Hibák kezelése és visszaadása

## Fájlok

* `classes.php`: tartlmazza azokat az osztályokat, amik szükségesek a számla készítéshez
* `api.php`: A Számlahegy API könyvtár

## Számla létrehozása

A számla egyszerűen létrehozható PHP programból

```
$api = new SzamlahegyApi();
$api->openHTTPConnection();
$response = $api->sendNewInvoice($invoice, 'api-key 1234-1234-1234');
$api->closeHTTPConnection();
```

Hibátlan futás esetén a `$response['error']` értéke `false`.

### Számla készítés hibák

Az API hibás futása esetén a `$response['error']` értéke `true`. Beállításra kerül még a
`$response['error_code']` és `$response['error_text']`.

Hibakódok:
* 101: Hálózati hiba történt, nem elérhető a beállított Számlahegy szerver.
* 102: A szerver nem rögzítette a számlát, hibát adott vissza.
* 103: A szerver nem rögzítette a számlát mert azz már korábban beküldtük. (foreign_id ellenőrzés)
* 201-206: JSON konverziós hiba

## Verziószám

Például: V3.0.1

* 3: fő Számlahegy verzió
* 0: al verzió, a Számlahegyben új funkciók változásakor vagy API változáskor változhat
* 1: Számlahegy PHP API verzió, az adott (3.0) Számlahegy verzióhoz. Akkor változik, ha ez a PHP API változik
