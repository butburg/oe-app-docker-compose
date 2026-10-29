# ToDo (Stand 2026-10-29, Branch `feat/05-image-links-slugs-mails`)

Vorherige Liste: [archive/TODO-2026-10-28-seed-data.md](archive/TODO-2026-10-28-seed-data.md)

## 1. Server (manuell, @butburg)
- [ ] Nach dem nächsten Deploy die alten statischen Bilder im Server-Storage löschen (`starfish.svg broken.png impressum-contact.png impressum-mail.png blue.jpg placeholder.jpg` in `storage/app/public/files/images`). `factory_images/` bleibt. Details in [archive/TODO-2026-10-28-ui-review.md](archive/TODO-2026-10-28-ui-review.md).
- [ ] Deploy von Branch 05: nginx-Image neu bauen (`docker compose build app`), Migrationen laufen lassen, Service `queue` läuft (nach jedem Code-Update `docker compose restart queue`).
- [ ] Danach `php artisan images:strip-metadata --dry-run`, dann ohne `--dry-run` (FEAT-015).
- [ ] php-fpm: `docker compose logs php | grep max_children` prüfen; bei Bedarf `pm.max_children` erhöhen (≈ freier RAM für PHP ÷ 80 MB, nicht mehr als 2–4 pro CPU-Kern), siehe FEAT-011.

## 2. Manuell auf Dev testen (FEAT-011 bis 019, @butburg)
Setup: `docker compose up -d`, `docker compose run --rm artisan migrate`. Logins über die Developer-Buttons auf der Login-Seite, Mails in MailHog: http://localhost:8025. Alle Mails mit Beispieldaten: http://localhost/dev/mail.

**FEAT-011 Bildlinks**
- [ ] Galerie, My Images, Profile und Zoom zeigen alle Bilder; URLs sehen aus wie `/img/{id}/{token}`.
- [ ] Bild-URL direkt im neuen Tab geht; `/storage/files/posts/images/s/1.jpeg` gibt 404.
- [ ] Bild-URL kopieren, Post sensitive / Draft machen oder neues Bild hochladen, kopierte URL öffnen: 404. Gleiches beim Profilbild.

**FEAT-014 Anker**
- [ ] Share-Button kopiert `/?page=N#post-titel-des-posts`, der Link scrollt zum Post.
- [ ] "View in gallery" in My Images und auf Profilen springt zum richtigen Post.
- [ ] Draft umbenennen: Anker folgt dem Titel; nach dem Veröffentlichen bleibt er.

**FEAT-015 EXIF/GPS**
- [ ] Echtes Handyfoto mit Standort hochladen, gespeichertes Bild über die Bild-URL laden und mit einem EXIF-Viewer prüfen: kein GPS, keine Kameradaten.
- [ ] Hoch- und Querformat-Fotos vom Handy stehen richtig herum. Profilbild ebenso ohne EXIF.

**FEAT-016 Mail-Layout**
- [ ] `/dev/mail`: alle Mails sehen gut aus. Registrierung mit neuer Adresse: Mail in MailHog mit Logo, Wortmarke, Farben.

**FEAT-017 Announcements**
- [ ] Als Admin: Menü zeigt "Users" und "Announcements", "Admin" unter dem Namen.
- [ ] Announcement mit Markdown und allen Platzhaltern (`{name}`, `{email}`, `{posts}`, `{comments}`, `{website}`) schreiben; Vorschau zeigt die eigenen Werte.
- [ ] "Send test to me" kommt an.
- [ ] "Send to N users" fragt nach; jeder User bekommt seine eigene Mail mit seinen Werten; nochmal absenden (Zurück + Senden) schickt nicht doppelt.
- [ ] Gruppen: alle verifizierten, alle inkl. unverifizierte (Unverified-Account bekommt sie), einzelner User. Liste "Sent mails" zeigt den Versand.
- [ ] Als normaler User: kein "Announcements"-Link, `/admin/mails` leitet weg.

**FEAT-018 Kommentar-Mails**
- [ ] User A kommentiert ein Bild von User B: B bekommt eine Mail (Name, Titel, Auszug, Button zum Post).
- [ ] Kommentar am eigenen Bild: keine Mail. Schalter "Comments on my images" aus: keine Mail.
- [ ] Link "Change mail settings" ausgeloggt öffnen: Login, danach landet man im Profil-Abschnitt.

**FEAT-019 Mails zu neuen Bildern**
- [ ] Zum schnellen Testen `MAIL_NEW_IMAGES_QUIET_MINUTES=1` in `src/.env` setzen und `docker compose restart queue` (danach zurücksetzen).
- [ ] Zwei Drafts kurz nacheinander veröffentlichen: nach der Wartezeit eine Mail pro anderem verifizierten User mit beiden Titeln; der Uploader bekommt keine.
- [ ] Post zum Draft machen und wieder veröffentlichen: keine neue Mail. Schalter "New images" aus: keine Mail.
- [ ] Mail hat keine Bilder (nur das Logo) und keine Links außer Galerie und Einstellungen.

- [ ] Alles geprüft; gefundene Probleme behoben oder als Ticket angelegt.
