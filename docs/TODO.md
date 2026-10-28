# ToDo (Stand 2026-10-28)

## 1. Datenbank
- [x] Secret `ENV_VARIABLES` prüfen: `DB_CONNECTION=mysql` gesetzt? JAA
- [x] `database/database.sqlite` auf dem Server löschen ERLEDIGT (`old_database.sqlite` wird beim nächsten Deploy entfernt, rsync-Exclude ist raus)
- [x] `composer.json`: automatisches Anlegen der SQLite-Datei entfernen, ohne MySQL-Config soll ein Fehler kommen (-> FEAT-007)
- [x] Prüfen, dass `migrate --force` gegen MySQL durchläuft (lokal gegen MySQL 8.0 geprüft, alle Migrationen laufen)

## 2. Uncommittete UI-/Layout-Änderungen durchgehen (-> FEAT-008)
- [x] Alle ~54 geänderten Dateien prüfen, dann in Commits aufteilen (Branch `feat/ui-redesign-review`)
- [x] Login/Register: Erklärungsblock "Weedy Gallery, The Ultimate Art Platform" fehlt (wieder drin, farblich abgesetzt)
- [x] Navigation: "Weedy Gallery" wie Login/Register darstellen (weiß + Linie nur wenn aktiv, Hover, nicht mehr Teil des Logos) also NAV[(LOGO)Weedy Gallery | Login | Register] bzw angemeldet dann My Images. (angemeldet: Weedy Gallery | My Images | Profile | Admin, rechts Create, Name, Log Out; unter `lg` alles im Hamburger-Menü, Name klein und grau unten)
- [x] Zweite Überschrift im Body weg (nur noch `sr-only` für Screenreader/SEO), "Create"/"Edit" steht im Formular
- [x] Navigation: "Search Gallery" vertikal mittig setzen
- [x] Suche prüfen (Umlaute, Groß-/Kleinschreibung, MySQL) (Feature-Tests grün auf SQLite und MySQL 8.0)
- [x] Such-Feld: natives X-Icon (Chrome-Clear-Button) entfernt
- [x] Galerie: Datums-Popup kontrastreich (dunkle Schrift auf Cyan), Format `d.m.Y` (auch bei Kommentaren)
- [x] Profil: "About Me" gleicher Ton wie der Text, näher am Text, Abstand zu den Stats

### Versteckte Reduktionen (VR) aus dem Review
- [x] VR1: Galerie-Überschrift "Weedy Gallery" -> "Gallery", Nav-Link fest "Gallery" statt `config('app.name')` (Nav-Link und versteckte Überschrift nutzen wieder `config('app.name')`)
- [x] VR2: Create-Button für Gäste wieder sichtbar (in der Nav, führt zum Login; nicht auf Login/Register/Passwort-Seiten)
- [x] VR3: Profilbild wieder `h-80 w-80` (Profilseite und Vorschau in Profil bearbeiten)
- [x] VR4: Profil bearbeiten: abwechselnde Abschnitts-Hintergründe wieder da (`app-surface-accent`, wie der Login-Hinweis)
- [x] VR5: Serifenschrift Nanum Myeongjo bei Überschriften weg -> bleibt weg (keine lateinische Schrift)
- [x] VR6: My Images: Icons ▽ ✎ ✕ △ und breiter linker Balken wieder da (`app-button-bar`, Farben aus der neuen Palette)

## 3. Tests
- [x] 12 veraltete Tests repariert, Suite grün (44 Tests). Dabei `posts.show` entfernt (Route ohne View, lieferte 500) und `edit()`-Rückgabetyp korrigiert (Redirect bei veröffentlichtem Post war ein 500).

## 4. Server (manuell, @butburg)
- [ ] Nach dem nächsten Deploy auf dem Server die alten statischen Bilder aus dem Storage löschen. Sie liegen jetzt in `public/images/` und werden normal mit deployed:
  ```
  cd oe-app/storage/app/public/files/images
  rm starfish.svg broken.png impressum-contact.png impressum-mail.png blue.jpg placeholder.jpg
  ```
  (`blue.jpg` und `placeholder.jpg` werden nirgends genutzt.) Erst nach dem Deploy löschen, sonst fehlen die Bilder bis dahin. `factory_images/` bleibt.
