# Review PR #5: Image links, slugs, EXIF, mails

**Gesamturteil: 6,5/10.** Die Tests sind breit und der Code ist größtenteils sauber. Es gibt aber zwei echte Logikfehler im Mail-Bundle-System und zwei Sicherheits- bzw. UX-Lücken im Auth-Flow. Die solltest du vor dem Merge beheben, denn der Merge deployt direkt auf Prod.

---

## 🔴 Kritisch, vor dem Merge beheben

**1. Ein fehlgeschlagener Bundle-Job sperrt den User für immer**
Betroffen sind [CollectMail.php:75](src/app/Actions/CollectMail.php#L75) und [SendMailBundle.php](src/app/Jobs/SendMailBundle.php).
- `collect()` startet nur dann einen Job, wenn für den User noch nichts in `pending_mails` liegt.
- Wenn der Job endgültig scheitert, bleiben seine Einträge liegen. Danach bekommt der User keinen Job mehr. Das passiert bei einem SMTP-Fehler nach den Retries, bei einem geleerten `jobs`-Table oder wenn der Worker nie lief und die Jobs verworfen werden.
- Der Fall ist wahrscheinlich: `--tries=3 --backoff=60` steht nur im docker-compose-Entrypoint. Ein Worker auf lima-city mit dem Standard `--tries=1` sperrt den User schon nach einem einzigen SMTP-Aussetzer.
- **Fix:** `public $tries = 3; public $backoff = 60;` direkt in die Job-Klasse schreiben. Dazu eine `failed()`-Methode, die die Einträge löscht oder neu dispatcht. Oder auf `ShouldBeUnique` mit `uniqueId() = userId` umstellen und immer dispatchen, statt die `$waiting`-Logik zu nutzen.
- ✅ **Erledigt (de6122c):**
  - `$tries = 3` und `$backoff = 60` stehen jetzt in der Job-Klasse.
  - `collect()` zählt einen User nur noch als wartend, solange sein ältester Eintrag nicht älter ist als `bundle_minutes + GRACE_MINUTES` (30). Ist er überfällig, startet der nächste Eintrag einen neuen Job, und das liegengebliebene Bundle geht mit raus.
  - Bewusst nicht umgesetzt:
    - `failed()` löscht keine Einträge, weil dabei Mails still verloren gingen.
    - Es gibt keinen Sweep für User, die nach einem Fehlschlag gar keinen neuen Eintrag bekommen. Wir gehen von funktionierenden Servern aus, das ist ein Einzelfall, und `queue:retry all` hilft dann von Hand.

**2. Mit `QUEUE_CONNECTION=sync` bricht das Kommentieren und Veröffentlichen**
Betroffen ist [SendMailBundle.php:36-49](src/app/Jobs/SendMailBundle.php#L36-L49). Die PR-Beschreibung sagt selbst „kein Worker auf dem Server“. Auf `sync` umzustellen wäre der naheliegende schnelle Workaround, und dann passiert Folgendes:
- Der Job läuft sofort.
- `$due->isFuture()` ist wahr, also dispatcht der Job sich selbst noch einmal, synchron und innerhalb des noch gehaltenen `Cache::lock`.
- Der innere Aufruf wartet 10 s auf den Lock, dann kommt eine `LockTimeoutException`. Der Kommentar- bzw. Publish-Request endet mit 500.
- **Fix:** Mindestens in `.env.example` und im TODO deutlich davor warnen, oder bei `sync` gezielt abbrechen.
- ✅ **Erledigt, ohne Codeänderung:**
  - `sync` wird nicht genutzt. Auf dem Server startet ein lima-Shell-Cronjob jede Minute `queue:work --stop-when-empty`, wie der `queue`-Container lokal. Die Anleitung steht in [TODO.md](TODO.md) und in der README.
  - In `.env.example` und im Docblock von `SendMailBundle` steht der Hinweis „nicht `sync`“.
  - Bewusst kein Schutz für `sync` im Job, denn wir gehen von funktionierender Infrastruktur aus.

**3. GET-Bestätigungslinks werden von Mail-Scannern „verbraucht“**
Outlook Safe Links, Defender und manche Gmail-Prefetcher rufen Links aus Mails automatisch auf.
- [RegisteredUserController.php:131](src/app/Http/Controllers/Auth/RegisteredUserController.php#L131): Der Scanner legt per GET das Konto an und loggt sich in *seiner* Session ein. Klickt der User danach selbst, sieht er „Link ungültig“, obwohl sein Konto schon existiert.
- [ProfileController.php:142](src/app/Http/Controllers/ProfileController.php#L142): Hier gilt dasselbe. Beim zweiten Klick steht da „Your email was not changed“, obwohl die E-Mail längst geändert ist. Die Meldung ist schlicht falsch.
- **Fix:** Per GET nur eine Seite mit Button zeigen, die eigentliche Aktion per POST ausführen. Für die E-Mail-Änderung zusätzlich prüfen: Wenn `sha1($user->email) === $hash`, die Erfolgsmeldung zeigen.
- ✅ **Registrierung erledigt (FEAT-021):** Der Link zeigt eine Willkommensseite mit Button, erst der POST legt das Konto an.
- ✅ **E-Mail-Änderung erledigt (FEAT-021):** Auch hier zeigt der Link nur eine Seite mit Button, erst der POST wechselt die E-Mail. Ein schon benutzter Link sagt „Your email is already changed to …“ statt der falschen Fehlermeldung.

**4. `POST /register` hat kein Throttle**
Betroffen ist [routes/auth.php:19](src/routes/auth.php#L19).
- Früher verhinderte die Unique-Regel auf der E-Mail mehrere Mails an dieselbe Adresse. Jetzt verschickt jeder Submit eine Mail an eine beliebige Adresse (`AccountAlreadyExists` oder `ConfirmRegistration`). Das ist eine Mail-Bombe und schadet der SMTP-Reputation.
- Nebenbei löscht jeder Submit die laufende Registrierung des Opfers.
- **Fix:** `->middleware('throttle:6,1')`, besser ein RateLimiter pro E-Mail-Adresse.
- ✅ **Erledigt:**
  - Pro Adresse gehen höchstens 7 Mails pro Stunde raus (`PendingRegistration::MAILS_PER_HOUR`). Das gilt für Registrieren, Resend und E-Mail-Ändern zusammen, denn auch Resend und E-Mail-Ändern ließen vorher etwa 180 Mails pro Stunde an eine fremde Adresse zu.
  - Das Limit wird geprüft, bevor sich etwas ändert. Ein geblockter Submit löscht die laufende Registrierung des Opfers also nicht.
  - `POST /register` hat zusätzlich einen benannten Limiter `register` mit 6 pro Minute pro IP. Ein schlichtes `throttle:6,1` hätte sich den Zähler mit Resend geteilt.

---

## 🟠 Hoch

**5. FEAT-011 schützt aktuell nichts, kostet aber spürbar Performance**
- Die Dateien liegen weiter unter `/storage/files/posts/images/{size}/{image_id}.jpeg`, und `image_id` ist fortlaufend. Damit sind alle Bilder durchzählbar, auch sensitive Posts und Drafts. Token und `public_id` bringen nichts, solange die Dateien nicht auf die private Disk umziehen. Das steht zwar im PR, aber eher als Randnotiz.
- Gleichzeitig ist jetzt jedes Bild ein eigener PHP-FPM-Request mit komplettem Laravel-Boot und einer DB-Query ([ImageController.php:16](src/app/Http/Controllers/ImageController.php#L16)). Auf Shared Hosting mit wenigen PHP-Prozessen kostet eine Galerieseite mit 20 bis 40 Bildern deutlich mehr.
- Die Galerie lädt außerdem nichts vor ([PostController.php:72](src/app/Http/Controllers/PostController.php#L72)), es fehlt `with('image.variants', ...)`. `Image::url()` lädt die `variants` deshalb pro Bild einzeln nach. Das N+1 gab es schon vorher, jetzt fällt es stärker ins Gewicht.
- ✅ **Erledigt:**
  - Die Bilder liegen jetzt auf der eigenen Disk `images` (`storage/app/images`), außerhalb von `public/`. Erreichbar sind sie nur noch über `/img/{variant}/{token}`.
  - `images:move-private` verschiebt die vorhandenen Dateien. Der Befehl ist idempotent und läuft bei jedem Deploy nach `storage:link` mit.
  - Die Galerie, „My images“, die Admin-Liste und das Profil laden Bild, Varianten, User und Kommentare vor. Eine Galerieseite mit 12 Posts und je 3 Kommentaren braucht 21 statt 206 Queries.
  - Der PHP-Request pro Bild bleibt, er ist der Preis für den Schutz. Lazy Loading und der private Browser-Cache fangen ihn ab.

**6. Die Mail-Rate wird nur innerhalb eines `collect()`-Aufrufs verteilt**
Betroffen ist [CollectMail.php:86](src/app/Actions/CollectMail.php#L86).
- Neu gestartete Jobs aus `handle()` und gleichzeitige Aufrufe (Announcement, neues Bild und Kommentare zugleich) werden nicht mitgezählt. Die Zusage „SMTP-Limit wird nie erreicht“ stimmt also nicht.
- Laravel 11 hat dafür Bordmittel: `RateLimited`-Job-Middleware mit `Limit::perMinute(30)` statt der eigenen Delay-Rechnung, und `WithoutOverlapping($userId)` statt `Cache::lock`. Das wäre einfacher und korrekt.
- ✅ **Erledigt, anders als vorgeschlagen:**
  - Das Limit setzt jetzt der Worker: Der Cronjob läuft jede Minute mit `--max-jobs=30`, ein Job verschickt höchstens eine Mail. Damit gilt das Limit für alle Wege (gleichzeitige Aufrufe, Rückstau vor dem ersten Lauf, neue Jobs aus `handle()`).
  - Die Verteilung in `collect()` und `MAIL_PER_MINUTE` sind entfernt.
  - Gegen `RateLimited` entschieden: Ein zurückgestellter Job zählt als Versuch, dafür hätte `$tries` durch `retryUntil()` und `$maxExceptions` ersetzt werden müssen. Das ist mehr Code für dieselbe Wirkung.
  - `Cache::lock` bleibt, er funktioniert.

---

## 🟡 Mittel

**7. Die Migration hängt am App-Model**
[add_slug_to_posts_table.php:25](src/database/migrations/2026_10_29_000003_add_slug_to_posts_table.php#L25) ruft `Post::uniqueSlug()` auf. Ändert sich das Model später, verhält sich diese alte Migration anders oder bricht. Best Practice ist, die Logik in die Migration zu kopieren.
- ✅ **Bewusst nicht umgesetzt:**
  - Die Migration läuft auf Prod genau einmal beim Merge, mit dem Model aus diesem PR. Danach entstehen Slugs nur noch beim Veröffentlichen.
  - Bei frischen Datenbanken (Tests, lokales Setup) ist `posts` leer, `uniqueSlug()` wird also gar nicht aufgerufen.
  - Probleme gäbe es nur, wenn ein Dump von vor dieser Migration mit späterem Code migriert wird. Alte Dumps spielen wir nicht ein.
  - Dasselbe gilt für `ImageVariant::newPublicId()` in [add_public_id_to_image_variants_table.php:22](src/database/migrations/2026_10_29_000007_add_public_id_to_image_variants_table.php#L22).

**8. Im Plain-Text-Teil der Bundle-Mail sind Markdown-Escapes sichtbar**
Ich habe das im Container gerendert:
```
> Wow\! \*great\* line1
line2
===
```
- [EscapesMarkdown.php:11](src/app/Mail/Concerns/EscapesMarkdown.php#L11) escapt auch für den Text-Teil, obwohl der nie als Markdown geparst wird.
- Zeilenumbrüche im Kommentar werden nicht behandelt. Mehrzeilige Kommentare rutschen aus dem Blockquote. Mit `===` oder 4 Leerzeichen Einrückung lässt sich in der HTML-Mail trotzdem Formatierung erzeugen, Links aber nicht.
- ✅ **Erledigt:**
  - Die Screenshots zeigten nur den HTML-Teil. Der Text-Teil wird nicht als Markdown geparst, deshalb blieben dort die Backslashes stehen, auch in Namen und Titeln.
  - `plainText()` macht aus HTML- und Markdown-Zeichen jetzt Entities statt Backslashes. Markdown zeigt sie als normalen Text, der Text-Teil dekodiert sie.
  - Zeilenumbrüche im User-Text werden zu Leerzeichen, damit nichts mehr aus dem Zitat fällt. Die Formatierung über Leerzeilen (`===`, Einrückung) geht damit nicht mehr.

**9. Eigenes `Post::save()` für die Slug-Race**
Betroffen ist [Post.php:57](src/app/Models/Post.php#L57).
- Bei der Größe der App ist die Race praktisch ausgeschlossen, das ist Over-Engineering.
- Der Retry hängt an `str_contains($e->getMessage(), 'slug')`, und das ist fragil. Unter Postgres wäre die Transaktion nach dem Fehler ohnehin abgebrochen.
- Mein Vorschlag: das Override entfernen und den Unique-Index die seltene Kollision mit 500 abfangen lassen.
- ✅ **Erledigt wie vorgeschlagen:**
  - Das `save()`-Override ist entfernt. `uniqueSlug()`, der `saving`-Hook und der Unique-Index bleiben.
  - Bei einer Kollision bekommt ein User eine 500-Seite. Slug und `published_at` stehen im selben UPDATE, es wird also nichts halb gespeichert. Ein zweiter Klick auf Veröffentlichen gibt den Slug `…-2`.
  - Der Postgres-Einwand trifft nicht zu, weil Prod MySQL und Dev SQLite nutzt.

---

## ⚪ Niedrig

- **Gleichzeitiges Doppel-Submit beim Admin-Senden:** Beide Requests bestehen den `exists()`-Check in [MailController.php:82](src/app/Http/Controllers/Admin/MailController.php#L82), der zweite endet mit 500 (UniqueConstraint). Doppelt gesendet wird nichts, aber die Fehlerseite ist unschön.
  - ✅ **Bewusst nicht umgesetzt:** Der Bestätigungsdialog und der deaktivierte Button lassen praktisch keinen zweiten Request durch. Selbst dann geht nichts doppelt raus, und der Unique-Index fängt den Rest ab.
- **Vorschau ohne Redirect nach dem POST:** Lädt der Admin nach „Send test to me“ die Seite neu, geht die Testmail noch einmal raus.
  - ✅ **Bewusst nicht umgesetzt:** Der Browser fragt vor dem erneuten Senden nach, und die Testmail geht nur an den Admin selbst. Ein Redirect bräuchte eine GET-Route für die Vorschau und die Formulardaten in der Session, das lohnt sich dafür nicht.
- **Lowercase-Logik doppelt und uneinheitlich:** Sie steckt in `RegisteredUserController::lowercaseEmail()` und in `LoginRequest::prepareForValidation()`. [ProfileUpdateRequest.php:27](src/app/Http/Requests/ProfileUpdateRequest.php#L27) lehnt Großbuchstaben dagegen weiter per `lowercase`-Regel ab.
  - ✅ **Erledigt:** Auch das Profil wandelt Großbuchstaben jetzt um, statt sie abzulehnen. Die `lowercase`-Regel ist raus, dazu gibt es einen Test.
  - ✅ **Doppelte Logik bewusst gelassen:** Es sind drei Kopien von je drei Zeilen. Login und Profil sind FormRequests, die Registrierung arbeitet mit einem einfachen `Request`, und dort läuft `prepareForValidation()` nicht. Zusammenlegen ginge nur, wenn man die Registrierung auf FormRequests umbaut.
- **Seiteneffekte in Models:** `PendingRegistration::sendLink()` und `User::sendEmailChangeLink()` versenden Mails direkt aus dem Model. Dazu kommt `app(CollectMail::class)` in den Controllern statt Method Injection. Das ist kein Bug, aber nicht der übliche Laravel-Stil.
  - ✅ **Mails aus Models bewusst so belassen:** Token, Ablaufzeit, Limit und Mail gehören fachlich zusammen und werden von mehreren Controller-Aktionen gebraucht. Laravel macht das selbst genauso: `sendEmailVerificationNotification()` und `sendPasswordResetNotification()` sitzen am `User`. Action-Klassen oder Events wären mehr Klassen ohne Änderung am Verhalten.
  - ✅ **Method Injection erledigt:** `CommentController::store()` und `PostController::publish()` bekommen `CollectMail` jetzt als Parameter, so wie `MailController::send()`.
- **Division durch null:** `IMAGES_LINK_WINDOW_DAYS=0` führt in [Image.php:51](src/app/Models/Image.php#L51) zu einem `DivisionByZeroError`. `max(1, …)` in der Config behebt das.
  - ✅ **Erledigt wie vorgeschlagen:** In [config/images.php:16](src/config/images.php#L16) steht jetzt `max(1, …)`. Ein leerer Wert oder ein Tippfehler wird durch `(int)` ebenfalls zu 0 und landet jetzt bei 1 Tag.
- **Neu-Kodierung bei `images:strip-metadata`:** Der Befehl kodiert jedes betroffene JPEG neu und verliert dabei einmalig etwas Qualität. Vor dem Lauf auf Prod sollte man das wissen.