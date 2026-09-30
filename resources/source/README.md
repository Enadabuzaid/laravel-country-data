# Pinned upstream data

Exact copies of the upstream datasets used by `scripts/build-data.php`, so the
build is reproducible offline. Revisions are recorded in `manifest.json`.

- Refresh: `composer data:fetch` (network), review the diff, then `composer data:build`.
- Never edit these files by hand. Corrections belong in the override constants at
  the top of `scripts/build-data.php` (each one commented with its reason) or in
  `resources/curated/`.

Licences: see `data/ATTRIBUTION.md`.
