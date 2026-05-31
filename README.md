# webAR

WebAR page using A-Frame + MindAR and browser camera. When the MindAR target is detected, a 3D object appears and can be rotated with the A-Frame gesture component.

## Run locally

Because camera access requires a secure context, serve this project with a local web server (not `file://`). Example:

```bash
python3 -m http.server 8080
```

Then open `http://localhost:8080/index.html`.
