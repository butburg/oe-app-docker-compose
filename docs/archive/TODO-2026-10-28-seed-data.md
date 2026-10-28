# ToDo (Stand 2026-10-28, Branch `feat/static-seed-data`)

Vorherige Liste: [archive/TODO-2026-10-28-ui-review.md](archive/TODO-2026-10-28-ui-review.md)

## 1. Server (manuell, @butburg)
- [ ] Nach dem nächsten Deploy die alten statischen Bilder im Server-Storage löschen (`starfish.svg broken.png impressum-contact.png impressum-mail.png blue.jpg placeholder.jpg` in `storage/app/public/files/images`). `factory_images/` bleibt. Details in der vorherigen Liste.

## 2. Statischer Seed-Datensatz
- [x] Alte lokale DB und Post-Bilder nach `archiv/legacy_2026-10-28_*` verschoben
- [x] 31 frei lizenzierte Bilder (CC0, Wikimedia Commons), Seitenverhältnisse wie im Altbestand (4:3, 9:16, 16:9, 3:4, 1:1, Panoramen), verschiedene Größen, Quellen und Lizenz im Repo vermerken
- [x] Nutzer: „Test-Admin User" (3 veröffentlicht), „Test-User ohne Bilder" (0), „Test-User mit ganz vielen Bildern" (14 veröffentlicht, 14 Entwürfe)
- [x] 31 Kommentare verteilt 10/8/6/4/2/1, Rest ohne
- [x] Seeder baut Bild-Varianten über `CreateImageVariants`, alte Factory-Seeder ersetzen (Daten in `src/database/seeders/data/`, Quellen in `posts.json`)
- [x] Legacy-Import entfernen (`ConvertBlobImages`, `MigrateLegacyImages`, `AssignUserIdToPosts`, Migration `migrate_old_data_to_posts_table` + DB-Verbindung `old_sqlite`), Skill `local-preview-data` anpassen
- [x] README, Skill und Deploy nachgezogen (Seed-Daten dokumentiert, `database/seeders/data/` wird nicht mit deployed)
