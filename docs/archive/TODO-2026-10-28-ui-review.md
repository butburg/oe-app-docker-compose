# ToDo (Stand 2026-10-28)

Vorherige Liste: [archive/TODO-2026-10-28.md](archive/TODO-2026-10-28.md)

## 1. Server (manuell, @butburg)
- [ ] Nach dem nächsten Deploy auf dem Server die alten statischen Bilder aus dem Storage löschen. Sie liegen jetzt in `public/images/` und werden normal mit deployed:
  ```
  cd oe-app/storage/app/public/files/images
  rm starfish.svg broken.png impressum-contact.png impressum-mail.png blue.jpg placeholder.jpg
  ```
  (`blue.jpg` und `placeholder.jpg` werden nirgends genutzt.) Erst nach dem Deploy löschen, sonst fehlen die Bilder bis dahin. `factory_images/` bleibt.

## 2. My Images: Schloss-Symbol bei Entwürfen
- [x] Bei "Not Yet Published"-Posts sitzt das Schloss-Symbol (Protected) nicht vertikal mittig zur Textzeile. Bei den veröffentlichten Posts passt es schon (dort `translate-y-[0.2rem]` am Icon), das als Vorlage nehmen (`posts/index.blade.php`, Entwurf ca. Z. 73, veröffentlicht ca. Z. 165). (Beide Zeilen jetzt per Flexbox zentriert wie in der Galerie, `translate-y`-Hack entfernt.)

## 3. Galerie mobil (unter ~640px / `sm`)
- [x] Post-Karten randlos: keine abgerundeten Ecken, kein seitlicher Abstand zum Bildschirmrand (`mx-1` in `components/gallery/gallery.blade.php` weg). Die Karte (Bild, Titel, Autor, Angaben, Kommentare) geht nahtlos bis an den Rand, Hintergrund sieht man nur noch zwischen den Karten.
- [x] Innenabstand der Karte verringern, damit mehr Platz fürs Bild bleibt. Bild bleibt linksbündig mit Titel, Autor und den weiteren Angaben. (Innenabstand bleibt `px-2`, der Platz kommt aus dem weggefallenen Außenabstand und Rand.)
- [x] Ab `sm` bleibt alles wie bisher (runde Karte mit Abstand).

## 4. Suche mobil (Navigation)
- [x] Problem: Tippt man mobil (z. B. auf der Startseite) auf das Suchsymbol, wird das Suchfeld links abgeschnitten, weil rechts daneben noch der Create-Button und das Hamburger-Menü sitzen.
- [x] Prüfen: Suche mobil direkt in der Navigationsleiste ausklappen statt als Pop-up darunter. Nach dem Tippen auf das Suchsymbol füllt das Feld den Platz zwischen Logo und Create-Button aus. Vermutlich die schönere Variante. (Umgesetzt: mobiles Pop-up und Desktop-Feld zu einem Formular zusammengelegt, Gast-Links weichen beim Aufklappen, Escape/Klick daneben schließt.)

## 5. Seitenabstand mobil
- [x] Unter `sm` fehlte auf den meisten Seiten der seitliche Abstand (nur Profil bearbeiten hatte `px-4`). Jetzt einheitlich `px-4 sm:px-6 lg:px-8` bei My Images, Create, Impressum, öffentlichem Profil und Admin. Ausnahmen mit voller Breite wie die Galerie: Galerie und Edit.
- [x] Admin: Tippfehler `justify-centersm:gap-4` behoben, Kacheln mit `gap-4` statt `m-3` (liefen mobil über den Rand).
