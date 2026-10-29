# Cronjobs auf dem Server

Die App braucht auf dem Server Cronjobs, die der Hoster ausführt. Die Einträge unten sind für unseren aktuellen Hoster **lima-city**. Bei anderen Hostern heißen die Felder anders, oder der Cronjob kommt per `crontab -e` dazu. Pfade und `flock` können dort ebenfalls abweichen.

Bei lima-city im Kundenbereich unter **Cronjobs > Cronjob erstellen**. Hilfe: https://www.lima-city.de/hilfe/cronjobs

## Queue-Worker

Manche Arbeit erledigt die App nicht sofort im Request. Sie legt dafür einen Job in die Queue, also in die Tabelle `jobs`, oft mit „frühestens um X Uhr“. Der Worker holt die fälligen Jobs dort ab und führt sie aus, egal welche es sind. Ohne Worker bleiben alle Jobs liegen. Lokal macht das der `queue`-Container.

Welche Jobs es gibt, steht in `src/app/Jobs/`. Ein Beispiel sind die Sammel-Mails (Kommentare, neue Bilder, Ankündigungen), die erst nach einer Wartezeit gebündelt rausgehen.

| Feld | Eintrag |
|---|---|
| Typ | **Shell-Cronjob** (nicht URL-Cronjob) |
| Ausführungszeitpunkte | `* * * * *` |
| Kommando (bei URL-Cronjob heißt das Feld „URL“) | `cd oe-app && flock -n ~/.cronlock-queue php artisan queue:work --stop-when-empty --max-jobs=30 --quiet &>> ~/queue-cron.log` |
| Bemerkung | `Laravel Queue-Worker: arbeitet die fälligen Jobs ab` |

Was die Teile bedeuten:
- `* * * * *`: jede Minute. Der Worker arbeitet die fälligen Jobs ab und beendet sich wieder (`--stop-when-empty`).
- `cd oe-app`: Der Cronjob startet bei lima-city im Home-Verzeichnis, die App liegt in `~/oe-app`.
- `flock -n ~/.cronlock-queue`: Läuft der vorige Durchgang noch, wird dieser übersprungen.
- `--max-jobs=30`: Nach 30 Jobs beendet sich der Worker, der Rest bleibt für den nächsten Durchgang liegen. Ein Mail-Job verschickt höchstens eine Mail, also gehen höchstens 30 Mails pro Minute raus, unter dem Limit des SMTP-Anbieters. Das gilt nur, solange:
  - der Cronjob jede Minute läuft. Bei `*/5` wären es 30 pro 5 Minuten.
  - nur dieser eine Worker läuft. Ein zweiter Cron-Eintrag oder ein `queue:work` von Hand per SSH verdoppelt die Rate.

  Andere Jobs zählen mit. Kommen später welche dazu, gehen die Mails eher langsamer raus, nie schneller.
- `--quiet &>> ~/queue-cron.log`: Nur Fehler des Workers landen in `~/queue-cron.log`. Fehlgeschlagene Jobs stehen zusätzlich in der Tabelle `failed_jobs` und in `storage/logs/laravel.log`.

Warum kein URL-Cronjob: Der ruft nur eine Webseite auf. Dafür bräuchte die App eine öffentliche, abgesicherte Route, die den Worker startet. Der Shell-Cronjob führt den Befehl direkt aus.

Warum nicht einfach `QUEUE_CONNECTION=sync`: Dann gibt es keine Queue, und jeder Job läuft sofort im Request. „Frühestens um X Uhr“ wird ignoriert. Jobs, die warten sollen, funktionieren damit nicht. Die Sammel-Mails blockieren sich dann sogar selbst (500er nach 10 s).

### Prüfen, ob er läuft
1. Nach 1 bis 2 Minuten: `~/queue-cron.log` ist leer oder existiert nicht. Steht etwas drin, z. B. `php: command not found`, im Kommando den vollen Pfad aus `which php` einsetzen.
2. Die Tabelle `jobs` enthält nur Jobs, deren Zeitpunkt noch in der Zukunft liegt (Spalte `available_at`). Beispiel: Einen Kommentar auf ein fremdes Bild schreiben. Nach ca. 61 Minuten (`MAIL_BUNDLE_MINUTES`, Standard 60) ist die Mail beim Besitzer, und der Job ist aus `jobs` verschwunden.
