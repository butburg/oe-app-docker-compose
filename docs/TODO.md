# ToDo (Stand 2026-10-29, Branch `feat/05-image-links-slugs-mails`)

Vorherige Liste: [archive/TODO-2026-10-28-seed-data.md](archive/TODO-2026-10-28-seed-data.md)

## 1. Server (manuell, @butburg)
- [x] Nach dem nächsten Deploy die alten statischen Bilder im Server-Storage löschen (`starfish.svg broken.png impressum-contact.png impressum-mail.png blue.jpg placeholder.jpg` in `storage/app/public/files/images`). `factory_images/` bleibt. Details in [archive/TODO-2026-10-28-ui-review.md](archive/TODO-2026-10-28-ui-review.md).
- [ ] Deploy von Branch 05: Frontend-Build und Migrationen macht der GitHub-Workflow. Vorher DB-Dump und Sicherung von `storage/app/public/files`. `MAIL_BUNDLE_MINUTES`, `MAIL_PER_MINUTE`, `IMAGES_*` müssen nicht in die Secrets, die Defaults passen.
- [ ] Queue-Worker auf dem Server fehlt (kein Docker, kein `crontab`): ohne ihn bleiben die Sammel-Mails in `jobs` liegen. Lösung (lima-Cronjob) folgt.
- [ ] Bilder liegen noch auf der `public`-Disk, `/storage/files/...` ist direkt erreichbar (FEAT-011). Umzug auf die private Disk folgt.
- [ ] Danach per SSH in `~/oe-app`: `php artisan images:strip-metadata --dry-run`, dann ohne `--dry-run` (FEAT-015). Überschreibt Dateien mit Metadaten.

## 2. Manuell auf Dev testen (FEAT-011 bis 020, @butburg)
Setup: `docker compose up -d`, `docker compose run --rm artisan migrate`. Logins über die Developer-Buttons auf der Login-Seite, Mails in MailHog: http://localhost:8025. Alle Mails mit Beispieldaten: http://localhost/dev/mail.

**FEAT-011 Bildlinks**
- [x] Galerie, My Images, Profile und Zoom zeigen alle Bilder; URLs sehen aus wie `/img/{zufalls-id}/{token}`, z.B. `/img/04b5965df9ad034b/bb40…`: keine fortlaufende Nummer mehr (vorher `docker compose run --rm artisan migrate`).
- [x] Bild-URL direkt im neuen Tab geht (gewollt, der Link selbst ist der Zugang). Der alte Dateipfad http://localhost/storage/files/posts/images/s/1.jpeg gibt 404 (diesen von Hand eintippen, die App zeigt ihn nirgends mehr).
- [x] Bild-URL kopieren, Post sensitive / Draft machen oder neues Bild hochladen, kopierte URL öffnen: 404. Profilbild: Link kopieren, **neues Profilbild hochladen**, alten Link öffnen: 404. (Ohne neues Bild bleibt der Link bis zu 2 Wochen gültig, das ist gewollt.)

**FEAT-014 Anker**
- [x] Share-Button kopiert `/?page=N#post-titel-des-posts`, der Link scrollt zum Post.
- [x] "View in gallery" in My Images und auf Profilen springt zum richtigen Post.
- [x] Draft anlegen, umbenennen, veröffentlichen: Anker kommt vom Titel beim Veröffentlichen. Admin benennt den Post danach um: Anker bleibt.

**FEAT-015 EXIF/GPS**
- [x] Echtes Handyfoto mit Standort hochladen, gespeichertes Bild über die Bild-URL laden und mit einem EXIF-Viewer prüfen: kein GPS, keine Kameradaten.
- [x] Hoch- und Querformat-Fotos vom Handy stehen richtig herum. Profilbild ebenso ohne EXIF.

**FEAT-016 Mail-Layout**
- [x] `/dev/mail` schmal (Handy-Breite) und breit: Box hat Rand zum Bildschirm, Schrift ist Figtree.
- [x] Mail in MailHog: Logo und Figtree sind da, obwohl nichts nachgeladen wird (Logo hängt als Inline-Bild an, Schrift steckt im HTML).
- [x] Website: Schrift ist weiterhin Figtree; in den DevTools (Network) gehen keine Anfragen an fremde Server (vorher fonts.bunny.net und lima-city-Banner).
- [x] `/dev/mail`: alle Mails sehen gut aus. Registrierung mit neuer Adresse: Mail in MailHog mit Logo, Wortmarke, Farben.
- [x] Footer: "You get this mail because you have an account on Weedy Gallery."; Registrierung und neue E-Mail: "… because this address was entered on Weedy Gallery."

**FEAT-017 Announcements**
- [x] Als Admin: Menü zeigt "Users" und "Announcements", "Admin" unter dem Namen.
- [x] Announcement mit Markdown und allen Platzhaltern (`{name}`, `{email}`, `{posts}`, `{comments}`, `{website}`) schreiben; Vorschau zeigt die eigenen Werte.
- [x] "Send test to me" kommt an.
- [x] "Send to N users" fragt nach; jeder User bekommt sie mit seiner nächsten Sammel-Mail (FEAT-020) mit seinen Werten; nochmal absenden (Zurück + Senden) schickt nicht doppelt.
- [x] Gruppen: alle verifizierten, alle inkl. unverifizierte (Unverified-Account bekommt sie), einzelner User. Liste "Sent mails" zeigt den Versand.
- [x] Als normaler User: kein "Announcements"-Link, `/admin/mails` leitet weg.

**FEAT-018 Kommentar-Mails**
- [x] User A kommentiert ein Bild von User B: B bekommt es mit der Sammel-Mail (Name, Titel, Auszug, Link zum Post).
- [x] Kommentar am eigenen Bild: keine Mail. Schalter "Comments on my images" aus: keine Mail.
- [x] Link "Change mail settings" ausgeloggt öffnen: Login, danach landet man im Profil-Abschnitt.

**FEAT-019 Mails zu neuen Bildern**
- [x] Zum schnellen Testen die Wartezeit in `src/.env` auf 1 setzen (heißt jetzt `MAIL_BUNDLE_MINUTES`, siehe FEAT-020).
- [x] Zwei Drafts kurz nacheinander veröffentlichen: nach der Wartezeit eine Mail pro anderem verifizierten User mit beiden Titeln; der Uploader bekommt keine.
- [x] Post zum Draft machen und wieder veröffentlichen: keine neue Mail. Schalter "New images" aus: keine Mail.
- [x] Mail hat keine Bilder (nur das Logo) und keine Links außer Galerie und Einstellungen.
- [x] Neuer Text ("Hey! … just uploaded 3 new images … Go check them out") und kurzer Footer gefallen (jetzt Teil der Sammel-Mail).

**FEAT-020 Eine Sammel-Mail pro User** (Vorschau: http://localhost/dev/mail/bundle-everything)
- [x] Mit `MAIL_BUNDLE_MINUTES=1`: User B kommentiert zwei Bilder von User A, User C veröffentlicht ein Bild, Admin schickt ein Announcement. Nach ca. 1 Minute bekommt A **eine** Mail mit allem, Betreff z.B. `Announcement · 2 new comments on your images · C published 1 new image`.
- [x] Danach ein neuer Kommentar: neue Mail erst nach Ablauf eines neuen Fensters.
- [x] Nur Announcement (Schalter im Profil aus): Mail ohne "Open the gallery"-Button und ohne Settings-Link.
- [x] "Send test to me" kommt sofort, nicht gesammelt. Registrierungs-Mails kommen sofort.
- [x] Profil → Mail notifications erklärt das Sammeln; Admin-Vorschau sagt "at the latest in N minutes".

- [x] Alles geprüft; gefundene Probleme behoben oder als Ticket angelegt.
