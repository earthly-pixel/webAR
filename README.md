# webAR

WebAR page using A-Frame + MindAR and browser camera. The scene is now wired for multiple image targets, with different 3D content for `targetIndex: 0` and `targetIndex: 1`.

Important: MindAR multi-target tracking requires one compiled `.mind` file that contains every marker image you want to scan. Two separate files like `targets_acekid.mind` and `targets_nonacekid.mind` cannot be scanned at the same time by one `a-scene`.

To make both targets work in this project:

1. Compile the original Acekid and Nonacekid marker images together into a single `.mind` file with the MindAR compiler: https://hiukim.github.io/mind-ar-js-doc/tools/compile/
2. Replace the `imageTargetSrc` value in [index.html](/home/angga/www/app/laravel/webAR/index.html) with that combined file (already set to `./targets.mind` in this repo).
3. Keep the existing `targetIndex: 0` and `targetIndex: 1` entities in [index.html](/home/angga/www/app/laravel/webAR/index.html) so each marker shows its own 3D object.

## Run locally

Because camera access requires a secure context, serve this project with a local web server (not `file://`). Example:

```bash
python3 -m http.server 8080
```

Then open `http://localhost:8080/index.html`.
