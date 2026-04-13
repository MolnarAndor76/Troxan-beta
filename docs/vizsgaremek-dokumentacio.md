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

# Alkalmazott technológiák

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

# A rendszer architektúrája

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

# Az alkalmazás funkcióinak ismertetése

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

# Összefoglalás

A Troxan webalkalmazás fejlesztése során egy teljes értékű, komplex webes platform valósult meg, amely szervesen összekapcsolódik a hozzá tartozó C# Windows játékklienssel. A projekt nem csupán funkcionálisan teljes, hanem módszertanilag is az önálló gondolkodás és a mélységi tudás megszerzésének jó példája: keretrendszer nélküli PHP MVC backend, natív JavaScript frontend, Vite-alapú build pipeline és Tailwind CSS v4 stílusozás mind azt bizonyítják, hogy a modern webfejlesztés alapjai – a keretrendszerektől függetlenül – is képesek rendkívül hatékonyan alkalmazható megoldások létrehozására.

Az alkalmazás fejlesztése során elsajátított ismeretek és megoldott kihívások – a token-alapú játékkliens integráció, az MVC routing implementálása, a biztonsági rétegek tudatos kialakítása, a dinamikus SPA-szerű oldalnavigáció keretrendszer nélkül – mind olyan tapasztalatok, amelyek a valós munkakörnyezetben is közvetlen értéket képviselnek.

A projekt a pillanatnyi formájában is teljesen működőképes és élesben futó alkalmazás; a fentiekben összefoglalt továbbfejlesztési irányok megvalósítása esetén egy még gazdagabb, még professzionálisabb platformmá válhat.

\newpage

# Irodalomjegyzék

1. PHP Foundation – *PHP 8 Documentation* – https://www.php.net/docs.php – (2025)
2. MySQL AB – *MySQL 8.0 Reference Manual* – https://dev.mysql.com/doc/refman/8.0/en/ – (2025)
3. Vite Contributors – *Vite Documentation* – https://vitejs.dev/guide/ – (2025)
4. Tailwind CSS Contributors – *Tailwind CSS v4 Documentation* – https://tailwindcss.com/docs – (2025)
5. PHPMailer Contributors – *PHPMailer Documentation* – https://github.com/PHPMailer/PHPMailer – (2025)
6. OWASP Foundation – *OWASP Top Ten* – https://owasp.org/www-project-top-ten/ – (2025)
7. MDN Web Docs – *JavaScript Reference* – https://developer.mozilla.org/en-US/docs/Web/JavaScript – (2025)
