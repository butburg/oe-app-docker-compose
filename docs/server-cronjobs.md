# Cronjobs auf dem Server

Die App braucht auf dem Server Cronjobs, die der Hoster ausführt. Die Einträge unten sind für unseren aktuellen Hoster **lima-city**. Bei anderen Hostern heißen die Felder anders, oder der Cronjob kommt per `crontab -e` dazu. Pfade und `flock` können dort ebenfalls abweichen. Was der Befehl tun muss, bleibt gleich.

Bei lima-city im Kundenbereich unter **Cronjobs > Cronjob erstellen**. Hilfe: https://www.lima-city.de/hilfe/cronjobs

## Queue-Worker (Sammel-Mails)

Verschickt die Kommentar-, Neues-Bild- und Ankündigungs-Mails (FEAT-017 bis 020). Ohne ihn bleiben sie in der Tabelle `jobs` liegen. Lokal macht das der `queue`-Container.

Erst nach dem Deploy von Branch 05 anlegen.

| Feld | Eintrag |
|---|---|
| Typ | **Shell-Cronjob** (nicht URL-Cronjob) |
| Ausführungszeitpunkte | `* * * * *` |
| Kommando (bei URL-Cronjob heißt das Feld „URL“) | `cd oe-app && flock -n ~/.cronlock-queue php artisan queue:work --stop-when-empty --quiet &>> ~/queue-cron.log` |
| Bemerkung | `Laravel Queue: Sammel-Mails verschicken` |

Was die Teile bedeuten:
- `* * * * *`: jede Minute. Der Worker schaut nach, welche Mails fällig sind, verschickt sie und beendet sich wieder (`--stop-when-empty`).
- `cd oe-app`: Der Cronjob startet bei lima-city im Home-Verzeichnis, die App liegt in `~/oe-app`.
- `flock -n ~/.cronlock-queue`: Läuft der vorige Durchgang noch, wird dieser übersprungen.
- `--quiet &>> ~/queue-cron.log`: Nur Fehler landen in `~/queue-cron.log`. Fehlgeschlagene Mails stehen zusätzlich in der Tabelle `failed_jobs` und in `storage/logs/laravel.log`.

Warum kein URL-Cronjob: Der ruft nur eine Webseite auf. Dafür bräuchte die App eine öffentliche, abgesicherte Route, die den Worker startet. Der Shell-Cronjob führt den Befehl direkt aus.

Warum nicht einfach `QUEUE_CONNECTION=sync`: Dann läuft der Job sofort im Request, die Wartezeit fürs Bündeln wird ignoriert, und der Job blockiert sich selbst (500er nach 10 s).

### Prüfen, ob er läuft
1. Nach 1 bis 2 Minuten: `~/queue-cron.log` ist leer oder existiert nicht. Steht etwas drin, z. B. `php: command not found`, im Kommando den vollen Pfad aus `which php` einsetzen.
2. Einen Kommentar auf ein fremdes Bild schreiben. Nach ca. 61 Minuten (`MAIL_BUNDLE_MINUTES`, Standard 60) ist die Mail beim Besitzer, und die Tabelle `jobs` ist leer.
