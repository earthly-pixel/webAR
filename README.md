# webAR

WebAR page using A-Frame + MindAR and browser camera. The scene is wired for multiple image targets, with different 3D content for `targetIndex: 0` and `targetIndex: 1`.

Important: MindAR multi-target tracking requires one compiled `.mind` file that contains every marker image you want to scan.

## PHP target upload backend

This project now supports secure upload and replacement of the active MindAR target file.
Public web files are served from `public/` (DDEV docroot), while secrets/config files remain outside web root.

- Main page: `public/index.php`
- Upload page: `public/upload-target.php`
- Active target config endpoint: `public/target-config.php`
- Shared backend config: `backend-config.php`

Upload flow:

1. Open `upload-target.php`
2. Enter passphrase
3. Upload a `.mind` file
4. Backend stores file in `./uploads/` with a generated filename
5. Backend updates `mindar-target.json`
6. `index.php` reads config and sets `imageTargetSrc` automatically

## Passphrase setup

Set environment variable `MINDAR_UPLOAD_PASSPHRASE` before opening `upload-target.php`.

Examples:

```bash
export MINDAR_UPLOAD_PASSPHRASE='your-strong-passphrase'
```

For DDEV, add it to your web container environment configuration, then restart.

## Run locally

Use PHP built-in server with `public/` as document root:

```bash
php -S localhost:8080 -t public
```

Then open `http://localhost:8080/index.php`.
