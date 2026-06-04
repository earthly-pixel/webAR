<?php
declare(strict_types=1);

require dirname(__DIR__) . '/backend-config.php';

$config = loadTargetConfig();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expectedPassphrase = getExpectedPassphrase();
    $submittedPassphrase = trim((string)($_POST['passphrase'] ?? ''));

    if ($expectedPassphrase === null) {
        $error = 'Server passphrase is not configured. Set MINDAR_UPLOAD_PASSPHRASE in your server environment.';
    } elseif (!hash_equals($expectedPassphrase, $submittedPassphrase)) {
        $error = 'Invalid passphrase.';
    } elseif (!isset($_FILES['mind_file']) || !is_array($_FILES['mind_file'])) {
        $error = 'No file uploaded.';
    } else {
        $upload = $_FILES['mind_file'];

        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = 'Upload failed with error code: ' . (int)$upload['error'];
        } elseif (!is_uploaded_file((string)$upload['tmp_name'])) {
            $error = 'Invalid upload source.';
        } elseif ((int)$upload['size'] > MINDAR_MAX_UPLOAD_BYTES) {
            $error = 'File is too large. Maximum upload size is 25 MB.';
        } else {
            $originalName = (string)($upload['name'] ?? '');
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($extension !== 'mind') {
                $error = 'Only .mind files are allowed.';
            } else {
                if (!is_dir(MINDAR_UPLOAD_DIR) && !mkdir(MINDAR_UPLOAD_DIR, 0755, true) && !is_dir(MINDAR_UPLOAD_DIR)) {
                    $error = 'Failed to create upload directory.';
                } else {
                  try {
                    $randomSuffix = bin2hex(random_bytes(4));
                  } catch (Throwable $exception) {
                    $randomSuffix = substr(sha1(uniqid('', true)), 0, 8);
                  }

                    $newName = 'targets-' . gmdate('Ymd-His') . '-' . $randomSuffix . '.mind';
                    $destinationPath = MINDAR_UPLOAD_DIR . '/' . $newName;
                    $newRelativePath = './uploads/' . $newName;

                    if (!move_uploaded_file((string)$upload['tmp_name'], $destinationPath)) {
                        $error = 'Failed to store uploaded file.';
                    } elseif (!saveTargetConfig($newRelativePath)) {
                        @unlink($destinationPath);
                        $error = 'File uploaded but failed to update config.';
                    } else {
                        $oldPath = (string)($config['imageTargetSrc'] ?? '');
                      if (strpos($oldPath, './uploads/') === 0) {
                            $oldBasename = basename($oldPath);
                            $oldAbsolutePath = MINDAR_UPLOAD_DIR . '/' . $oldBasename;
                            if (is_file($oldAbsolutePath) && $oldAbsolutePath !== $destinationPath) {
                                @unlink($oldAbsolutePath);
                            }
                        }

                        $config = loadTargetConfig();
                        $success = 'Upload successful. imageTargetSrc is now ' . $config['imageTargetSrc'];
                    }
                }
            }
        }
    }
}

$currentTarget = htmlspecialchars((string)($config['imageTargetSrc'] ?? MINDAR_DEFAULT_TARGET), ENT_QUOTES, 'UTF-8');
$updatedAt = htmlspecialchars((string)($config['updatedAt'] ?? '-'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MindAR Target Upload</title>
    <style>
      :root {
        --bg: #f5f6f8;
        --card: #ffffff;
        --text: #1e2430;
        --border: #d8deea;
        --ok: #0d7a39;
        --err: #b42318;
        --accent: #0b6bcb;
      }

      body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family: "Segoe UI", sans-serif;
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 24px;
      }

      .card {
        width: min(680px, 100%);
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
      }

      h1 {
        margin-top: 0;
        margin-bottom: 12px;
        font-size: 24px;
      }

      p {
        margin-top: 0;
      }

      .meta {
        margin: 0 0 18px;
        color: #42526b;
        font-size: 14px;
      }

      .alert {
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 16px;
        font-size: 14px;
      }

      .alert.error {
        border: 1px solid #f6c0be;
        background: #fff1f0;
        color: var(--err);
      }

      .alert.success {
        border: 1px solid #b5e5c8;
        background: #ecfff3;
        color: var(--ok);
      }

      form {
        display: grid;
        gap: 14px;
      }

      label {
        display: grid;
        gap: 6px;
        font-size: 14px;
        font-weight: 600;
      }

      input {
        font: inherit;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid var(--border);
      }

      button {
        width: fit-content;
        border: 0;
        border-radius: 8px;
        padding: 10px 16px;
        font: inherit;
        font-weight: 700;
        color: #fff;
        background: var(--accent);
        cursor: pointer;
      }

      .hint {
        margin-top: 10px;
        font-size: 13px;
        color: #56657d;
      }

      .links {
        margin-top: 16px;
      }
    </style>
  </head>
  <body>
    <section class="card">
      <h1>Upload MindAR Target File</h1>
      <p class="meta">Current imageTargetSrc: <strong><?php echo $currentTarget; ?></strong></p>
      <p class="meta">Last updated: <strong><?php echo $updatedAt; ?></strong></p>

      <?php if ($error !== ''): ?>
      <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if ($success !== ''): ?>
      <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <label>
          Passphrase
          <input type="password" name="passphrase" required autocomplete="off" />
        </label>

        <label>
          .mind file
          <input type="file" name="mind_file" accept=".mind" required />
        </label>

        <button type="submit">Upload and Activate</button>
      </form>

      <p class="hint">Set environment variable <code>MINDAR_UPLOAD_PASSPHRASE</code> on your server before using this page.</p>
      <p class="links"><a href="./index.php">Open WebAR page</a></p>
    </section>
  </body>
</html>
