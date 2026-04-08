<?php
// Mivel a MVC-dben a router valószínűleg behúzza a config.php-t, 
// a konstansok (DB_HOST, stb.) itt már élni fognak.

try {
    // A DSN (Data Source Name) összeállítása
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // A PDO extra beállításai a maximális biztonságért és kényelemért
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ha hiba van, dobjon kivételt
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // A lekérdezések alapból asszociatív tömbbel (névvel) térjenek vissza
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Valódi prepared statements (Hacker-védelem ON!)
    ];

    // Létrehozzuk a PDO kapcsolatot (Ezt a $pdo változót használjuk majd mindenhol)
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Teszteléshez kiveheted a kommentet:
    // echo "Sikeresen rácsatlakoztunk a Troxan szerverére! 🚀";

} catch (PDOException $e) {
    // Ha valami gebasz van (nem fut a XAMPP, elírtad a nevet), itt megáll a kód és kiírja a hibát
    die("Server error (Database connection failed): " . $e->getMessage());
}
?>