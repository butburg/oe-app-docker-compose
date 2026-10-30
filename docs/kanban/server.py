from __future__ import annotations

import json
import os
import re
import tempfile
import time
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import unquote, urlsplit


REPOSITORY_ROOT = Path(__file__).resolve().parents[2]
FEATURES_DIRECTORY = REPOSITORY_ROOT / "docs" / "features"
FEATURE_FILENAME = re.compile(r"FEAT-\d+-[a-z0-9][a-z0-9-]*\.md", re.IGNORECASE)
FEATURE_ID = re.compile(r"^id:\s*(FEAT-\d+)\s*$", re.IGNORECASE | re.MULTILINE)
FRONTMATTER = re.compile(r"\A---\s*\n([\s\S]*?)\n---(?:\n|\Z)")
MAX_MARKDOWN_BYTES = 1_000_000
WATCH_INTERVAL_SECONDS = 0.5


class FeatureBoardHandler(SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=str(REPOSITORY_ROOT), **kwargs)

    def do_GET(self):
        if urlsplit(self.path).path == "/api/features":
            files = [
                {"fileName": path.name, "markdown": path.read_text(encoding="utf-8")}
                for path in sorted(FEATURES_DIRECTORY.iterdir())
                if path.is_file() and FEATURE_FILENAME.fullmatch(path.name)
            ]
            self._send_json(200, files)
            return
        if urlsplit(self.path).path == "/api/events":
            self._stream_changes()
            return
        super().do_GET()

    def _stream_changes(self):
        """Server-sent events: one "changed" event whenever a feature file is added, removed or saved."""
        self.send_response(200)
        self.send_header("Content-Type", "text/event-stream")
        self.send_header("Cache-Control", "no-store")
        self.end_headers()
        snapshot = _features_snapshot()
        try:
            while True:
                time.sleep(WATCH_INTERVAL_SECONDS)
                current = _features_snapshot()
                # The comment line also detects a closed tab, the write fails then
                self.wfile.write(b"data: changed\n\n" if current != snapshot else b": ping\n\n")
                self.wfile.flush()
                snapshot = current
        except (BrokenPipeError, ConnectionResetError):
            pass

    def do_PUT(self):
        path = urlsplit(self.path).path
        prefix = "/api/features/"
        if not path.startswith(prefix):
            self._send_json(404, {"error": "Unknown API endpoint."})
            return

        filename = unquote(path[len(prefix):])
        if not FEATURE_FILENAME.fullmatch(filename):
            self._send_json(400, {"error": "Invalid feature filename."})
            return

        try:
            content_length = int(self.headers.get("Content-Length", "0"))
        except ValueError:
            self._send_json(400, {"error": "Invalid content length."})
            return
        if content_length <= 0 or content_length > MAX_MARKDOWN_BYTES:
            self._send_json(413, {"error": "Feature Markdown must be under 1 MB."})
            return

        markdown = self.rfile.read(content_length).decode("utf-8")
        frontmatter = FRONTMATTER.match(markdown)
        feature_id = FEATURE_ID.search(frontmatter.group(1)) if frontmatter else None
        if not feature_id:
            self._send_json(400, {"error": "Markdown needs a FEAT-### id in YAML frontmatter."})
            return

        feature_id = feature_id.group(1).upper()
        if not filename.upper().startswith(f"{feature_id}-"):
            self._send_json(400, {"error": "Filename must match the feature ID."})
            return

        FEATURES_DIRECTORY.mkdir(parents=True, exist_ok=True)
        target = FEATURES_DIRECTORY / filename
        for existing in FEATURES_DIRECTORY.iterdir():
            if existing == target or not existing.is_file() or not FEATURE_FILENAME.fullmatch(existing.name):
                continue
            existing_frontmatter = FRONTMATTER.match(existing.read_text(encoding="utf-8"))
            existing_id = FEATURE_ID.search(existing_frontmatter.group(1)) if existing_frontmatter else None
            if existing_id and existing_id.group(1).upper() == feature_id:
                self._send_json(409, {"error": f"{feature_id} already exists in {existing.name}."})
                return

        temporary_path = None
        try:
            with tempfile.NamedTemporaryFile(
                mode="w",
                encoding="utf-8",
                dir=FEATURES_DIRECTORY,
                prefix=".feature-board-",
                delete=False,
            ) as temporary_file:
                temporary_file.write(markdown)
                temporary_path = Path(temporary_file.name)
            os.replace(temporary_path, target)
        except OSError as error:
            if temporary_path and temporary_path.exists():
                temporary_path.unlink()
            self._send_json(500, {"error": f"Could not write feature file: {error}"})
            return

        self._send_json(200, {"ok": True, "fileName": filename})

    def _send_json(self, status, data):
        payload = json.dumps(data).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(payload)))
        self.send_header("Cache-Control", "no-store")
        self.end_headers()
        self.wfile.write(payload)


def _features_snapshot():
    """Name, modification time and size of every feature file, enough to notice any save."""
    snapshot = set()
    for path in FEATURES_DIRECTORY.iterdir():
        if FEATURE_FILENAME.fullmatch(path.name):
            try:
                stat = path.stat()
            except FileNotFoundError:
                continue
            snapshot.add((path.name, stat.st_mtime_ns, stat.st_size))
    return snapshot


def main():
    server = ThreadingHTTPServer(("127.0.0.1", 8765), FeatureBoardHandler)
    print(f"Kanban board: http://localhost:{server.server_port}/docs/kanban/index.html")
    print(f"Feature files: {FEATURES_DIRECTORY}")
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nKanban server stopped.")
    finally:
        server.server_close()


if __name__ == "__main__":
    main()
