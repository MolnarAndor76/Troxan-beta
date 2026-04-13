---
title: "Troxan – Webalkalmazás dokumentáció"
author: "[TANULÓ NEVE]"
date: "2024/2025"
lang: hu
geometry: "margin=2.5cm"
fontsize: 12pt
linestretch: 1.5
toc: true
toc-depth: 3
numbersections: true
---

\newpage

# Fedőlap

**Projekt megnevezése:** Troxan – Játékkísérő webalkalmazás

**Tanuló neve:** [TANULÓ NEVE]

**Iskola neve:** [ISKOLA NEVE]

**Osztály:** [OSZTÁLY]

**Tanév:** 2024/2025

**Konzulens:** [KONZULENS NEVE]

**Leadás dátuma:** [DÁTUM]

\newpage

# Bevezetés

A mai digitális világban szinte minden komolyabb számítógépes játékhoz tartozik valamilyen kísérő webalkalmazás, amelyen a játékosok megtekinthetik a játék legfrissebb hírei, letölthetik a szükséges fájlokat, összehasonlíthatják egymással a teljesítményüket, és közösséget alkothatnak egymás körül. Gondoljunk csak olyan ismert példákra, mint a Blizzard Battle.net portálja vagy a Steam közösségi oldalai – ezek az alkalmazások nem csupán a játék meghosszabbításaként funkcionálnak, hanem önálló, értékes platformokká válnak, amelyek önmagukban is izgalmas fejlesztési kihívásokat rejtenek.

Jelen dokumentáció a **Troxan** nevű webalkalmazás fejlesztési folyamatát, architektúráját, funkcióit és technológiai hátterét mutatja be. A Troxan egy egyedi, Windows platformra fejlesztett, C# programozási nyelven megírt akciójáték kísérőoldala, amely összetett, valós idejű kapcsolatban áll magával a játékkal: a játékosok bejelentkezhetnek a weboldalon, feltölthetik vagy letölthetik a játékhoz készült pályákat, megtekinthetik a ranglistát és a saját statisztikáikat, valamint az adminisztrátori eszközök segítségével kezelhetik a közösséget.

A fejlesztés során szándékosan kerültem a nagyobb keretrendszerek alkalmazását: sem a backenden (PHP), sem a frontenden (JavaScript) nem használtam olyan könyvtárat, mint a Laravel vagy a React. Ez a döntés elsősorban azért született, mert sokkal mélyebb ismereteket nyújt az alaptechnológiákról, ha az ember saját maga rak össze egy teljes MVC architektúrát, saját routert, saját session-kezelést, és saját DOM-manipulációs logikát. Természetesen ez a megközelítés nehezebb és időigényesebb úton jár, de a végeredmény egyértelműen bizonyítja, hogy az alapok mély ismerete nélkülözhetetlen minden webfejlesztő számára.

A dokumentáció logikus sorrendben halad végig a projekt lényeges aspektusain: a technológiai döntések indoklásától kezdve az architektúra ismertetésén, a főbb funkciók részletes bemutatásán és a biztonsági megfontolások leírásán át egészen a tesztelés tapasztalataiig és a jövőbeli fejlesztési lehetőségekig.

\newpage

# A projekt bemutatása

## A játékról

A **Troxan** egy Windows platformra fejlesztett, C# programozási nyelven készült akciójáték, amelynek cselekménye egy misztikus, középkori fantáziavilágban játszódik. A Troxan nevű királyság évszázadokon át a béke és a jólét szimbóluma volt, ahol az emberek harmóniában éltek a természettel és egymással. Ez az aranykor azonban hirtelen véget ért, amikor egy ismeretlen, gyorsan mutálódó vírus kezdte el terjedni a királyság területén: a járvány elemésztette az erdőket, elnémította az utcákat, és az egykor boldog lakókat agresszív, üres lényekké változtatta.

A játékos egyedüli hősként lép be ebbe a sötét világba: a feladat az, hogy behatoljon a fertőzött területekre, felszámolja a vírus forrását, és megmentse a királyságot a teljes pusztulástól. A játékmenet során a felhasználó különféle pályákon halad végig, amelyeket a fejlesztők és maga a közösség is létrehozhat.

## A webalkalmazásról

A Troxan webalkalmazás a játék "command centerje": mindazt az infrastruktúrát biztosítja, ami a játékhoz kapcsolódó közösségi és adminisztrációs funkciókhoz szükséges. A weboldal és a játék között közvetlen, token-alapú API kommunikáció zajlik: amikor egy játékos belép a játékba, a kliens a webalkalmazás REST API végpontján hitelesíti magát, majd a játékmenet végén a szerzett statisztikákat szintén az API-n keresztül juttatja el a szerverre, ahol azok tárolásra kerülnek és azonnal megjelennek a ranglistán.

A weboldal tehát nem csupán statikus bemutatkozó oldal, hanem valódi, élő háttérrendszer, amely szervesen összekapcsolódik a játékélménnyel.

## Célközönség

Az alkalmazás célközönsége kettős:

- **Játékosok:** akik le szeretnék tölteni a játékot, böngészni szeretnék az elérhető pályákat, nyomon szeretnék követni saját fejlődésüket és összehasonlítani magukat más játékosokkal a ranglistán.
- **Adminisztrátori csapat:** akik kezelik a felhasználókat, közzéteszik a frissítési közleményeket (patch notes), moderálják a pályák könyvtárát, és szükség esetén kitiltják a szabályszegő játékosokat.

\newpage

# Felhasznált technológiák

## PHP 8

A backend réteg PHP 8 programozási nyelven íródott, keretrendszer használata nélkül. A PHP mind a mai napig az egyik legelterjedtebb szerveroldali webes nyelv: megbízható, jól dokumentált, és széles körben elérhető szinte minden tárhelyszolgáltatónál. A keretrendszer nélküli megközelítés lehetővé tette, hogy az alkalmazás struktúrája pontosan azokat az elveket kövesse, amelyek az adott projekthez a leginkább illeszkednek, anélkül hogy egy kész keretrendszer által erőltetett konvenciók korlátoznák a fejlesztési szabadságot.

A PHP backend feladata:

- REST API végpontok kiszolgálása
- Session alapú felhasználóhitelesítés kezelése
- Az adatbázissal való kommunikáció (PDO)
- HTML nézetek szerver oldali renderelése
- E-mail küldés (PHPMailer)

## MySQL 8

Az adatbázis réteg MySQL 8.0 relációs adatbázis-kezelő rendszeren alapul. A MySQL robusztus, megbízható és rendkívül jól optimalizálható megoldás, amely messzemenően megfelel a projekt igényeinek. Az adatbázis utasítások kizárólag PDO (PHP Data Objects) rétegen keresztül futnak, amely egységes, biztonságos interfészt biztosít az adatbázis-műveletekhez. A PDO prepared statements használatával az összes felhasználói bemenet automatikusan paraméterként kerül át a lekérdezésbe, ami teljes körű védelmet nyújt az SQL injection típusú támadásokkal szemben.

## JavaScript (Vanilla)

A frontend logika kizárólag natív, "vanilla" JavaScript segítségével valósult meg, React, Vue vagy Angular keretrendszer alkalmazása nélkül. Ez a döntés tudatos: a natív DOM API, az eseménykezelés, a fetch API és a modern JavaScript (ES6+) lehetőségei tökéletesen elegendőek a projekt igényeinek kielégítéséhez. A keretrendszer nélküli fejlesztés sokkal mélyebb megértést igényel és nyújt a böngésző működéséről, ami hosszú távon értékesebb tudás.

A JavaScript felelős:

- Az oldalak közötti navigáció kezeléséért
- Az API kérések küldéséért és a válaszok feldolgozásáért
- A dinamikus DOM tartalom frissítéséért
- A modal ablakok és interaktív komponensek kezeléséért
- A felhasználói munkamenet állapotának localStorage-ban való tárolásáért

## Vite 7

A frontend build eszköz a **Vite 7**, amely az egyik legmodernebb és leggyorsabb JavaScript bundler és fejlesztői szerver. A Vite lehetővé tette, hogy a JavaScript forráskódot modulárisan, fájlonként megírjuk (külön modul az admin, a profil, a térképek stb. funkciókhoz), majd a build folyamat során ezeket egyetlen optimalizált csomagba fordítsa. Ezáltal a produkciós környezetben az oldal betöltési ideje minimálisra csökken, miközben a fejlesztés során a Hot Module Replacement gyors fejlesztési élményt biztosít.

## Tailwind CSS v4

A stíluslapok Tailwind CSS v4 utility-first CSS keretrendszer segítségével íródtak. A Tailwind megközelítése gyökeresen eltér a hagyományos CSS keretrendszerektől (mint például a Bootstrap): ahelyett, hogy előre megírt komponenseket kínálna, az osztályok szintjén adja meg az összes szükséges stílusutasítást közvetlenül a HTML elemeken. Ez nagyfokú rugalmasságot biztosít, és lehetővé teszi egy teljesen egyedi, a játék vizuális stílusához igazodó design megvalósítását.

## PHPMailer

E-mail küldési funkcióhoz a **PHPMailer** könyvtárat alkalmaztam. A PHPMailer az egyik legelterjedtebb PHP email-könyvtár, amely lehetővé teszi HTML formátumú e-mailek küldését SMTP kapcsolaton keresztül. A projektben az e-mail funkcionalitás két helyen kerül felhasználásra: az e-mail alapú regisztrációs megerősítő kód elküldésekor, valamint az elfelejtett jelszó visszaállítási folyamatban.

\newpage

# Fejlesztői dokumentáció – Architektúra és működés

## MVC minta

Az alkalmazás backend oldala a klasszikus **Model-View-Controller (MVC)** tervezési mintán alapul, amelyet saját implementációval valósítottam meg, keretrendszer nélkül. Az MVC minta három jól elkülönülő rétegre osztja az alkalmazás logikáját:

- **Model (modell):** Az adatbázis-struktúrát és az üzleti logikát reprezentálja. Jelen projektben a modellek szerepét nagyrészt a kontrollerek töltik be, közvetlenül a PDO lekérdezések formájában.
- **View (nézet):** A PHP fájlok, amelyek a HTML struktúrát tartalmazzák. Ezek nem kerülnek közvetlenül a böngészőhöz, hanem a szerver puffereli ezeket, majd JSON válaszként küldi el a frontendnek dinamikus betöltés céljából.
- **Controller (kontroller):** Az egyes URL végpontokhoz tartozó PHP fájlok, amelyek feldolgozzák a bejövő kéréseket, elvégzik az adatbázis-lekérdezéseket, és összeállítják a választ.

## Routing (útvonalválasztás)

A router az alkalmazás belépési pontja. Az Apache `.htaccess` konfigurációja minden beérkező HTTP kérést az `app/api.php` fájlra irányít, ahol a megadott URL szegmensek alapján (`/api/maps`, `/api/profile`, stb.) a router betölti a megfelelő PHP kontrollert. Ez az egyszerű, de hatékony rendszer lehetővé teszi, hogy áttekinthető, RESTful URL struktúrát valósítsunk meg anélkül, hogy komplex keretrendszeri routert kelljen alkalmaznunk.

```
GET  /maps         → mapsController.php → getContent()
POST /maps         → mapsController.php → handlePost()
GET  /profile      → profileController.php → getContent()
POST /game_login   → gameLoginController.php → handleGameLogin()
POST /game_update_stats → gameUpdateStatsController.php → handleGameUpdateStats()
```

## Frontend architektúra és oldalnavigáció

A frontend architektúra egy egyedi, SPA-szerű (Single Page Application) megközelítést alkalmaz. Az oldal egyetlen HTML héjból áll, amelyet a Vite build generál. Amikor a felhasználó navigál (például a "Maps" menüpontra kattint), a JavaScript egy AJAX kérést küld a PHP API megfelelő végpontjára. A PHP kontroller lerendeli a HTML nézetet, puffereli, majd JSON válasz formájában visszaküldi. A JavaScript ezután ezt a HTML tartalmat injektálja a megfelelő DOM elembe, így az oldal újratöltése nélkül frissül a tartalom.

Ez a megközelítés ötvözi a hagyományos szerver oldali renderelés előnyeit (a HTML a szerveren generálódik, ahol az adatbázis elérhető) a modern SPAs gyors, oldaláltás nélküli navigációjával.

## Session-kezelés

A felhasználói munkamenetek PHP natív session mechanizmusával kerülnek kezelésre, amelynek élettartama 7200 másodpercre (2 óra) van beállítva. Bejelentkezéskor a szerver egy `web_session_token` értéket generál, amelyet egyrészt a szerveroldali PHP sessionban, másrészt az `Active_Web_Sessions` adatbázistáblában tárol el. Kijelentkezéskor ez a bejegyzés törlésre kerül, így párhuzamos token-alapú érvénytelenítés is lehetséges.

A frontend oldali munkamenet-adatok (felhasználónév, bejelentkezési állapot, avatar) a böngésző `localStorage`-ában élnek, és minden oldalbetöltéskor szinkronizálódnak a szerveroldali PHP sessionnal.

## A játék-weboldal kommunikáció

A webalkalmazás és a C# játékkliens között egy kétlépéses tokenel hitelesített API-kommunikáció zajlik. Az első lépésben a játék POST kéréssel küldi el a felhasználónevet és jelszót a `/api/game_login` végpontra, ahol a szerver ellenőrzi az adatokat, generál egy egyedi, 64-karateres hexadecimális tokent, és visszaküldi a kliensnek. A második lépésben, a játékmenet befejezésekor, a játékkliens a megszerzett tokennel hitelesített POST kéréssel elküldi a játékos statisztikáit (szerzett pontszám, elpusztított ellenfelek száma, eltelt játékidő stb.) a `/api/game_update_stats` végpontra, ahol az adatok a szerver által validálva és tárolva kerülnek.

\newpage

# Felhasználói dokumentáció

## Főoldal

A főoldal az alkalmazás első benyomása, ezért különösen nagy hangsúlyt kapott a vizuális megjelenés és az informatív tartalom egyensúlya. Az oldal látogatója – legyen akár regisztrált felhasználó vagy ismeretlen vendég – azonnal megkapja a játékhoz szükséges legfontosabb információkat és élményeket.

A főoldal több jól elkülönülő szekciót tartalmaz:

**Trailer szekció:** Az oldal tetején egy beágyazott YouTube-videó automatikusan lejátszódik, és vizuálisan bevezeti a látogatót a Troxan világába. A trailer URL az adminisztrációs panelen keresztül szerkeszthető, így egy esetleges új előzetes megjelenésekor nincs szükség kódbeli változtatásra.

**Letöltés szekció:** Egy jól látható, kiemelkedő gomb vezeti a látogatót a játék telepítőfájljának letöltéséhez. A letöltési link szintén admin panelen kezelhető.

**Lore szekció:** A játék narratív világát bemutató, hosszabb szöveges tartalom, amely a Troxan királyságának történetét és a küldetés hátterét részletezi. Ez a tartalom is szerkeszthető az admin panelről, így a fejlesztők folyamatosan bővíthetik, finomíthatik a játék háttértörténetét.

**Rendszerkövetelmények szekció:** Táblázatos formában jelenik meg a játék futtatásához szükséges minimális és ajánlott hardveres konfiguráció.

**Patch notes (frissítési közlemények):** A főoldal alján jelennek meg a legutóbbi játékfrissítések közleményei, fordított időrendi sorrendben. Minden patch note tartalmaz egy dátumot, egy tartalomleírást, és az adminok számára szerkesztési és törlési lehetőségeket.

**About Us szekció:** Rövid bemutatkozás a fejlesztőcsapatról, amely szintén az admin panelről szerkeszthető.

*[KÉP HELYE – Főoldal általános nézet]*

*[KÉP HELYE – Patch Notes szekció]*

## Regisztráció

A regisztrációs folyamat átgondolt, többlépéses mechanizmust alkalmaz az e-mail cím valódiságának biztosítása érdekében. Az első lépésben a felhasználó megadja a kívánt felhasználónevét, e-mail-címét, jelszavát és jelszavának megerősítését. A rendszer azonnal, még a szerverre való elküldés előtt a JavaScript segítségével ellenőrzi, hogy:

- a felhasználónév legalább 3 és legfeljebb 30 karakter hosszú legyen,
- az e-mail cím szintaxisa érvényes legyen,
- a jelszó elérje az elvárt minimális biztonságot (legalább 8 karakter, kisbetű, nagybetű és szám),
- a jelszó és a megerősítés egyezzen,
- a felhasználónév ne tartalmazzon tiltott karaktereket.

A szerver oldal elvégzi ezeket az ellenőrzéseket szintén, majd a sikeres validáció után a rendszer egy 6 jegyű numerikus megerősítőkódot generál, és azt a megadott e-mail-címre elküldi a PHPMailer segítségével. A fiók addig nem aktiválódik, amíg a felhasználó vissza nem jelzi a kódot a weboldal felé. A megerősítőkód 15 percig érvényes, utána a regisztrációt meg kell ismételni.

*[KÉP HELYE – Regisztrációs űrlap]*

*[KÉP HELYE – E-mail megerősítő kód bekérő képernyő]*

## Bejelentkezés

A bejelentkezési oldal klasszikus e-mail + jelszó kombinációt alkalmaz. A rendszer az ellenőrzés során nemcsak azt vizsgálja, hogy a megadott adatok egyeznek-e az adatbázisban tároltakkal, hanem azt is ellenőrzi, hogy:

- a fiók e-mailként meg van-e erősítve (nem megerősített fiók esetén tájékoztató hibüzenet jelenik meg, és lehetőség van újra elküldeni a megerősítőkódot),
- a felhasználó nincs-e kitiltva (bannolt felhasználó esetén a weboldal az "isBanned" oldalra irányít),
- a fiókhoz nincs-e ideiglenes jelszó rendelve (admin által visszaállított jelszó esetén a rendszer kötelező jelszóváltást kér a belépéskor).

Sikeres bejelentkezés után a szerver létrehozza a PHP sessiont, generál egy egyedi web session tokent, és azt az `Active_Web_Sessions` adatbázistáblában rögzíti. A felhasználói információk (felhasználónév, avatar) a böngésző `localStorage`-ába is kerülnek, lehetővé téve a fejléc azonnali frissítését.

**Elfelejtett jelszó folyamat:** Ha a felhasználó elfelejtette jelszavát, megadhatja az e-mail-címét, mire a rendszer ideiglenes jelszót generál, és azt e-mailben elküldi. Az első bejelentkezéskor a rendszer kötelező jelszóváltást kér el.

*[KÉP HELYE – Bejelentkezési oldal]*

*[KÉP HELYE – Elfelejtett jelszó modal]*

## Profil oldal

A profil oldal a bejelentkezett felhasználó személyes "irányítópultja", ahol áttekintheti saját adatait és elvégezheti a fiókjával kapcsolatos módosításokat.

**Megjelenített adatok:**

- Felhasználónév és annak utolsó módosítási dátuma
- E-mail-cím
- Regisztráció dátuma
- Utolsó online idő
- Ranglistán elfoglalt aktuális helyezés
- Számlányit jelző szerep (Player, Moderator, Admin, Engineer)
- Aktuális avatar

**Felhasználónév csere:** A felhasználónév módosítása egy megerősítő modál ablakon keresztül történik, és az arra vonatkozó cooldown mechanizmus megakadályozza a túl sűrű névváltoztatást. A rendszer naplózza az utolsó felhasználónév-csere időpontját.

**Jelszóváltás:** A jelszó módosításához meg kell adni a régi jelszót is, amelyet a szerver ellenőriz, mielőtt az újat elmentené.

**Avatar csere:** Egy modál ablakon belül a felhasználó az elérhető avatarok közül választhat egyet magának. Az avatarok a szerveren, az adatbázisban kerülnek tárolásra BLOB formátumban, és base64 kódolással kerülnek a frontendre.

**Játékstatisztikák:** A profil oldalon megjelennek a felhasználóhoz tartozó, a játékból feltöltött legfrissebb statisztikák: az összesített pontszám, az elpusztított ellenfelek száma és az összes lejátszott idő.

*[KÉP HELYE – Profil oldal]*

*[KÉP HELYE – Avatar csere modal]*

*[KÉP HELYE – Felhasználónév csere modal]*

## Térképek böngészője

A térképek oldal a közösség által feltöltött játékpályák katalógusa. Minden aktív (publikált) pálya elérhető itt, és a felhasználók szabad keresést, rendezést végezhetnek.

**Megjelenített adatok pályánként:**

- Pálya neve
- Létrehozó neve
- Letöltések száma
- Feltöltés dátuma
- Pálya borítóképe
- "Hozzáadás a könyvtárhoz" gomb

**Keresés és szűrés:** A keresőmező segítségével a felhasználók a pálya neve vagy a készítő neve szerint szűrhetnek. A rendezési opciók lehetővé teszik a pályák sorba rendezését letöltésszám, ABC sorrend, legfrissebb vagy legrégebbi szerint.

**Könyvtárba mentés:** A bejelentkezett felhasználók egy gombnyomással hozzáadhatják vagy eltávolíthatják a pályákat a saját könyvtárukból. A könyvtárba mentett pályák a "Saját Térképek" oldalon lesznek elérhetők.

**Moderátori nézet:** Az Admin, Moderátor és Engineer szerepkörű felhasználók külön "szemeteskuka" szekciót látnak, ahol a törölt, visszavont vagy kifogásolt pályák jelennek meg moderálási céllal.

*[KÉP HELYE – Térképek oldal – általános nézet]*

*[KÉP HELYE – Keresési és szűrési funkciók]*

## Saját Térképek

A "Saját Térképek" oldal a bejelentkezett felhasználó személyes pályagyűjteményét tartalmazza. Az itt megjelenő pályák két forrásból érkezhetnek:

1. **Saját készítésű pályák:** amelyeket maga a felhasználó töltött fel, és amelyek vázlat (draft), közzétett vagy visszavont állapotban vannak.
2. **Könyvtárba mentett pályák:** amelyeket a térképek oldalon a felhasználó hozzáadott a saját könyvtárához.

Az oldal lehetővé teszi a pályák eltávolítását a személyes könyvtárból, keresést és rendezést (ABC sorrend, legújabban hozzáadott, vagy legrégebben hozzáadott szerint).

Egy fontos részlet: ha egy pálya készítője törli a pályáját, az még akkor is megmarad azoknak a felhasználóknak a könyvtárában, akik korábban elmentették (státusz 5 = készítő által törölt, de könyvtárban megmarad).

*[KÉP HELYE – Saját Térképek oldal]*

## Ranglista

A ranglista oldal mutatja a játékban legjobban teljesítő felhasználókat. A toplista a nem kitiltott felhasználók statisztikái alapján épül fel, és a játékosok a szerzett pontszámuk alapján kerülnek rangsorolásra.

**Megjelenített tartalom:**

- Top 10 legjobb játékos neve, pontszáma és helyezése
- A bejelentkezett felhasználó saját helyezése és pontszáma – még akkor is, ha nem kerül be a top 10-be – egy külön, kiemelt sorban az oldal alján jelenik meg
- A ranglista legutóbb frissítésének időpontja

*[KÉP HELYE – Ranglista oldal]*

## Statisztikák

A statisztikák oldal a játék általános teljesítményadatait jeleníti meg. Az oldal tartalmát a PHP backend szerver oldalon rendeli le, tehát minden adatfrissítés azonnal tükröződik a megjelenítésben.

*[KÉP HELYE – Statisztikák oldal]*

## Adminisztrációs panel

Az adminisztrációs panel az alkalmazás legösszetettebb és funkcionálisan leggazdagabb oldala. Csak Admin és Engineer szerepkörű felhasználók férhetnek hozzá. Ha egy alacsonyabb szerepkörű felhasználó próbálna belépni, 403 Unauthorized hibaüzenetet kap.

### Felhasználókezelés

Az admin panel fő nézete egy kereshető felhasználótáblázatot jelenít meg, amelybe az összes regisztrált felhasználó belekerül. Minden felhasználósornál látható:

- Felhasználónév és profilkép
- E-mail-cím
- Regisztráció dátuma
- Utolsó online idő
- Jelenlegi szerepkör
- Kitiltás állapota

**Szerep módosítása:** Az adminok megváltoztathatják a felhasználók szerepkörét (Player, Moderator, Admin közötti szintek közötti módosítás lehetséges). Az Engineer szerepkör kizárólag egyedi, speciális eljárással módosítható.

**Felhasználó kitiltása (ban):** A ban gomb megnyomásakor egy megerősítő modál jelenik meg, amelyben meg kell adni a kitiltás indokát. Kitiltás nélkül nem menthető el a ban. A rendszer automatikusan naplózza a kitiltás okát. Az admin saját magát nem tilthatja ki. Adminokat kizárólag Engineer tud kitiltani.

**Felhasználó tiltásának feloldása (unban):** Bannolt felhasználónál a ban gomb "Unban"-ná változik, és egyetlen kattintással feloldható a tilalom.

**Statisztika megtekintése:** Minden felhasználóhoz megtekinthető annak legfrissebb játékstatisztikája egy kinyíló részletező sávban.

*[KÉP HELYE – Admin panel – felhasználótáblázat]*

*[KÉP HELYE – Admin panel – kitiltás modal]*

### Patch Notes szerkesztő

Az admin panel tartalmaz egy teljes értékű patch notes kezelő felületet is:

- **Új patch note létrehozása:** Egy szövegmező segítségével az admin beküldheti az új frissítési közleményt. Az üzenet azonnal megjelenik a főoldalon.
- **Patch note szerkesztése:** Meglévő bejegyzés szövege módosítható a helyszínen, egy szerkesztőmező segítségével.
- **Patch note törlése:** Egy megerősítő modál ablakban a törlési szándék visszaigazolása után kerül törlésre a bejegyzés.

*[KÉP HELYE – Patch Notes szerkesztő]*

### Weboldal beállítások szerkesztője

Az adminisztrátorok az alábbi weboldal-szintű beállításokat módosíthatják közvetlenül a panelről, kódbeli változtatás nélkül:

- **Trailer URL:** a főoldalon beágyazott videó URL-je
- **Letöltési link:** a játék telepítőjének letöltési URL-je
- **About Us szöveg:** a fejlesztőcsapat bemutatkozása
- **Köszönetnyilvánítások (Special Thanks):** külső közreműködők megemlítése
- **Rendszerkövetelmények:** a minimális és ajánlott hardverkonfiguráció táblázata
- **Lore szöveg:** a játék háttértörténetének szövege

*[KÉP HELYE – Weboldal beállítások szerkesztője]*

## Kitiltott felhasználók oldala (isBanned)

Ha egy kitiltott felhasználó megkísérli elérni az alkalmazás bármelyik oldalát, a rendszer automatikusan az isBanned nézetbe irányítja, ahol tájékoztató üzenet jelenik meg arról, hogy a fiókja le van tiltva. A kitiltott felhasználó a kijelentkezésen kívül semmilyen más műveletet nem végezhet.

Ez az ellenőrzés nem csupán a frontend oldali navigáció szintjén, hanem a PHP router szintjén is megtörténik: minden egyes API kérés beérkezésekor, ha a felhasználó be van lépve, a rendszer azonnal leellenőrzi a kitiltás állapotát az adatbázisból, és szükség esetén felülírja a kért útvonalat az isBanned végpontra.

*[KÉP HELYE – isBanned oldal]*

## Vendég nézet

Ha egy nem bejelentkezett felhasználó próbál meg olyan oldalra navigálni, amely bejelentkezést igényel (profil, saját térképek stb.), a rendszer egy vendég nézetbe irányítja, ahol tájékoztatja a látogatót, hogy az adott tartalom csak regisztrált és bejelentkezett felhasználók számára érhető el, és lehetőséget kínál a bejelentkezésre vagy regisztrációra.

*[KÉP HELYE – Vendég nézet]*

\newpage

# Részletes működési specifikáció

Ez a fejezet az alkalmazás technikai működését lépésről lépésre, kódszintű részletességgel mutatja be. A cél az, hogy ne csak az derüljön ki, mit tud a rendszer, hanem az is, hogyan valósul meg minden fontos folyamat a forráskódban.

## Moduláris frontend felépítés

A frontend belépési pontja a `src/main.js`, amely modulonként importálja az összes oldalspecifikus JavaScript fájlt. Ez azért fontos, mert Vite build után minden modul egyetlen bundle-be kerülhet, így minden globális eseménykezelésnél figyelni kell arra, hogy csak a megfelelő oldalon fusson a logika.

```js
import './admin-src/admin.js';
import './basesite-src/basesite.js';
import './leaderboard-src/leaderboard.js';
import './maps-src/maps.js';
import './myMaps-src/myMaps.js';
import './login-src/login.js';
import './register-src/register.js';
import './profile-src/profile.js';
import './isBanned-src/isBanned.js';
```

Ez a struktúra két dolgot ad egyszerre:

1. Fejlesztéskor moduláris, áttekinthető forrásfájlokkal lehet dolgozni.
2. Éles környezetben optimalizált, összecsomagolt kód fut.

*[KÉP HELYE – main.js modul importok]*

\newpage

## Routing és kérésfeldolgozás teljes útja

Az API működésének központja az útvonal-feldolgozó réteg:

1. A kérés `app/api.php` fájlba érkezik.
2. A router kiolvassa a `path` paramétert.
3. A `route['segment1']` alapján kiválasztja a kontrollert.
4. A kontroller feldolgozza a bemenetet.
5. A rendszer JSON választ küld vissza.

Rövidített router logika:

```php
$path = $_GET['path'] ?? '';
$path = trim($path, '/');
$segments = ($path === '') ? [] : explode('/', $path);

$route = [
	'segment1' => $segments[0] ?? null,
	'segment2' => $segments[1] ?? null,
	'segment3' => $segments[2] ?? null,
];
```

Végpont-választás:

```php
switch ($route['segment1']) {
	case 'main': load_controller(... 'mainController.php'); break;
	case 'maps': load_controller(... 'mapsController.php'); break;
	case 'profile': load_controller(... 'profileController.php'); break;
	case 'game_login': require ...; handleGameLogin(); break;
	case 'game_update_stats': require ...; handleGameUpdateStats(); break;
	default: json_response(['error' => 'API endpoint not found'], 404);
}
```

*[KÉP HELYE – Router működési ábra]*

\newpage

## Játék és weboldal kommunikáció részletesen

Ez a rész különösen kritikus, mert a játékkliens (C#) és a webes backend itt találkozik.

### 1. Bejelentkezés a játékból (`/api/game_login`)

A játék POST kéréssel küldi a `username` és `password` mezőket. A backend:

1. Ellenőrzi a HTTP metódust (csak POST).
2. Lekéri a felhasználót adatbázisból.
3. `password_verify` segítségével ellenőriz.
4. Tiltott felhasználót azonnal elutasít.
5. Generál egy erős tokent: `bin2hex(random_bytes(32))`.
6. Elmenti a tokent a `User.user_token` mezőbe.
7. Visszaküldi a tokent a játéknak.

Példa válasz:

```json
{
	"status": "success",
	"message": "Login successful!",
	"data": {
		"user_id": 12,
		"username": "player01",
		"token": "64karaktereshash..."
	}
}
```

*[KÉP HELYE – Játék login API kérés/válasz]*

### 2. Statisztika lekérés játékból (`/api/game_stats`)

Ha a kliens futás közben adatot kér, `Authorization: Bearer <token>` fejlécet küld. A szerver ellenőrzi a tokent, ellenőrzi a ban állapotot, majd visszaadja a játékoldali adatszerkezethez szükséges mezőket.

*[KÉP HELYE – Bearer token header példa]*

### 3. Statisztika mentés (`/api/game_update_stats`)

Ez a legfontosabb pipeline, mert itt dől el, hogy a ranglista és profil adatok mennyire konzisztensek.

A szerver oldali logika:

1. Csak POST metódus elfogadása.
2. Token olvasása több forrásból (`HTTP_AUTHORIZATION`, `REDIRECT_HTTP_AUTHORIZATION`, Apache headers).
3. Felhasználó-token egyezés ellenőrzése.
4. Opcionális `username` ellenőrzés (anti-cheat).
5. Bejövő statisztika és előző snapshot összehasonlítása.
6. Delta számolása reset-biztosan.
7. Új összesített stat beírása a `Statistics` táblába.
8. `User` tábla `coins`, `level`, `last_time_online` frissítése.

*[KÉP HELYE – game_update_stats folyamatábra]*

\newpage

## Pontszámítás és statisztika-aggregáció teljes magyarázata

### Miért kell delta alapú logika?

Ha a játék csak abszolút számokat küld (például `Mobs killed = 45`), akkor a szerver nem tudná eldönteni, hogy:

1. ez az összesített érték,
2. vagy csak az aktuális session száma.

Ezért a backend snapshot-delta elvet használ.

### Kulcsfüggvények

```php
function troxan_get_stat_score($stats)
{
		return troxan_get_stat_int($stats, ['score', 'Experience points'], 0);
}
```

```php
function troxan_compare_leaderboard_rows(array $a, array $b): int
{
		$scoreCompare = ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
		if ($scoreCompare !== 0) {
				return $scoreCompare;
		}
		return strcasecmp((string)($a['username'] ?? ''), (string)($b['username'] ?? ''));
}
```

### A delta képlete

Legyen:

- $I$ = bejövő számláló érték,
- $S$ = előző session snapshot,
- $T$ = eddigi összesített érték.

Ekkor a növekmény:

$$
\Delta = \begin{cases}
I - S, & \text{ha } I \ge S \\
I, & \text{ha } I < S \text{ (session reset)}
\end{cases}
$$

Az új összesített érték:

$$
T_{new} = T + \max(\Delta, 0)
$$

Ez garantálja, hogy:

1. új session esetén nincs duplikált beszámítás,
2. resetnél nem lesz negatív korrekció,
3. a ranglista mindig monoton értelmes marad.

*[KÉP HELYE – Pontszámítás képlet + példa táblázat]*

\newpage

## Gombszintű funkcióleírás (oldalanként)

Ebben az alfejezetben oldalanként felsorolásra kerülnek a fontos gombok, felhasználói akciók, és a mögöttük futó logikák.

### Login oldal

1. Bejelentkezés gomb
2. Elfelejtett jelszó akció
3. Verifikációs kód megerősítése
4. Kötelező jelszócsere megerősítése

Technikai rész:

- Email + jelszó validáció
- `is_verified` ellenőrzés
- temp jelszó-ág (`force_password_change`)
- session és avatar adatok mentése localStorage-be

*[KÉP HELYE – Login űrlap gombjai számozva]*
*[KÉP HELYE – Force password change modal]*

### Regisztráció oldal

1. Regisztráció elküldése
2. E-mail verifikációs kód beküldése

Technikai rész:

- username egyediség és formátum ellenőrzés
- email validáció
- jelszó erősség és egyezés
- verifikációs kód lejárat kezelése

*[KÉP HELYE – Register form mezők + gomb]*
*[KÉP HELYE – Verification kód modal]*

### Főoldal / Basesite

1. Tab gombok (`basesite-btn-*`)
2. Download gomb (`basesite-download-game-btn`)
3. Feature request modal nyitás/zárás
4. Engineer settings edit/save gomb
5. Patch notes létrehozás/szerkesztés/törlés

Technikai rész:

- tabváltás animációval
- login-feltételes letöltés
- megerősítő modal callback lánc
- stale állapotok tisztítása (`reconcilePatchUiState`, `resetPatchDeleteConfirmState`)

*[KÉP HELYE – Basesite tabok]*
*[KÉP HELYE – Download gomb működés]*
*[KÉP HELYE – Patch note delete confirm modal]*

### Maps oldal

1. Mobil menü gomb
2. Saját térképekre ugrás gomb
3. Keresőmező
4. Rendezés dropdown
5. Add to library gomb
6. Törlés/Visszaállítás staff gombok
7. Help és Trash modal nyitó gombok

Technikai rész:

- kliensoldali szűrés + rendezés
- `POST /api/maps` add_to_library
- `POST /api/maps` delete_map
- role-alapú trash megjelenítés

*[KÉP HELYE – Maps kontrollsor gombjai]*
*[KÉP HELYE – Map card gombok (Add/Delete)]*
*[KÉP HELYE – Trash modal]*

### My Maps oldal

1. Keresés
2. Rendezés
3. Rename map
4. Remove from library
5. Publish / Unpublish

Technikai rész:

- kombinált SQL lekérdezés (saját + library)
- státusz alapú működés (`0`, `1`, `3`, `5`)
- letöltésszámláló korrekció eltávolításnál

*[KÉP HELYE – My Maps gombok és státusz badge-ek]*

### Profil oldal

1. Settings modal nyitás
2. Logout gomb
3. Avatar váltás
4. Felhasználónév/Jelszó módosítás
5. Admin panel navigációs gomb (jogosultságfüggő)

Technikai rész:

- profile modal animációk
- avatar mentés + header frissítés
- rank számítás backend oldalon

*[KÉP HELYE – Profil oldal elemei]*
*[KÉP HELYE – Avatar picker modal]*

### Leaderboard oldal

1. Rendezés trigger (ha jelen van)
2. Top lista és saját helyezés blokk

Technikai rész:

- legfrissebb stat rekord kiválasztása userenként
- score desc + username asc tie-break
- bannolt felhasználók kizárása

*[KÉP HELYE – Leaderboard top10 + current user sor]*

### Admin oldal

1. Kereső input
2. Ban/Unban gomb
3. Role change gomb
4. Hamburger action menü
5. User details modal
6. User map kezelő gombok (rename/remove)
7. Hard delete (Engineer)
8. Logs dátumszűrő + view gomb
9. Site settings frissítés (Engineer)

Technikai rész:

- erős jogosultságellenőrzés
- ban reason kötelező
- saját maga ban tiltás
- admin/engineer hierarchia

*[KÉP HELYE – Admin user card gombok számozva]*
*[KÉP HELYE – Ban reason modal]*
*[KÉP HELYE – Role change confirm modal]*
*[KÉP HELYE – Admin logs panel]*

\newpage

## API-k részletes specifikációja

### Auth végpontok

#### `POST /api/login`

Kért mezők:

- `email`
- `password`

Lehetséges ágak:

1. Hiányzó mező -> 400
2. Hibás hitelesítő -> 401
3. Nincs verifikálva -> 403 (`not_verified`)
4. Temp jelszó kötelező csere -> 403 (`force_password_change`)
5. Sikeres login -> 200

*[KÉP HELYE – Login endpoint válaszok táblázata]*

#### `POST /api/registration`

Két fő akció:

1. `registerUser`
2. `verifyRegistrationCode`

Eredmények:

- user létrehozás pending verifikációval
- kódellenőrzés után `is_verified = 1`

*[KÉP HELYE – Registration API flowchart]*

### Játék API végpontok

#### `POST /api/game_login`

Metóduskényszer, token-generálás, ban-check.

#### `GET /api/game_stats`

Bearer token kötelező, user-token párosítás, tiltásellenőrzés.

#### `POST /api/game_update_stats`

A legfontosabb integrációs pont:

1. Token ellenőrzés
2. Username anti-cheat check
3. Snapshot reset detektálás
4. Delta aggregáció
5. Új rekord beszúrás

*[KÉP HELYE – Game endpoints request body példák]*

### Tartalmi végpontok

1. `GET /api/main`
2. `GET /api/maps`
3. `GET /api/my_maps`
4. `GET /api/profile`
5. `GET /api/leaderboard`
6. `GET /api/statistics`
7. `GET/POST /api/admin`

Mindegyik végpont JSON választ ad, tipikusan:

```json
{
	"status": "success|error|info",
	"message": "...",
	"html": "..."
}
```

*[KÉP HELYE – API endpoint összefoglaló táblázat]*

\newpage

## Modal és állapotkezelés részletesen

A rendszer több helyen használ központi alert/confirm modalt. Mivel az összes frontend modul egy bundle-be kerülhet, fontos, hogy egy oldal eseménykezelése ne "ragassza be" egy másik oldal modalját.

Kiemelt védelmek:

1. Capture-phase event guard a confirm cancel/close esetekre.
2. Állapot nullázás callback lefutás után.
3. Oldalspecifikus DOM guard (`.admin-page-shell`, `.maps-site`).

Korábbi tipikus hiba, amit ez a minta megelőz:

- Modal láthatatlanná válik idegen CSS osztály miatt.
- `confirmCallback` bent ragad.
- `patchActionInProgress` true marad, ezért a következő művelet blokkolódik.

*[KÉP HELYE – Modal state diagram]*

\newpage

## Jogosultságkezelés (RBAC) működése

Szerepkörök:

1. Player
2. Moderator
3. Admin
4. Engineer

Fő szabályok:

1. Admin felület csak Admin/Engineer.
2. Engineer-only műveletek: hard delete, site settings végleges mentés.
3. Saját magát senki sem banolhatja.
4. Admin bannolásához Engineer jogosultság kell.
5. Bannolt user kérésenként route-szinten átirányításra kerül.

*[KÉP HELYE – Role matrix táblázat]*

\newpage

## Részletes teszteset-gyűjtemény

### Auth tesztek

1. Érvényes login
2. Hibás jelszó
3. Nem verifikált account
4. Temp jelszó lejárt
5. Force password change sikeres

### Game API tesztek

1. game_login siker
2. game_login banned
3. game_update_stats valid token
4. game_update_stats invalid token
5. game_update_stats username mismatch
6. session reset delta ellenőrzés

### UI tesztek

1. Modal nyit-zár ismételten frissítés nélkül
2. Patch note create/edit/delete folyamat
3. Maps add/remove flow
4. Admin ban/unban flow
5. Mobil menü nyitás-zárás

### Jogosultság tesztek

1. Player admin oldal tiltás
2. Admin hard delete tiltás
3. Engineer hard delete engedély

*[KÉP HELYE – Teszteredmény táblázat mintakép]*

\newpage

## Ábrajegyzék és képlistázó sablon

Az alábbi listát közvetlenül lehet használni Word-ben a képek beillesztésének ellenőrzésére:

1. Főoldal teljes nézet
2. Főoldal patch notes blokk
3. Login oldal
4. Login hibakezelés
5. Regisztráció oldal
6. E-mail verifikáció modal
7. Profil oldal
8. Avatar választó modal
9. Maps oldal desktop
10. Maps oldal mobil menü
11. Map card gombok
12. My Maps oldal
13. My Maps rename modal
14. Leaderboard oldal
15. Admin user lista
16. Admin ban modal
17. Admin role change megerősítés
18. Admin logs panel
19. Site settings editor
20. isBanned oldal
21. Guest oldal
22. API kérés/válasz minták (Postman)
23. Game login request
24. Game update stats request
25. Score aggregation példa

Ez a lista tetszőlegesen bővíthető oldalszám-cél szerint.

\newpage

# Biztonsági megoldások

A webes alkalmazások fejlesztése során a biztonság nem opcionális kiegészítő, hanem alapvető követelmény. A Troxan webalkalmazás fejlesztése során számos rétegben kerültek beépítésre védelmi megoldások.

## SQL injection elleni védelem

Az alkalmazás kizárólag PDO prepared statements segítségével kommunikál az adatbázissal. Ez azt jelenti, hogy a felhasználói bemeneteket a rendszer soha nem fűzi közvetlenül az SQL lekérdezés szövegébe, hanem mindig paraméterként adja át azokat a PDO réteg számára. Ez teljes körű védelmet nyújt az SQL injection típusú támadásokkal szemben, amelyek az OWASP Top 10 sérülékenységi lista egyik leggyakoribb és legsúlyosabb kategóriáját alkotják.

## Jelszó hashing

A felhasználók jelszavai soha nem kerülnek titkosítatlan szövegként az adatbázisba. A jelszavak tárolása a PHP beépített `password_hash()` függvényével, bcrypt algoritmussal történik. Az ellenőrzéshez a `password_verify()` függvény kerül alkalmazásra, amely időben konstans összehasonlítást végez, nehezítve az időzítéses oldaltámadásokat.

## Session biztonság

- A session élettartama 7200 másodpercre (2 óra) van korlátozva.
- Session tokenek generálása `bin2hex(random_bytes(32))` segítségével kriptográfiai minőségű véletlenszámokkal történik.
- Bejelentkezéskor a `Active_Web_Sessions` táblában tárolt token lehetővé teszi a szerver oldali session érvénytelenítést.

## Szerepköralapú hozzáférés-szabályozás

Az alkalmazás négy szerepkört ismer, amelyek hierarchiában helyezkednek el: **Player**, **Moderator**, **Admin** és **Engineer**. Minden adminisztrátori művelet végrehajtásakor a szerver oldali kontroller ellenőrzi a kérelmező felhasználó szerepkörét, és engedélytelnek minősülő kérést 403-as HTTP hibakóddal visszautasít. A szerepkörellenőrzés minden esetben a szerveren és az adatbázisból történik, nem csupán kliens oldali logika alapján.

## Token-alapú játék API hitelesítés

A játékkliens és a webszerver közötti kommunikáció token-alapú hitelesítéssel védett. A token kriptográfiai minőségű véletlen bytes-ból kerül előállításra, és egyhasználatos: az adatbázisban felülírásra kerül minden egyes bejelentkezéskor. A statisztika-feltöltési végpont csak érvényes token bemutatásával fogad el adatot.

## Input validáció és sanitizáció

Minden felhasználói bemenet – legyen szó az URL szegmensekről, a POST adatokról vagy a GET paraméterekről – a szerver oldali kontrollerben validálásra és `trim()`, `filter_var()`, illetve típusos kasztolás segítségével sanitizálásra kerül, mielőtt az adatbázisba vagy a válaszba kerülne. A JSON válaszokban az összes szöveg automatikusan HTML-entitásokká kódolódik (`JSON_HEX_TAG`, `JSON_HEX_APOS`, `JSON_HEX_QUOT` flagek segítségével), ami XSS (Cross-Site Scripting) elleni alapszintű védelmet biztosít.

## Bannolás ellenőrzés minden kérésnél

Minden egyes API kérés beérkezésekor, ha a munkamenetben bejelentkezési adat található, a rendszer azonnal adatbázis-lekérdezéssel ellenőrzi, hogy a felhasználó nincs-e kitiltva. Ez garantálja, hogy egy bannolt felhasználó nem tudja "kijárni" a bannját azzal, hogy egy nemrég kiadott session tokenrel próbál hozzáférni az adatokhoz.

\newpage

# Telepítés és üzembe helyezés

## Rendszerkövetelmények (szerver)

A Troxan webalkalmazás futtatásához az alábbi szerveres komponensek szükségesek:

- **PHP 8.1** vagy újabb verzió (PDO, PDO_MySQL, mbstring, openssl kiterjesztésekkel)
- **MySQL 8.0** vagy újabb verzió
- **Apache 2.4** webszerver (mod_rewrite engedélyezve)
- **Node.js 20+** és **npm** (csak a build folyamathoz szükséges)

## Build folyamat

Az alkalmazás produkciós üzembe helyezése az alábbi lépésekben történik:

```
1. npm install
2. npm run build
3. cp -r app dist/
4. cp .htaccess dist/
```

A `npm run build` parancsa lefuttatja a Vite bundlert, amely a `src/` mappában lévő JavaScript és CSS forrásokat lefordítja, kicsinyíti és egy optimalizált csomagba rendezi a `dist/` mappában. Ezután az `app/` PHP backend mappa és a `.htaccess` Apache konfigurációs fájl másolásra kerül a `dist/` mappába, amely így a teljes, deployra kész állapotot tartalmazza.

## Adatbázis konfiguráció

Az adatbázis kapcsolat paraméterei az `app/core/config.php` fájlban kerülnek beállításra:

```php
const DB_HOST    = "localhost";
const DB_USER    = "troxan_user";
const DB_PASS    = "...";
const DB_NAME    = "troxan_db";
const DB_CHARSET = "utf8mb4";
```

Az adatbázissémát az SQL szkript alkalmazásával kell inicializálni. A rendszer bizonyos táblákat (pl. `SiteSettings`, `Active_Web_Sessions`) automatikusan is létrehozza az első futtatáskor, ha azok még nem léteznek.

\newpage

# Tesztelés

## Manuális tesztelés

Az alkalmazás tesztelése elsősorban manuális módszerrel történt, az összes főbb felhasználói folyamatot végigkövetve mind böngészőben, mind a játékkliens szemszögéből. Az alábbi területeken kerültek tesztek elvégzésre:

**Regisztráció és hitelesítés:**

- Érvényes és érvénytelen adatbevitel esetén adott visszajelzések ellenőrzése
- E-mail küldés és a megerősítőkód validálásának tesztelése
- Lejárt megerősítőkód kezelésének ellenőrzése
- Elfelejtett jelszó és a kötelező jelszóváltás folyamatának tesztelése

**Jogosultságkezelés:**

- Különböző szerepkörű felhasználók (Player, Admin, Engineer) hozzáférésének ellenőrzése az adminisztrációs panelen
- Bannolt felhasználók átirányításának tesztelése
- Nem bejelentkezett felhasználók vendég nézetbe irányításának ellenőrzése

**Adatintegritás:**

- Térképek könyvtárba adásának és eltávolításának tesztelése
- Statisztika feltöltés és megjelenítés pontosságának ellenőrzése
- Ranglista sorrend helyességének ellenőrzése különböző pontszám-kombinációk esetén

**Felhasználói felület:**

- Modál ablakok megnyitásának, bezárásának és újranyitásának tesztelése (különösen a patch notes törlési megerősítő ablak)
- A navigáció helyes működésének ellenőrzése bejelentkezett és kijelentkezett állapotban
- Reszponzív megjelenés ellenőrzése különböző képernyőméreteken

**API végpontok:**

- Játék bejelentkezési folyamat tesztelése, érvényes és érvénytelen adatokkal
- Statisztika feltöltési végpont tesztelése érvényes tokennel és lejárt/érvénytelen tokennel
- HTTP metódus ellenőrzések (pl. GET helyett POST küldése esetén adott válasz)

## Ismert korlátok és megoldott hibák

A fejlesztés során számos szélsőséges esetben köszöntek be problémák, amelyeket az iteratív tesztelés során sikerült azonosítani és javítani. Ezek közül a legjelentősebbek:

- **Popup akadás probléma:** Az admin és a térképek oldalak globális eseményfigyelői interferáltak a főoldal megerősítő modáljával, esetenként megakadályozva a modal újranyitásának lehetőségét oldal-frissítés nélkül. A hiba javítása DOM-alapú lapőr feltételek bevezetésével történt.
- **Session timeout:** A PHP default session élettartama (24 perc) túl rövid volt az aktív felhasználói munkamenetekhez, ami váratlan kijelentkezéseket okozott. A javítás: 2 órára növelt session élettartam.

\newpage

# Továbbfejlesztési lehetőségek

*[HELYE – IDE KERÜLNEK A FEJLESZTÉSI LEHETŐSÉGEK]*

\newpage

# Melléklet A – Endpoint mátrix (részletes)

## A.1 Auth és session

### `POST /api/login`

Feladat: webes hitelesítés és session létrehozás.

Kritikus ellenőrzések:

1. Minden kötelező mező jelenléte.
2. Jelszó hash ellenőrzés.
3. Verifikált fiók feltétel.
4. Temp jelszó branch.
5. Web session token létrehozás és per-user upsert.

Válaszkódok:

1. 200 success
2. 400 bad input
3. 401 invalid credential
4. 403 business-rule tiltás
5. 500 váratlan backend hiba

*[KÉP HELYE – Login endpoint státuszkód térkép]*

### `POST /api/logout`

Feladat: session megszüntetése és token rekord törlése az aktív web session táblából.

Műveleti lépések:

1. `$_SESSION` adatok olvasása.
2. `Active_Web_Sessions` takarítása.
3. Session cookie és session állapot invalidálás.

*[KÉP HELYE – Logout flow diagram]*

## A.2 Játék integráció

### `POST /api/game_login`

Input:

```json
{
	"username": "player01",
	"password": "..."
}
```

Output (siker):

```json
{
	"status": "success",
	"data": {
		"user_id": 12,
		"username": "player01",
		"token": "..."
	}
}
```

*[KÉP HELYE – game_login request/response]*

### `GET /api/game_stats`

Header:

```text
Authorization: Bearer <token>
```

Ellenőrzések:

1. Token kinyerhető-e.
2. Token létezik-e userhez kötötten.
3. User nem bannolt-e.

*[KÉP HELYE – game_stats auth header példa]*

### `POST /api/game_update_stats`

Input minta:

```json
{
	"username": "player01",
	"coins": 1540,
	"level": 8,
	"statistics": {
		"score": 22500,
		"Mobs killed": 156,
		"Deaths": 9,
		"Story finished": 2
	}
}
```

Kulcspontok:

1. Alias mezők támogatása (`score` / `Experience points`).
2. Reset detektálás és delta alapú összegzés.
3. Mindig új stat rekord beszúrás (időbélyeges history).

*[KÉP HELYE – game_update_stats delta példa]*

## A.3 Tartalmi végpontok

### `GET /api/main`

Feladat: főoldali tartalom renderelése (`main.php`) és JSON burkolásban visszaadás.

### `GET /api/maps`

Feladat: aktív pályák lekérése, keresés/rendezés támogatás, role-függő trash lista.

### `POST /api/maps`

Akciók:

1. `add_to_library`
2. `delete_map`
3. `restore_map` (staff)

### `GET /api/my_maps`

Feladat: saját pályák és mentett könyvtár egyesített nézetben.

### `POST /api/my_maps`

Akciók:

1. `remove_map`
2. `rename_map`
3. `publish_map`
4. `unpublish_map`

### `GET /api/profile`

Feladat: profiladatok, rank, avatar készlet.

### `POST /api/profile`

Akciók:

1. `change_avatar`
2. `change_username`
3. `change_password`

### `GET /api/leaderboard`

Feladat: top lista és saját pozíció meghatározása.

### `GET/POST /api/admin`

Feladat: teljes adminisztrációs műveletkészlet.

Admin POST akciók példák:

1. `ban` / `unban`
2. `change_role`
3. `get_user_maps`
4. `rename_map`
5. `remove_map`
6. `hard_delete_user`
7. `change_username`
8. `get_user_logs`
9. `get_site_settings`
10. `update_site_settings`

*[KÉP HELYE – Admin endpoint action matrix]*

\newpage

# Melléklet B – Forráskód walkthrough fájlonként

## B.1 Backend core

### `app/core/config.php`

Tartalma:

1. DB konfigurációs konstansok
2. dátum-idő formázó helper
3. stat kinyerő helper-ek
4. score normalizáló helper
5. leaderboard comparator

Ez a fájl funkcionálisan az alkalmazás központi utility rétege.

*[KÉP HELYE – config.php helper függvények]*

### `app/core/router.php`

Tartalma:

1. URL path szeletelés
2. route tömb építés
3. JSON válasz helper
4. controller loader

### `app/core/router/api.php`

Tartalma:

1. globális ban ellenőrzés minden kérés előtt
2. route-to-controller mapping
3. unknown endpoint kezelése

*[KÉP HELYE – api router switch-case blokk]*

## B.2 Backend kontrollerek (üzleti logika)

### `registrationController.php`

Legfontosabb ágak:

1. registerUser
2. verifyRegistrationCode
3. e-mail kód lejárat

### `loginController.php`

Legfontosabb ágak:

1. loginUser
2. forcePasswordChange
3. Active_Web_Sessions token frissítés

### `logoutController.php`

Legfontosabb ág:

1. session törlés és redirect jellegű válasz

### `profileController.php`

Legfontosabb ágak:

1. profil adatok és rank összerakása
2. avatar csere
3. username csere
4. jelszó csere

### `mapsController.php`

Legfontosabb ágak:

1. aktív térképek lekérése
2. staff trash nézet
3. add_to_library
4. delete_map
5. restore_map

### `myMapsController.php`

Legfontosabb ágak:

1. saját + mentett map egyesített lekérdezés
2. remove_map
3. rename_map
4. publish/unpublish

### `leaderboardController.php`

Legfontosabb ágak:

1. legfrissebb stat rekord userenként
2. pontszám normalizálás
3. rendezés és rank kiosztás

### `statisticsController.php`

Feladat:

1. statisztika nézet renderelése

### `adminController.php`

Legösszetettebb controller, fő blokkjai:

1. jogosultságellenőrzés
2. user list/keresés
3. ban/unban
4. role change
5. map kezelések
6. logs nézet
7. site settings kezelése
8. hard delete

### `gameLoginController.php`

Fő blokk:

1. játék bejelentkeztetés és token kiadás

### `gameStatsController.php`

Fő blokk:

1. tokenes lekérés a játék felé

### `gameUpdateStatsController.php`

Fő blokk:

1. snapshot-alapú aggregáció, anti-cheat, persist

*[KÉP HELYE – Controller kapcsolat ábra]*

\newpage

## B.3 Frontend walkthrough

### `src/main.js`

Fő feladatok:

1. modulok importálása
2. fejléc állapotfrissítés (`updateHeader`)
3. login/profil gomb dinamikus cseréje

### `src/basesite-src/basesite.js`

Fő feladatok:

1. tab motor
2. modal rendszer
3. patch notes CRUD klienslogika
4. engineer beállítás-szerkesztő
5. stale state reset és guardok

### `src/login-src/login.js`

Fő feladatok:

1. login submit flow
2. verifikációs és force-change modal
3. localStorage/session szinkron

### `src/register-src/register.js`

Fő feladatok:

1. regisztrációs validáció
2. verifikációs modal branch

### `src/profile-src/profile.js`

Fő feladatok:

1. profil modal kezelők
2. avatar választás
3. jelszó/felhasználónév módosító flow

### `src/maps-src/maps.js`

Fő feladatok:

1. keresés/rendezés
2. add/delete/restore map akciók
3. mobil menü és modal kezelések

### `src/myMaps-src/myMaps.js`

Fő feladatok:

1. saját gyűjtemény szűrés
2. rename/remove/publish/unpublish

### `src/leaderboard-src/leaderboard.js`

Fő feladatok:

1. ranking render
2. esetleges kliensoldali rendezés

### `src/admin-src/admin.js`

Fő feladatok:

1. élő keresés
2. ban/role/rename/hard delete akciók
3. log megtekintés és dátumszűrés
4. modal-interakciók és callback kezelés

*[KÉP HELYE – Frontend modulkapcsolati ábra]*

\newpage

# Melléklet C – Gombkatalógus (ellenőrzőlista)

## C.1 Login és Register

1. Login submit
2. Forgot password submit
3. Verify code submit
4. Force password change submit
5. Register submit
6. Register verify submit

## C.2 Basesite

1. Tab gombok (Download/Lore/Patch notes stb.)
2. Open request modal
3. Close request modal
4. Download game
5. Patch create
6. Patch edit
7. Patch save
8. Patch delete
9. Confirm OK
10. Confirm Cancel
11. Alert OK
12. Site settings edit
13. Site settings save

## C.3 Profile

1. Settings open
2. Logout open
3. Logout confirm
4. Avatar modal open
5. Avatar select save
6. Username change confirm
7. Password change confirm
8. Navigate My Maps
9. Navigate Admin (ha jogosult)

## C.4 Maps

1. Mobile menu toggle
2. Go My Maps
3. Search input
4. Sort trigger
5. Sort option select
6. Help open
7. Trash open
8. Add to library
9. Delete map
10. Restore map
11. Confirm/Alert modal gombok

## C.5 My Maps

1. Search input
2. Sort trigger
3. Rename
4. Remove from library
5. Publish
6. Unpublish
7. Confirm/Alert modal gombok

## C.6 Leaderboard

1. Sort trigger (ha aktív)
2. Sort option select

## C.7 Admin

1. Search input
2. Ban toggle
3. Ban reason confirm
4. Change role
5. Username modal open
6. Username save
7. Open user details
8. Open user maps
9. Rename player map
10. Remove map from library
11. Hard delete open
12. Hard delete confirm
13. Logs open
14. Logs view detail
15. Logs date from/to filter
16. Back gomb
17. Confirm/Alert modal gombok

*[KÉP HELYE – Gombkatalógus ellenőrző táblázat screenshotokkal]*

\newpage

# Melléklet D – Részletes felhasználói folyamatok (lépésenként)

## D.1 Vendégből regisztrált felhasználó

### Cél

A folyamat bemutatja, hogyan jut el egy új látogató a sikeres regisztrációtól a működő profilig.

### Lépések

1. A felhasználó megnyitja a főoldalt.
2. A fejlécben rákattint a Login/Registration opcióra.
3. A regisztrációs formon kitölti a mezőket.
4. A frontend kliensoldali validációt futtat.
5. A backend szerveroldali validációt futtat.
6. A rendszer verifikációs kódot küld.
7. A felhasználó megadja a kódot.
8. A rendszer aktiválja a fiókot.
9. A felhasználó belép.
10. A rendszer sessiont nyit és fejlécet frissít.

*[KÉP HELYE – D.1-01 Főoldal nyitóállapot]*
*[KÉP HELYE – D.1-02 Regisztrációs űrlap kitöltve]*
*[KÉP HELYE – D.1-03 Verifikációs modal]*
*[KÉP HELYE – D.1-04 Sikeres login utáni fejléc]*

### Gyakori hibafutások

1. Már létező email.
2. Már létező username.
3. Lejárt verifikációs kód.
4. Rossz formátumú email.
5. Rövid/gyenge jelszó.

*[KÉP HELYE – D.1-H1 Duplicate email hiba]*
*[KÉP HELYE – D.1-H2 Verification expired hiba]*

\newpage

## D.2 Bejelentkezés és kötelező jelszócsere

### Cél

Annak bemutatása, hogy a temp jelszavas ágon hogyan kényszeríti a rendszer az azonnali biztonsági jelszócserét.

### Lépések

1. Felhasználó belép ideiglenes jelszóval.
2. A backend `force_password_change` hibakóddal válaszol.
3. A frontend megnyitja a force change modalt.
4. A felhasználó megadja az új jelszót és megerősítést.
5. A backend validál és ment.
6. A `has_temp_password` flag visszaáll 0-ra.
7. A felhasználó normál sessionnel folytatja.

*[KÉP HELYE – D.2-01 Force password change modal megnyitva]*
*[KÉP HELYE – D.2-02 Sikeres jelszócsere visszajelzés]*

\newpage

## D.3 Játékos statisztikaútvonal a kliensből a ranglistáig

### Cél

Bemutatni, hogyan jut el a játékon belül elért teljesítmény a weboldali leaderboard megjelenésig.

### Lépések

1. Játékkliens hitelesít (`game_login`).
2. Token mentése kliensoldalon.
3. Játék során statok gyűjtése memóriában.
4. Session végén `game_update_stats` hívás.
5. Backend tokenellenőrzés és anti-cheat check.
6. Delta aggregáció és `Statistics` insert.
7. Leaderboard lekérés (`/api/leaderboard`).
8. Rendezés score DESC szerint.
9. Top10 + saját helyezés megjelenítése.

*[KÉP HELYE – D.3-01 Game kliens login képernyő]*
*[KÉP HELYE – D.3-02 game_update_stats request body]*
*[KÉP HELYE – D.3-03 Leaderboard frissült pontszámmal]*

### Hibatűrési pontok

1. Missing token -> 401.
2. Invalid token -> 401.
3. Username mismatch -> 403.
4. Banned account -> 403.
5. Method mismatch -> 405.

*[KÉP HELYE – D.3-H1 Invalid token response]*

\newpage

## D.4 Térkép életciklus teljes folyamat

### Állapotok

1. `0` = Draft
2. `1` = Published
3. `3` = Unpublished
4. `4` = Banned/Trash
5. `5` = Creator deleted but library-kept

### Példafolyamat

1. Creator draft pályát készít.
2. Publish művelet -> státusz `1`.
3. Más felhasználó könyvtárba menti.
4. Creator visszavonja -> státusz `3`.
5. Staff moderálja a trash nézetből.
6. Visszaállítás esetén aktív lista.

*[KÉP HELYE – D.4-01 Draft map kártya]*
*[KÉP HELYE – D.4-02 Published map kártya]*
*[KÉP HELYE – D.4-03 Trash modal nézet]*

### Letöltésszámláló logika

1. Library add -> counter +1.
2. Library remove -> counter -1 (ha releváns).
3. Többszörös mentés tiltása (info válasz).

*[KÉP HELYE – D.4-04 Downloads counter before/after]*

\newpage

## D.5 Admin moderációs folyamatok

### Ban folyamat

1. Admin kiválasztja a felhasználót.
2. Ban gomb megnyomása.
3. Ban reason modal kötelező kitöltéssel.
4. Backend szerepkör-szabály ellenőrzés.
5. Státusz váltás és visszajelzés.

*[KÉP HELYE – D.5-01 Admin user list]*
*[KÉP HELYE – D.5-02 Ban reason modal kitöltve]*
*[KÉP HELYE – D.5-03 Ban success alert]*

### Unban folyamat

1. Bannolt user kiválasztás.
2. Unban gomb.
3. Megerősítés.
4. Backend státusz visszaváltás.

*[KÉP HELYE – D.5-04 Unban megerősítés]*

### Role change folyamat

1. Promote vagy demote gomb.
2. Confirm modal.
3. Backend role validáció.
4. UI reload friss role adatokkal.

*[KÉP HELYE – D.5-05 Role change confirm]*

### Hard delete (Engineer)

1. Engineer kiválaszt célfelhasználót.
2. Erős megerősítés (szöveg + confirm).
3. Backend végleges törlés.
4. Kapcsolódó adatok cascade tisztítása.

*[KÉP HELYE – D.5-06 Hard delete modal]*

\newpage

# Melléklet E – UI állapotok és eseménytranzíciók

## E.1 Modal állapotgépek

Általános állapotok:

1. Hidden
2. Opening
3. Visible
4. Closing
5. Reset

Tranzíciók:

1. Hidden -> Opening (open click)
2. Opening -> Visible (anim vége)
3. Visible -> Closing (cancel/close/backdrop)
4. Closing -> Hidden (anim vége)
5. Hidden -> Reset (callback nullázás)

*[KÉP HELYE – E.1 Modal state machine diagram]*

## E.2 Patch notes szerkesztés állapotok

1. Idle
2. Edit mode
3. Save pending
4. Save success
5. Delete pending
6. Delete confirmed

Reset szabályok:

1. confirm cancel mindig tisztítja a state-et.
2. aktív edit id elvesztésekor fallback reset.
3. párhuzamos mentés tiltása.

*[KÉP HELYE – E.2 Patch notes state diagram]*

## E.3 Maps keresés és rendezés tranzíciók

1. Input változás -> filter fut.
2. Sort váltás -> újrarendezés.
3. Add művelet -> lokális kártya frissítés.
4. Delete művelet -> animált eltávolítás.

*[KÉP HELYE – E.3 Maps filter/sort állapotábra]*

## E.4 Admin menü állapotok

1. Card menu closed
2. Card menu open
3. Másik card nyitása -> előző zárása
4. külső kattintás -> összes zárása

*[KÉP HELYE – E.4 Admin card action menu állapotok]*

\newpage

# Ábrajegyzék – részletes képbeillesztési checklista

## F.1 Kötelező képek funkciónként

1. Login oldal – alap nézet
2. Login oldal – hibás jelszó
3. Login oldal – nem verifikált account
4. Login oldal – force password change
5. Register oldal – alap nézet
6. Register oldal – sikeres beküldés
7. Register oldal – verifikációs modal
8. Register oldal – lejárt kód hiba
9. Főoldal – teljes nézet
10. Főoldal – trailer blokk
11. Főoldal – letöltés blokk
12. Főoldal – lore blokk
13. Főoldal – patch notes lista
14. Főoldal – patch note edit
15. Főoldal – patch note delete confirm
16. Profile oldal – alap nézet
17. Profile oldal – avatar modal
18. Profile oldal – username change
19. Profile oldal – password change
20. Maps oldal – desktop
21. Maps oldal – mobile menu
22. Maps oldal – search/sort használat
23. Maps oldal – add to library siker
24. Maps oldal – delete confirm
25. Maps oldal – trash modal
26. My Maps oldal – alap nézet
27. My Maps oldal – rename flow
28. My Maps oldal – publish flow
29. My Maps oldal – unpublish flow
30. Leaderboard oldal – top10
31. Leaderboard oldal – current user blokk
32. Admin oldal – user lista
33. Admin oldal – ban modal
34. Admin oldal – role change confirm
35. Admin oldal – user maps modal
36. Admin oldal – logs panel
37. Admin oldal – hard delete modal
38. Admin oldal – site settings editor
39. isBanned oldal
40. Guest fallback oldal
41. API: game_login request
42. API: game_login response
43. API: game_update_stats request
44. API: game_update_stats response
45. Pontszám aggregáció példa táblázat
46. Role matrix táblázat
47. Router működési ábra
48. Modal state machine
49. Endpoint mátrix kép
50. Build/deploy lépés képernyőkép

## F.2 Opcionális képek oldalszám növeléshez

1. Minden fő gomb külön közeli screenshot
2. Minden modal nyitott és zárt állapota
3. Mobil nézetek külön oldalra
4. Hibaüzenetek katalógusa képekkel
5. Postman endpoint kollekció képei

*[KÉP HELYE – F. Összesített képkatalógus sablon]*

\newpage

# Melléklet G – Backend kontrollerek részletes, kódközeli magyarázata

## G.1 `loginController.php`

Ez a kontroller nem egyszerűen egy "belépés" gomb mögötti backend, hanem több, egymástól jól elkülöníthető hitelesítési forgatókönyvet kezel.

### Fő belépési ág: `loginUser($input)`

A folyamat első lépése a bemenet minimális ellenőrzése. A backend kimenti az `email` és `password` mezőket, majd rögtön kizárja az üres kéréseket. Ez azért fontos, mert a frontend validáció önmagában nem elég: egy kliensoldal könnyen megkerülhető kézi HTTP kérésekkel.

Ezután egy összetett SQL lekérdezés történik, amely nemcsak a felhasználó alapadatait húzza be, hanem a szerepkört és az avatart is. Ennek az a gyakorlati előnye, hogy sikeres login után a frontend már egyetlen válaszból megkaphat minden olyan információt, amely a fejléc és a session állapot felépítéséhez kell.

Az ellenőrzés ágai sorrendben:

1. létezik-e ilyen email,
2. egyezik-e a jelszó hash,
3. verifikált-e a fiók,
4. ideiglenes jelszó alatt áll-e a felhasználó,
5. ha igen, az ideiglenes jelszó lejárt-e.

Ez a sorrend azért jó, mert üzletileg értelmes hibákat ad vissza, ugyanakkor nem engedi át a jogosulatlan állapotokat a session létrehozásig.

Sikeres belépéskor a rendszer a következő session mezőket írja:

```php
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['username']  = $user['username'];
$_SESSION['role_name'] = $user['role_name'] ?? 'Player';
$_SESSION['logged_in'] = true;
```

Ezután létrejön vagy frissül az `Active_Web_Sessions` rekord, valamint generálódik egy külön websaját token is. Ez a token nem a játéktoken helyett van, hanem a webes session élettartamának és állapotának szerveroldali nyomon követésére.

### Kényszerített jelszócsere: `forcePasswordChange($input)`

Ez az ág akkor aktiválódik, amikor a felhasználó temp jelszóval próbál belépni. A backend itt négy dolgot vizsgál:

1. minden mező megvan-e,
2. az új jelszó és a megerősítés egyezik-e,
3. az új jelszó elég erős-e,
4. a régi jelszó tényleg az ideiglenes jelszó-e.

Siker esetén a `User` rekordból törlődik a temp jelszó állapot, nullázódik a lejárat, és normál hash kerül a jelszó mezőbe. Ezután e-mailes megerősítés is kiküldhető, ami felhasználói és audit oldalról is előnyös.

### Elfelejtett jelszó ág

Ez az útvonal generál egy egyszer használható, időkorlátos ideiglenes jelszót. Itt az a fontos, hogy a backend nem tárol plain text jelszót, csak a hash-t, és a temp jelszó is ugyanazon hash-elési elven megy át, mint a normál jelszó.

*[KÉP HELYE – G.1 loginController folyamatábra]*
*[KÉP HELYE – G.1 force password change branch]*

\newpage

## G.2 `registrationController.php`

Ez a kontroller két nagy folyamatot kezel: a felhasználó létrehozását és a létrehozott felhasználó e-mailes aktiválását.

### Regisztrációs létrehozás

Az input egyszerre támogat JSON és form alapú forrást, ami rugalmasabb frontend integrációt tesz lehetővé. A validáció több szinten történik:

1. kötelező mezők,
2. felhasználónév hossza és karakterkészlete,
3. email formátum,
4. jelszó minimumhossz,
5. jelszómegerősítés egyezése,
6. username/email egyediség.

Különösen fontos, hogy a rendszer nemcsak `User` rekordot készít, hanem létrehozza a kapcsolódó kezdeti adatszerkezeteket is, például üres `Statistics` sort és kapcsolódó beállítási struktúrát. Ez azért előnyös, mert később a profil- és ranglistaoldal úgy tud működni, hogy nem kell mindenhol külön "van-e már stat rekord?" típusú fallback logikát írni.

### Verifikációs kód ellenőrzés

A verifikáció során a backend nemcsak azt nézi, hogy a kód numerikusan helyes-e, hanem azt is, hogy még időben érvényes-e. Ha a felhasználó már verifikált, a rendszer nem hibát dob, hanem idempotens módon sikeres állapotot is vissza tud adni. Ez felhasználóbarát és kliensoldali integráció szempontból is stabilabb.

*[KÉP HELYE – G.2 registrationController validációs táblázat]*

\newpage

## G.3 `logoutController.php`

Ez a kontroller látszólag egyszerű, de egy fontos feladata van: következetesen takarítani a session és az aktív webes token állapotot.

Lépések:

1. session változók kiolvasása,
2. `Active_Web_Sessions` rekord törlési kísérlete,
3. `$_SESSION` nullázása,
4. `session_destroy()`,
5. session cookie invalidálás.

Ez azért fontos, mert ha csak a kliensoldali localStorage ürülne, a szerveroldali session még élhetne, ami inkonzisztens kijelentkezési állapotot eredményezne.

## G.4 `profileController.php`

Ez a fájl a bejelentkezett felhasználó teljes személyes dashboardját állítja össze.

### Profiloldal felépítése

A `getContent()` több egymásra épülő adatlekérést végez:

1. user alapadatok,
2. legfrissebb statisztikai rekord,
3. teljes leaderboard lista a saját helyezés kiszámításához,
4. összes elérhető avatar.

Ez egy tudatos döntés: a profiloldal nem csak a saját rekordot mutatja, hanem összeveti a játékost a többiekkel is. Emiatt a rank számítás itt is történik, nem csak a leaderboard nézetben.

### Avatar csere

Az avatarcsere nem csupán egy UI művelet. A backend átállítja az `avatar_id` mezőt, a frontend pedig a localStorage-ban tárolt avatart és a fejlécet is frissíti. Ez egy jó példa a szerveroldali igazság és kliensoldali megjelenés szinkronjára.

### Username és jelszó változtatás

A névváltoztatásnál a backend cooldown vagy utolsó módosítási dátum logikával is dolgozhat, a jelszóváltoztatásnál pedig mindig szükség van a régi jelszó megerősítésére. Ez meggátolja, hogy egy nyitva hagyott sessionben valaki csendben átírja a jelszót.

*[KÉP HELYE – G.4 profileController adatforrásai]*

\newpage

## G.5 `mapsController.php`

Ez a kontroller egyszerre katalógus, könyvtár-integráció és moderációs belépési pont.

### GET ág – aktív térképek listája

Az SQL lekérdezés egyszerre hozza:

1. a pálya adatait,
2. a készítő nevét és szerepkörét,
3. azt az információt, hogy az adott pálya benne van-e az aktuális user könyvtárában.

Ez utóbbi azért fontos, mert így a frontend egyből tudja, hogy az "Add" gombot milyen állapotban jelenítse meg.

### POST ág – add és delete műveletek

Az `add_to_library` akció egyszerre két üzleti hatást hordoz:

1. bekerül a kapcsolat a `User_Map_Library` táblába,
2. nő a letöltésszám.

Ez a gyakorlatban azt jelenti, hogy a rendszer a könyvtárba mentést használja letöltési/metrikai eseményként is.

Törlésnél a státuszkezelés miatt nem minden esetben fizikai törlés történik. A rendszer inkább állapotot vált, hogy a moderáció és a visszaállítás lehetősége megmaradjon.

## G.6 `myMapsController.php`

Ez a kontroller talán az egyik legérdekesebb SQL oldalról, mert két külön forrásból épít egységes listát:

1. a user saját pályái,
2. a könyvtárába elmentett pályák.

Az SQL feltétel emiatt összetett, hiszen más státuszkészlet vonatkozik a saját és a mentett térképekre. A különböző státuszok (`0`, `1`, `3`, `5`) azt biztosítják, hogy a játékos a számára releváns tartalmat akkor is lássa, ha annak életciklusa már eltér az eredeti publikált állapottól.

Publish/unpublish esetén a backend üzleti állapotot vált, nem pusztán UI flag-et.

*[KÉP HELYE – G.6 myMaps SQL logika ábra]*

\newpage

## G.7 `leaderboardController.php`

Ez a fájl a játék versenylogikájának nyilvános reprezentációja.

Legfontosabb döntései:

1. minden userből csak a legfrissebb stat rekordot veszi,
2. bannolt usereket kizárja,
3. score szerint csökkenő sorrendben rendez,
4. pontegyenlőség esetén név szerint rendez.

Ez a működés megakadályozza, hogy régi stat sorok vagy törölt/bannolt játékosok torzítsák a rangsort.

## G.8 `statisticsController.php`

Ez a kontroller egyszerűbb, mert főleg view render szerepet lát el. Mégis fontos, mert külön statisztikai oldalt biztosít a rendszernek, így a játék teljesítményadatai nem csak a profil vagy leaderboard alá vannak beszórva.

## G.9 `adminController.php`

Ez a legnagyobb és legösszetettebb controller. Itt koncentrálódik a legtöbb üzleti és jogosultsági szabály.

### Admin nézet felépítése

A GET ág lekéri a felhasználókat, szerepköröket, avatart és a legfrissebb statisztikai rekordot. A keresés már itt, SQL szinten is támogatható.

### Ban/unban logika

Ez a blokk több kritikus szabályt tartalmaz:

1. saját magát senki nem tilthatja ki,
2. Engineer tiltása védett,
3. Admin tiltása csak Engineer által történhet,
4. ban indok megadása kötelező.

Ez azt mutatja, hogy a rendszer nem csak funkcionálisan, hanem szervezeti hierarchiában is gondolkodik.

### Role change

A szerepkör-váltásnál a backendnek kell garantálnia, hogy a frontendről érkező gombkattintás ne tudja felülírni a szervezeti korlátokat.

### User map műveletek

Az admin képes egy user térképkönyvtárát megnézni, térképet átnevezni vagy eltávolítani. Ez moderációs és támogatási szempontból is fontos eszköz.

### Hard delete

Ez a legveszélyesebb művelet, ezért Engineer-only. Itt az alkalmazásnak különösen erős megerősítési mechanizmust kell használnia, mert a művelet visszafordíthatatlan lehet.

### Site settings

Az admin controller egyik különleges része, hogy nemcsak user- és map-adminisztrációt kezel, hanem CMS-szerű oldaltartalom-szerkesztést is. Ezzel a főoldal kulcsszövegei, trailer URL-je, letöltési linkje és egyéb tartalmi elemek kódmódosítás nélkül alakíthatók.

*[KÉP HELYE – G.9 adminController alrendszerei]*

\newpage

## G.10 `gameLoginController.php`, `gameStatsController.php`, `gameUpdateStatsController.php`

Ez a három kontroller együtt adja a játék–web integráció gerincét.

### `gameLoginController.php`

Feladata a játékkliens azonosítása és a token kiadása. A webes sessiontől független, dedikált játéktoken itt keletkezik.

### `gameStatsController.php`

Feladata a tokennel hitelesített lekérés a játék számára. Ez lehetőséget ad arra, hogy a kliens mindig a szerveroldali valós állapotból dolgozzon.

### `gameUpdateStatsController.php`

Ez a legfontosabb integrációs egység. A `troxan_stats_pick_int` helper alias-kulcsokból is tud dolgozni, így a backend toleránsabb többféle kliens JSON szerkezettel szemben. A snapshot metaadatok használata pedig megakadályozza a statok duplikált vagy hibás aggregálását.

*[KÉP HELYE – G.10 Game API controller trio]*

\newpage

# Melléklet H – Frontend eseménykezelők teljes bontása

## H.1 `main.js`

Ez a modul nem egyszerűen betöltőfájl, hanem a kliensoldali navigáció és fejlécállapot egyik központi koordinátora.

### Fontos viselkedések

1. a különböző oldalmodulok importálása,
2. a `loadContent()` segítségével dinamikus oldaltöltés,
3. `updateHeader()` segítségével Login gomb és profil-avatar cseréje,
4. localStorage alapú session-visszaállítás.

Ha be van lépve a user, a `href="/login"` link helyére egy avataros profilblokk kerül. Mobilon külön profile link jön létre. Ha nincs bejelentkezve, ez a folyamat fordított irányban visszaépíti a login linkeket.

## H.2 `basesite.js`

Ez az egyik legsűrűbb frontend modul.

### Tab eventek

Az összes `.basesite-tab-btn` kattintás központi event delegationnel működik. A handler:

1. azonosítja a target tabot,
2. elrejti az összes tab contentet,
3. aktiválja a megfelelőt,
4. animációt futtat,
5. scrollbar állapotot szinkronizál.

### Alert/confirm modal eventek

Különlegesség a capture-phase click listener, amely minden cancel/close/backdrop esemény előtt képes állapotot tisztítani. Ez a modal beragadás ellen kulcsfontosságú.

### Patch note eventek

Főbb gombok:

1. lock/unlock,
2. edit,
3. save,
4. delete,
5. confirm cancel/ok.

Állapotváltozók:

1. `patchActionInProgress`,
2. `activeEditPatchId`,
3. `confirmCallback`.

### Site settings editor

Az edit gombra kattintva a rendszer runtime HTML editor mezőket generál. A save gomb ekkor már nem ugyanaz az állapot, hanem módosított ID-val és szereppel működik.

*[KÉP HELYE – H.2 basesite event delegation térkép]*

\newpage

## H.3 `login.js`

Ez a modul több egymásba ágyazott modal- és auth-flow-t kezel.

Fő eseménycsoportok:

1. login form submit,
2. verification code modal submit,
3. forgot password submit,
4. forced password change submit.

Itt különösen fontos a callback és Promise-szerű vezérlés, mert egy hibakódtól függően teljesen más UI ág nyílik meg.

## H.4 `register.js`

Itt a legfontosabb frontend feladat a felhasználó gyors, kliensoldali visszajelzésekkel történő segítése.

Események:

1. regisztrációs űrlap submit,
2. modal kód beküldés,
3. hibaállapotok vizuális megjelenítése.

## H.5 `profile.js`

Fő UI események:

1. settings nyitása,
2. logout modal nyitása,
3. avatar modal nyitása,
4. avatar elem kiválasztása,
5. username change submit,
6. password change submit,
7. my maps navigáció,
8. admin navigáció jogosultság esetén.

Itt a modal nyitás-zárás animált osztályokkal történik, nem csak `display:none` szintű kapcsolással, ami felhasználói élmény szempontból sokkal kifinomultabb.

*[KÉP HELYE – H.5 profile modal state-ek]*

\newpage

## H.6 `maps.js`

Ez a modul egyszerre kereső, rendező, könyvtárkezelő és staff moderációs UI.

### Fő események

1. mobile menu toggle,
2. live search input,
3. Enter tiltása keresőben,
4. sort dropdown nyitás/zárás,
5. sort elem kiválasztás,
6. help modal nyitás,
7. trash modal nyitás,
8. add to library,
9. delete map,
10. restore map.

Különösen fontos, hogy a modul oldalspecifikus guardot használ:

```js
if (!document.querySelector('.maps-site')) return;
```

Ez megakadályozza, hogy más oldalakon is belefusson ugyanaz a globális click handler.

### Add to library vizuális flow

Siker esetén a modul azonnal lokálisan is frissíti:

1. a downloads számlálót,
2. a gomb feliratát,
3. a gomb CSS osztályait,
4. az állapotot jelző data attribútumot.

Ez csökkenti a teljes újratöltés szükségességét és gyorsabbnak érződik az UI.

## H.7 `myMaps.js`

Fő események:

1. keresés input,
2. rendezés dropdown,
3. rename confirm,
4. remove_map confirm,
5. publish,
6. unpublish,
7. modal ok/cancel műveletek.

Ez a modul szintén saját alert/confirm megoldással és state-gépekkel dolgozik.

## H.8 `leaderboard.js`

Fő feladata a ranglista kliensoldali támogatása: adott esetben rendezési vagy megjelenítési logikák segítése. A nehéz logika azonban itt nem frontend oldalon, hanem backend oldalon történik.

## H.9 `admin.js`

Ez a frontend egyik legösszetettebb része.

### Élő keresés

Az `input` event azonnal szűri az `.admin-user-card` elemeket. A kódban külön kiszűrésre kerülhetnek a vizuális ikonok, például lakatjelölések, hogy a keresés ténylegesen a felhasználónévre vonatkozzon.

### Action menu kezelés

A hamburger gombokkal nyitható card-action menük egyik fontos mintája, hogy egyszerre csak egy menü marad nyitva, és külső kattintás mindent bezár.

### Ban/role modal kezelések

Az admin UI több globális state objektumot tart fenn, például:

1. `currentBanTarget`,
2. `currentAdminTargetUser`,
3. `currentAdminRenameMap`,
4. `currentHardDeleteTarget`,
5. `currentLogsData`.

Ezek a state-ek teszik lehetővé, hogy a több lépésből álló modalflow-k konzisztensen működjenek.

*[KÉP HELYE – H.9 admin.js event/state diagram]*

\newpage

# Melléklet I – API hibakód-katalógus és válaszminták

## I.1 `POST /api/login`

### 400 – hiányzó mezők

```json
{
	"status": "error",
	"message": "All fields are required!"
}
```

### 401 – hibás hitelesítő

```json
{
	"status": "error",
	"message": "Invalid email or password!"
}
```

### 403 – nincs verifikálva

```json
{
	"status": "error",
	"code": "not_verified",
	"message": "Your account is not verified yet."
}
```

### 403 – kötelező jelszócsere

```json
{
	"status": "error",
	"code": "force_password_change",
	"message": "You must change your password before accessing your account."
}
```

## I.2 `POST /api/registration`

### 400 – formátumhiba

Lehetséges okok:

1. hiányzó mező,
2. hibás email,
3. túl rövid jelszó,
4. nem egyező jelszavak,
5. tiltott username karakterek.

### 409 – foglalt username vagy email

```json
{
	"status": "error",
	"message": "Username or email already taken!"
}
```

### 403 – lejárt verifikációs kód

```json
{
	"status": "error",
	"message": "The verification code has expired. Please register again."
}
```

## I.3 `POST /api/game_login`

### 400 – hiányzó username/password
### 401 – rossz hitelesítő
### 403 – bannolt user
### 405 – rossz HTTP metódus

Példa:

```json
{
	"status": "error",
	"message": "A fiókod ki van tiltva a szerverről!"
}
```

## I.4 `GET /api/game_stats`

### 401 – hiányzó vagy hibás token
### 403 – bannolt fiók
### 405 – nem GET kérés

## I.5 `POST /api/game_update_stats`

### 400 – hibás JSON vagy hiányos stat szerkezet
### 401 – invalid token
### 403 – cheat gyanú / username mismatch / banned
### 405 – rossz metódus

Példa cheat-védelmi válasz:

```json
{
	"status": "error",
	"message": "Username mismatch or invalid token."
}
```

## I.6 `GET/POST /api/profile`

### 200 guest fallback

Érdekesség, hogy nem minden nem-hitelesített hozzáférés kemény hibakód. Egyes ágak guest view-t adnak vissza, ami UX szempontból sokkal finomabb.

### 401 – login required
### 404 – user not found
### 500 – SQL error

## I.7 `GET/POST /api/maps`

### 401 – login required
### 403 – tiltott akció vagy staff-only művelet
### 404 – pálya nem található

## I.8 `GET/POST /api/my_maps`

### 401 – login required
### 403 – idegen pálya módosítása tiltott
### 404 – map not found

## I.9 `GET/POST /api/admin`

### 401 – unauthorized access
### 403 – insufficient role
### 404 – user/map not found
### 400 – validation hiba (pl. üres ban reason)
### 500 – adatbázis vagy belső hiba

Példák:

```json
{
	"status": "error",
	"message": "Only Admins and Engineers can access this area."
}
```

```json
{
	"status": "error",
	"message": "You cannot ban yourself."
}
```

```json
{
	"status": "error",
	"message": "Engineers cannot be banned."
}
```

## I.10 Hibakódok dokumentálási javaslata a beadott Word fájlban

Minden nagy endpoint blokkhoz érdemes külön mini táblázatot illeszteni az alábbi oszlopokkal:

1. HTTP kód
2. belső `status`
3. opcionális `code`
4. felhasználói üzenet
5. kliensoldali reakció

Ez vizuálisan is nagyon erősíti a dokumentáció szakmai hatását.

*[KÉP HELYE – I. hibakód-katalógus táblázat]*

\newpage

# Melléklet J – Gombonkénti teljes funkcionális specifikáció

## J.1 Login oldal – elemenkénti működés

### J.1.1 Login submit gomb

**Felhasználói cél:** belépés meglévő fiókkal.

**Trigger:** login form `submit`.

**Frontend lépések:**

1. beolvassa `email` és `password` mezőket,
2. minimális kliensoldali validáció,
3. elküldi a kérést `POST /api/login` útvonalra,
4. feldolgozza a választ és branch-el.

**Siker esetén:**

1. localStorage frissül (`isLoggedIn`, `username`, `userAvatar`),
2. header frissül,
3. a felhasználó továbblép főoldalra/profilra.

**Hiba esetén:**

1. hibamodal vagy inline üzenet,
2. mezők megtartása,
3. fókusz vissza a hibás inputra.

*[KÉP HELYE – J.1.1 login gomb kattintás előtti/utáni állapot]*

### J.1.2 Elfelejtett jelszó gomb

**Trigger:** forgot password gomb/modal submit.

**Backend:** `POST /api/login` `action=forgot_password`.

**Fontos logika:**

1. email formátum ellenőrzés,
2. létező user ellenőrzés,
3. temp jelszó generálás,
4. temp jelszó hash mentés,
5. e-mail küldés.

*[KÉP HELYE – J.1.2 forgot password flow]*

### J.1.3 Force password change megerősítő gomb

**Trigger:** temp jelszavas belépés után modal submit.

**Művelet:**

1. old/new/confirm mezők ellenőrzése,
2. új hash mentése,
3. temp flag törlése,
4. sikeres visszajelzés.

*[KÉP HELYE – J.1.3 force password change mezők]*

## J.2 Regisztráció oldal – elemenkénti működés

### J.2.1 Regisztráció elküldése

**Input mezők:** username, email, password, password_confirm.

**Kliensoldali validáció:**

1. üres mezők tiltása,
2. email regex,
3. jelszó minimumhossz,
4. jelszó egyezés.

**Szerveroldali validáció pluszban:**

1. username minta,
2. username/email egyediség,
3. stat/default adatok létrehozása.

*[KÉP HELYE – J.2.1 register form validációs állapotok]*

### J.2.2 Verifikációs kód megerősítése

**Művelet:** `verifyRegistrationCode()` ág fut.

**Lehetséges eredmények:**

1. success,
2. already verified,
3. invalid code,
4. expired code.

*[KÉP HELYE – J.2.2 code modal success/error]*

## J.3 Főoldal (Basesite) – elemenkénti működés

### J.3.1 Tab gombok (`basesite-btn-*`)

**Funkció:** aktív szekció váltása újratöltés nélkül.

**DOM változások:**

1. tab panelek hide/show,
2. aktív/inaktív gombosztály csere,
3. belépő animáció,
4. scrollbar-mód szinkron.

*[KÉP HELYE – J.3.1 tabváltás 1-2-3 állapot]*

### J.3.2 Download gomb

**Guard:** ha nincs login, hibamodal + login redirect.

**Sikeres ág:** `window.location.href = downloadUrl`.

**Hibaág:** hiányzó URL esetén engineernek szóló hiba.

*[KÉP HELYE – J.3.2 download guard működés]*

### J.3.3 Patch notes gombok

**Gombcsoport:** create, edit, save, delete, lock.

**Állapotvédelem:**

1. `patchActionInProgress` párhuzam tiltás,
2. `activeEditPatchId` egyidejű szerkesztés tiltás,
3. capture-phase cancel reset.

**Delete flow:**

1. delete gomb,
2. confirm modal,
3. OK -> backend delete,
4. UI frissítés.

*[KÉP HELYE – J.3.3 patch note create/edit/delete sorozat]*

### J.3.4 Site settings edit/save gomb

**Jogosultság:** Engineer.

**Edit mód:** runtime editor mezők generálása.

**Save mód:** validáció + API mentés + vizuális visszaállítás.

*[KÉP HELYE – J.3.4 site settings editor mezők]*

## J.4 Maps oldal – elemenkénti működés

### J.4.1 Mobil menü gomb

**Funkció:** vezérlősáv összehajtása/kinyitása mobilon.

**Edge case:** külső kattintás bezárja.

### J.4.2 Kereső input

**Funkció:** kliensoldali szűrés pályanév és készítő alapján.

**Megjegyzés:** Enter tiltás a véletlen submit elkerülésére.

### J.4.3 Rendezés dropdown

**Opciók:** Downloads, Alphabetical, Most recent, Oldest.

**Hatás:** kártyalista újrarendezése a DOM-ban.

### J.4.4 Add to library gomb

**Backend:** `POST /api/maps action=add_to_library`.

**UI azonnali frissítés:**

1. letöltésszám +1,
2. gomb állapot "Added",
3. stílusváltás.

### J.4.5 Delete / Restore gombok

**Delete:** confirm modal után státuszváltás/törlési ág.

**Restore:** staff visszaállítási ág.

*[KÉP HELYE – J.4 maps gombok számozva]*

## J.5 My Maps oldal – elemenkénti működés

### J.5.1 Rename gomb

**Művelet:** új név validálás, `POST /api/my_maps action=rename_map`.

### J.5.2 Remove gomb

**Művelet:** könyvtárkapcsolat törlése és szükséges letöltésszám-korrekció.

### J.5.3 Publish/Unpublish gombok

**Művelet:** státuszváltás 0/1/3 ágak mentén.

*[KÉP HELYE – J.5 my maps státuszváltás]*

## J.6 Profil oldal – elemenkénti működés

### J.6.1 Avatar gomb és avatar választó

**Művelet:** választott avatar mentése backendbe és header lokális frissítése.

### J.6.2 Username change gomb

**Művelet:** modal input -> backend validáció -> UI frissítés.

### J.6.3 Password change gomb

**Művelet:** régi jelszó ellenőrzés + új hash mentés.

### J.6.4 Logout gomb

**Művelet:** `POST /api/logout`, localStorage/session tisztítás.

*[KÉP HELYE – J.6 profile műveletek]*

## J.7 Admin oldal – elemenkénti működés

### J.7.1 Kereső input

Élő szűrés user kártyákon.

### J.7.2 Ban/Unban gomb

**Folyamat:** target kijelölés -> reason modal -> backend role-check -> státuszváltás.

### J.7.3 Role change gomb

**Folyamat:** confirm modal -> backend promote/demote ág.

### J.7.4 User maps gomb

**Folyamat:** `get_user_maps` -> map modal render -> rename/remove.

### J.7.5 Hard delete gomb (Engineer)

**Folyamat:** erős megerősítés -> visszafordíthatatlan backend művelet.

### J.7.6 Logs gombok

**Folyamat:** logbetöltés -> dátumtartomány szűrés -> részletek lenyitása.

*[KÉP HELYE – J.7 admin gombmátrix]*

\newpage

# Melléklet K – Endpointonkénti kérés-válasz forgatókönyvek

## K.1 `POST /api/login` – 6 tipikus forgatókönyv

### K.1.1 Sikeres belépés

**Request**

```json
{
	"email": "player@example.com",
	"password": "StrongPass123"
}
```

**Response (200)**

```json
{
	"status": "success",
	"user": {
		"username": "Player01",
		"avatar": "data:image/jpeg;base64,..."
	}
}
```

### K.1.2 Hiányzó mező

**Response (400)**

```json
{
	"status": "error",
	"message": "All fields are required!"
}
```

### K.1.3 Hibás hitelesítő

**Response (401)**

```json
{
	"status": "error",
	"message": "Invalid email or password!"
}
```

### K.1.4 Nem verifikált fiók

**Response (403)**

```json
{
	"status": "error",
	"code": "not_verified",
	"message": "Your account is not verified yet."
}
```

### K.1.5 Temp jelszó lejárt

**Response (403)**

```json
{
	"status": "error",
	"code": "temp_password_expired",
	"message": "Your temporary password has expired."
}
```

### K.1.6 Force password change

**Response (403)**

```json
{
	"status": "error",
	"code": "force_password_change",
	"message": "You must change your password before accessing your account.",
	"user_id": 12,
	"username": "Player01"
}
```

*[KÉP HELYE – K.1 login scenario táblázat]*

## K.2 `POST /api/registration` – 5 tipikus forgatókönyv

### K.2.1 Sikeres regisztráció

### K.2.2 Duplikált email/username (409)

### K.2.3 Hibás username minta (400)

### K.2.4 Rövid jelszó (400)

### K.2.5 Lejárt verifikációs kód (403)

Mindegyik ághoz a dokumentációban érdemes request + response párokat képpel együtt megjeleníteni.

*[KÉP HELYE – K.2 registration scenario képek]*

## K.3 `POST /api/game_login` – 4 tipikus forgatókönyv

1. success token issuance,
2. invalid credentials,
3. banned user,
4. method not allowed.

## K.4 `GET /api/game_stats` – 4 tipikus forgatókönyv

1. valid bearer token,
2. missing header,
3. invalid token,
4. banned account.

## K.5 `POST /api/game_update_stats` – 7 tipikus forgatókönyv

1. success with normal delta,
2. success with reset-detected delta,
3. invalid JSON,
4. missing token,
5. invalid token,
6. username mismatch,
7. banned account.

### K.5.1 Példa reset-detect branch

**Előző snapshot:** `Mobs killed = 120`

**Új bejövő érték:** `Mobs killed = 8`

**Értelmezés:** új session, reset történt -> delta = 8, nem negatív korrekció.

*[KÉP HELYE – K.5 delta reset számítási példa]*

## K.6 `GET/POST /api/maps` – 6 tipikus forgatókönyv

1. list maps success,
2. search/sort,
3. add_to_library success,
4. add_to_library already added,
5. delete_map success,
6. restore_map staff-only.

## K.7 `GET/POST /api/my_maps` – 6 tipikus forgatókönyv

1. own + library merged list,
2. rename success,
3. remove success,
4. publish success,
5. unpublish success,
6. unauthorized branch.

## K.8 `GET/POST /api/admin` – 10 tipikus forgatókönyv

1. admin page load,
2. ban user,
3. self-ban blocked,
4. engineer-ban blocked,
5. role change,
6. get user maps,
7. rename map,
8. remove map,
9. hard delete engineer-only,
10. site settings update engineer-only.

*[KÉP HELYE – K.8 admin endpoint scenario mátrix]*

\newpage

# Melléklet L – UI állapotmátrix és részletes képkövetelmények

## L.1 Oldalankénti kötelező állapotképek

### Login

1. üres form,
2. kitöltött form,
3. hibás email,
4. hibás jelszó,
5. not_verified válasz,
6. force_password_change modal,
7. forgot password siker.

### Register

1. alapnézet,
2. validációs hibák,
3. success üzenet,
4. code modal,
5. expired code hiba,
6. verified success.

### Basesite

1. minden tab külön képen,
2. download logged-out hiba,
3. download logged-in ág,
4. patch create form,
5. patch edit mód,
6. patch delete confirm,
7. site settings edit,
8. site settings save success.

### Maps

1. desktop nézet,
2. mobil menü nyitva,
3. keresési találat,
4. üres keresési találat,
5. sort by downloads,
6. sort by recent,
7. add success,
8. delete confirm,
9. trash modal,
10. restore success.

### My Maps

1. own maps,
2. library maps,
3. rename input,
4. publish confirm,
5. unpublish confirm,
6. remove confirm,
7. üres állapot.

### Profile

1. alap profil,
2. avatar modal,
3. username change,
4. password change,
5. logout confirm,
6. rank blokk.

### Leaderboard

1. top10,
2. current user sor,
3. frissítési dátum blokk,
4. tie-break példa.

### Admin

1. user lista,
2. search filter before/after,
3. ban reason modal,
4. role change confirm,
5. user details modal,
6. user maps modal,
7. rename map modal,
8. hard delete modal,
9. logs panel,
10. logs dátum szűrés.

### IsBanned és Guest

1. isBanned teljes oldal,
2. guest fallback oldal,
3. logout from banned state.

*[KÉP HELYE – L.1 képkövetelmény checklista táblázat]*

## L.2 Kódrészlet-képkombináció sablon

Minden fontos funkciónál érdemes ugyanazt a mintát követni:

1. rövid funkciónév,
2. 8-20 soros kódrészlet,
3. működési magyarázat,
4. screenshot a megfelelő UI állapotról,
5. tipikus hiba és megoldása.

Ez a minta nagyban növeli a beadandó szakmai minőségét és a javító tanár számára az átláthatóságot.

## L.3 Plusz terjedelem-növelő, de szakmailag értékes blokkok

1. "Miért így terveztük" mini alfejezet minden fő modul végén.
2. "Tipikus felhasználói hiba" blokk funkciónként.
3. "Fejlesztői hibakeresés" blokk endpointonként.
4. "Refaktor javaslat" blokk controllerenként.
5. "Terhelés és skálázás" rövid javaslatok.

## L.4 Állapotátmenet-táblák (példaminta)

### L.4.1 Patch notes state

| Aktuális állapot | Esemény | Következő állapot | Backend hívás |
|---|---|---|---|
| Idle | Edit click | EditMode | nincs |
| EditMode | Save click | Saving | `POST edit` |
| Saving | Success | Idle | kész |
| Saving | Error | EditMode | hibaág |
| Idle | Delete click | ConfirmOpen | nincs |
| ConfirmOpen | Confirm OK | Deleting | `POST delete` |
| Deleting | Success | Idle | kész |

### L.4.2 Maps add flow state

| Aktuális állapot | Esemény | Következő állapot | UI |
|---|---|---|---|
| NotAdded | Add click | Pending | gomb tiltás/opcionális spinner |
| Pending | Success | Added | felirat: Added, count +1 |
| Pending | AlreadyExists | Added | információs modal |
| Pending | Error | NotAdded | hiba modal |

*[KÉP HELYE – L.4 állapotátmenet táblák vizuálisan]*

\newpage

# Melléklet M – Kódrészlet-tár és mély technikai kommentár

Ebben a mellékletben célzottan sok kódrészlet szerepel, és mindegyikhez rövid, de pontos technikai magyarázat tartozik. A cél az, hogy a dokumentáció ne csak "felsorolás" legyen, hanem ténylegesen visszakövethető műszaki leírás.

## M.1 Router alaplogika – útvonal feldarabolás

```php
$path = $_GET['path'] ?? '';
$path = trim($path, '/');
$segments = ($path === '') ? [] : explode('/', $path);

$route = [
	'segment1' => $segments[0] ?? null,
	'segment2' => $segments[1] ?? null,
	'segment3' => $segments[2] ?? null,
];
```

Magyarázat:

1. A rendszer nem bonyolult framework routert használ, hanem saját egyszerű route-feldolgozást.
2. A `segment1` gyakorlatilag erőforrás-azonosító (pl. `maps`, `profile`, `admin`).
3. A modell könnyen bővíthető, mert új case ág hozzáadása elegendő.

## M.2 API route switch

```php
switch ($route['segment1']) {
	case "main":
		load_controller($data, API_CONTROLLERS . 'mainController.php');
		break;
	case "maps":
		load_controller($data, API_CONTROLLERS . 'mapsController.php');
		break;
	case "profile":
		load_controller($data, API_CONTROLLERS . 'profileController.php');
		break;
	case 'game_login':
		require API_CONTROLLERS . 'gameLoginController.php';
		handleGameLogin();
		break;
	default:
		json_response(['error' => 'API endpoint not found'], 404);
}
```

Magyarázat:

1. A klasszikus REST végpontok és az egyedi game végpontok együtt szerepelnek.
2. A hibatűrés explicit: ismeretlen endpoint azonnal 404.
3. A struktúra vizsgadokumentációban jól mutatja az architektúra tisztaságát.

## M.3 Login – hitelesítés és session létrehozás

```php
$stmt = $pdo->prepare("\n            SELECT u.user_id, u.username, u.password, u.is_verified, u.has_temp_password, u.temp_password_expires, r.role_name, a.avatar_picture \n            FROM `User` u\n            LEFT JOIN `Avatars` a ON u.avatar_id = a.id\n            LEFT JOIN `Roles` r ON u.role_id = r.id\n            WHERE u.email = ?\n        ");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
	$_SESSION['user_id']   = $user['user_id'];
	$_SESSION['username']  = $user['username'];
	$_SESSION['role_name'] = $user['role_name'] ?? 'Player';
	$_SESSION['logged_in'] = true;
}
```

Magyarázat:

1. A backend egy lekérdezéssel hozza az auth + role + avatar adatokat.
2. A `password_verify` bcrypt-hash ellenőrzést jelent.
3. A sessionbe csak sikeres hitelesítés után kerül adat.

## M.4 Login – webes session token táblában

```php
$pdo->exec("CREATE TABLE IF NOT EXISTS `Active_Web_Sessions` (
	`user_id` INT NOT NULL PRIMARY KEY,
	`session_token` VARCHAR(128) NOT NULL,
	`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$webSessionToken = bin2hex(random_bytes(32));
$_SESSION['web_session_token'] = $webSessionToken;

$sessionStmt = $pdo->prepare("INSERT INTO `Active_Web_Sessions` (user_id, session_token) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_token = VALUES(session_token), updated_at = NOW()");
$sessionStmt->execute([$user['user_id'], $webSessionToken]);
```

Magyarázat:

1. Az aktív webes munkamenet szerveroldali nyoma adatbázisban is él.
2. A token újragenerálódik belépéskor.
3. `ON DUPLICATE KEY` miatt felhasználónként mindig egy aktuális rekord marad.

## M.5 Regisztráció – alap validációs szakasz

```php
if (empty($username) || empty($email) || empty($password) || empty($passwordConfirm)) {
	json_response(["status" => "error", "message" => "All fields are required!"], 400);
}

if (strlen($username) < 4 || strlen($username) > 12 || !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
	json_response(["status" => "error", "message" => "Username must be 4-12 characters and only letters/numbers!"], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	json_response(["status" => "error", "message" => "Invalid email format!"], 400);
}
```

Magyarázat:

1. A validáció sorrendje logikus, és használható hibaüzeneteket ad.
2. A regex miatt a username karakterkészlet kontrollált.
3. A dokumentációban ez jól mutatja a biztonság + UX egyensúlyt.

## M.6 Game login – token kiosztás

```php
$token = bin2hex(random_bytes(32));

$updateStmt = $pdo->prepare("UPDATE `User` SET user_token = ?, last_time_online = NOW() WHERE user_id = ?");
$updateStmt->execute([$token, $user['user_id']]);

json_response([
	"status" => "success",
	"message" => "Login successful!",
	"data" => [
		"user_id" => $user['user_id'],
		"username" => $user['username'],
		"token" => $token
	]
], 200);
```

Magyarázat:

1. A játékoldali token külön életciklusú a webes sessiontől.
2. A `last_time_online` frissítés közvetlenül megtörténik.
3. A játékkliens a válaszból teljes auth-csomagot kap.

## M.7 Game update stats – token kivétel több forrásból

```php
$authHeader = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
	$authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
	$authHeader = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
} elseif (function_exists('apache_request_headers')) {
	$requestHeaders = apache_request_headers();
	if (isset($requestHeaders['Authorization'])) {
		$authHeader = trim($requestHeaders['Authorization']);
	}
}

if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
	json_response(["status" => "error", "message" => "Missing or invalid token."], 401);
	return;
}
```

Magyarázat:

1. Ez egy robusztus megoldás, mert több szerverkonfigurációban is működik.
2. A Bearer formátum explicit regex-szel ellenőrzött.
3. Hiányzó tokennél azonnali és egyértelmű 401 válasz megy.

## M.8 Game update stats – anti-cheat ellenőrzés

```php
if (isset($input['username']) && $input['username'] !== $user['username']) {
	json_response(["status" => "error", "message" => "Cheat detected: You cannot modify another player's stats!"], 403);
	return;
}
```

Magyarázat:

1. A tokenhez tartozó user és a payload usernév összevetése anti-cheat kontroll.
2. Nem csak technikai, hanem üzleti integritás-védelem is.

## M.9 Game update stats – snapshot/delta aggregáció

```php
$counterMap = [
	'num_of_story_finished' => ['num_of_story_finished', 'Story finished'],
	'num_of_enemies_killed' => ['num_of_enemies_killed', 'Mobs killed'],
	'num_of_deaths' => ['num_of_deaths', 'Deaths'],
	'score' => ['score', 'Experience points']
];

if ($previousSeen === null) {
	$delta = $incomingValue;
} elseif ($incomingValue >= $previousSeen) {
	$delta = $incomingValue - $previousSeen;
} else {
	$delta = $incomingValue; // reset
}

$newTotal = $previousTotal + max($delta, 0);
```

Magyarázat:

1. Alias kulcsok miatt több kliens JSON forma kompatibilis.
2. Reset-detektálás esetén nem negatív korrekció fut.
3. A totalsor mindig konzisztens marad.

## M.10 Frontend – main.js modul import és header frissítés

```js
import './admin-src/admin.js';
import './basesite-src/basesite.js';
import './leaderboard-src/leaderboard.js';
import './maps-src/maps.js';
import './myMaps-src/myMaps.js';
import './login-src/login.js';
import './register-src/register.js';
import './profile-src/profile.js';
import './isBanned-src/isBanned.js';
```

```js
const username = localStorage.getItem('username');
const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

if (isLoggedIn && username) {
  // login link -> profile avatar block
} else {
  // profile block -> login link restore
}
```

Magyarázat:

1. A moduláris import tiszta fejlesztői struktúrát ad.
2. A header login állapota kliensoldalon azonnal frissíthető.
3. A UX gyorsabbnak érződik, mert nem kell teljes reload minden változáshoz.

## M.11 Frontend – basesite modal capture guard

```js
document.addEventListener('click', (event) => {
	if (
		event.target.closest('#basesite-confirm-close-btn') ||
		event.target.closest('#basesite-confirm-cancel-btn') ||
		event.target.id === 'basesite-confirm-modal'
	) {
		resetPatchDeleteConfirmState();
	}
}, true);
```

Magyarázat:

1. A `true` miatt capture fázisban fut.
2. Így akkor is tisztul az állapot, ha bubbling fázisban másik handler leállítaná a terjedést.
3. Ez konkrétan modal-beragadás jellegű hibákat előz meg.

## M.12 Frontend – maps oldalspecifikus guard

```js
document.addEventListener('click', (event) => {
	if (!document.querySelector('.maps-site')) return;
	// maps-only click handling
});
```

Magyarázat:

1. Mivel egy bundle-ben fut több modul, oldalspecifikus guard kötelező.
2. E nélkül más oldalakon is lefutna a maps click handler.
3. A guard stabilitási és hibamegelőzési kulcspont.

## M.13 Frontend – admin oldalspecifikus guard

```js
document.addEventListener('click', function(event) {
	if (!document.querySelector('.admin-page-shell')) return;
	// admin-only logic
});
```

Magyarázat:

1. Ugyanaz a problémaosztály, mint mapsnél.
2. Ezzel megelőzhető, hogy admin osztályokat tegyen idegen modalokra.

## M.14 Frontend – add to library UI visszacsatolás

```js
if (data.status === 'success') {
	const countSpan = card.querySelector('.dl-number');
	if (countSpan) {
		let current = parseInt(countSpan.textContent.replace(/[^\d]/g, '')) || 0;
		countSpan.textContent = (current + 1).toLocaleString();
		card.setAttribute('data-downloads', current + 1);
	}

	addBtn.classList.remove('maps-add-btn-available');
	addBtn.classList.add('maps-add-btn-added');
	addBtn.dataset.added = 'true';
	addBtn.textContent = 'Added ✔️';
}
```

Magyarázat:

1. Azonnali vizuális visszajelzés történik backend roundtrip után.
2. A gomb és számláló is konzisztensen frissül.
3. A felhasználó számára egyértelmű, hogy sikerült a művelet.

*[KÉP HELYE – M melléklethez kód és UI páros képek]*

\newpage

# Melléklet N – Extra részletes, hosszú kifejtések funkciónként

## N.1 Mi történik pontosan a login gomb után? (hosszú leírás)

A login művelet valójában több egymásra épülő védelmi és állapotkezelési lépésből áll. Először a kliensoldal elküldi az adatokat, de ez még semmit sem garantál. A szerver először is ellenőrzi, hogy a kérés minimálisan értelmezhető-e. Ezután a felhasználó adatainak lekérése történik, amelyben egyszerre szerepel a hitelesítéshez szükséges hash, a szerepkör, valamint az avatar-információ. A hash-ellenőrzés sikerét követően nem azonnal jön a "welcome" ág, hanem további üzleti szabályok: verifikáltság, ideiglenes jelszó állapot, ideiglenes jelszó lejárat.

Ha minden feltétel teljesül, akkor jön létre az érvényes session. A session létrejötte nem csak memóriában történik, hanem az aktív webes session táblában is, ami egy plusz védelmi és monitorozási réteget ad. Ezzel a rendszer képes kezelni azt az esetet is, amikor ugyanaz a user több környezetből próbál belépni, vagy amikor utólag auditálni kell, milyen aktív munkamenet volt érvényben.

## N.2 Miért fontos a reset-detect a statisztikánál?

A játékkliens sok esetben abszolút számlálókat küld, nem pedig eleve deltát. Ez önmagában nem probléma, de szerveroldalról gondot okozhat, ha a számláló új játékmenetnél nulláról indul, mert ilyenkor egy naiv kivonás negatív vagy hibás eredményeket adna. A reset-detect logika pontosan ezt a problémát oldja meg: ha az új bejövő érték kisebb, mint az előző snapshotban látott érték, a szerver úgy értelmezi, hogy új session indult, és a delta közvetlenül a bejövő érték lesz.

Ez azért nagyon jó, mert:

1. nem duplázódik a stat,
2. nem lesz negatív korrekció,
3. új session esetén is természetes marad az aggregáció.

## N.3 Miért kell egyszerre kliens- és szerveroldali validáció?

A kliensoldali validáció gyors, felhasználóbarát és csökkenti a felesleges kéréseket, de nem tekinthető biztonsági védelemnek, mert a kliens manipulálható. A szerveroldali validáció ezzel szemben a tényleges biztonsági kapu. A jó rendszer mindkettőt használja:

1. kliensoldal: azonnali UX visszajelzés,
2. szerveroldal: végső szabályérvényesítés.

Ez a kettős modell látható a regisztráció, login, profile-change, map-műveletek és admin műveletek esetén is.

## N.4 Miért szerepkör-ellenőrzés backend oldalon is?

A frontend bármikor módosítható browser devtools-szal, ezért önmagában nem megbízható határvonal. A backend role-check biztosítja, hogy még akkor se lehessen jogosulatlan műveletet végrehajtani, ha a kliensoldalon valaki láthatóvá tesz egy egyébként rejtett gombot vagy kézzel küld API kérést.

Ez különösen fontos az admin és engineer-only műveleteknél, például:

1. hard delete,
2. role változtatás,
3. site settings végleges mentés,
4. védett szerepkörű userek tiltása.

## N.5 Miért jó a modulonkénti JS szervezés akkor is, ha egy bundle lesz belőle?

A fejlesztői oldalon a moduláris szerkezet olvashatóbb, tesztelhetőbb és karbantarthatóbb. A build pipeline feladata, hogy ezt a sok modult optimális csomaggá alakítsa. Ez a két világ nem ellentmondás, hanem egymást erősítő architektúra:

1. fejlesztéskor moduláris logika,
2. élesben optimalizált betöltés.

## N.6 Miért kulcskérdés a modal állapotkezelés?

A modern felületekben sok megerősítő és figyelmeztető modal van, és ezek callbackekkel dolgoznak. Ha egy callback bent ragad, vagy egy in-progress flag nem nullázódik, akkor a felület "meghaltnak" tűnhet, noha backend oldalon minden rendben. Emiatt a modal state-eket ugyanolyan komolyan kell venni, mint az adatbázis műveleteket.

Az itt használt védelem:

1. capture phase reset,
2. callback nullázás,
3. oldalspecifikus guard,
4. reconcile self-heal.

Ez együtt már megbízhatóan védi a felületet a tipikus beragadásos hibáktól.

## N.7 Miért hasznos a dokumentációban ennyi kódrészlet?

Az ilyen beadandó értékelésénél a legnagyobb gond sokszor az, hogy a dokumentáció szépen fogalmaz, de nem visszakövethető. A konkrét kódrészletek és a hozzájuk tartozó magyarázatok ezt oldják fel:

1. ellenőrizhető, hogy tényleg létezik a leírt logika,
2. látható a döntések technikai háttere,
3. a javító számára gyorsabb az értékelés,
4. a dokumentáció valódi fejlesztői értéket képvisel.

## N.8 Extra hosszú magyarázó blokk – teljes kérés életútja

Egy tipikus kérés életútja a rendszerben így néz ki:

1. böngésző oldalon történik egy esemény (kattintás, submit, dropdown választás),
2. a frontend event handler előkészíti a payloadot,
3. `fetch` elküldi a kérést a megfelelő API route-ra,
4. a router szegmentál és vezérlőt választ,
5. a kontroller validál,
6. a kontroller lekérdez vagy módosít adatot,
7. a kontroller JSON választ ad,
8. a frontend a választ értelmezi és UI állapotot frissít,
9. szükség esetén localStorage/session/header is frissül,
10. a felhasználó azonnali visszajelzést kap alert/confirm/modal formában.

Ez a teljes kör mutatja, hogy a Troxan webalkalmazás nem statikus oldal, hanem valódi eseményvezérelt rendszer, ahol az adat, az állapot és a felület folyamatosan együtt változik.

*[KÉP HELYE – N melléklethez teljes request life-cycle ábra]*

\newpage

# Összefoglalás

A Troxan webalkalmazás fejlesztése során egy teljes értékű, komplex webes platform valósult meg, amely szervesen összekapcsolódik a hozzá tartozó C# Windows játékklienssel. A projekt nem csupán funkcionálisan teljes, hanem módszertanilag is az önálló gondolkodás és a mélységi tudás megszerzésének jó példája: keretrendszer nélküli PHP MVC backend, natív JavaScript frontend, Vite-alapú build pipeline és Tailwind CSS v4 stílusozás mind azt bizonyítják, hogy a modern webfejlesztés alapjai – a keretrendszerektől függetlenül – is képesek rendkívül hatékonyan alkalmazható megoldások létrehozására.

Az alkalmazás fejlesztése során elsajátított ismeretek és megoldott kihívások – a token-alapú játékkliens integráció, az MVC routing implementálása, a biztonsági rétegek tudatos kialakítása, a dinamikus SPA-szerű oldalnavigáció keretrendszer nélkül – mind olyan tapasztalatok, amelyek a valós munkakörnyezetben is közvetlen értéket képviselnek.

A projekt a pillanatnyi formájában is teljesen működőképes és élesben futó alkalmazás; a fentiekben összefoglalt továbbfejlesztési irányok megvalósítása esetén egy még gazdagabb, még professzionálisabb platformmá válhat.

\newpage

# Források

1. PHP Foundation – *PHP 8 Documentation* – https://www.php.net/docs.php – (2025)
2. MySQL AB – *MySQL 8.0 Reference Manual* – https://dev.mysql.com/doc/refman/8.0/en/ – (2025)
3. Vite Contributors – *Vite Documentation* – https://vitejs.dev/guide/ – (2025)
4. Tailwind CSS Contributors – *Tailwind CSS v4 Documentation* – https://tailwindcss.com/docs – (2025)
5. PHPMailer Contributors – *PHPMailer Documentation* – https://github.com/PHPMailer/PHPMailer – (2025)
6. OWASP Foundation – *OWASP Top Ten* – https://owasp.org/www-project-top-ten/ – (2025)
7. MDN Web Docs – *JavaScript Reference* – https://developer.mozilla.org/en-US/docs/Web/JavaScript – (2025)
