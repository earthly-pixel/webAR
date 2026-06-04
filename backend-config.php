<?php
declare(strict_types=1);

const MINDAR_CONFIG_FILE = __DIR__ . '/mindar-target.json';
const MINDAR_UPLOAD_DIR = __DIR__ . '/public/uploads';
const MINDAR_DEFAULT_TARGET = './targets(3).mind';
const MINDAR_MAX_UPLOAD_BYTES = 25 * 1024 * 1024;

function getExpectedPassphrase(): ?string
{
    $value = getenv('MINDAR_UPLOAD_PASSPHRASE');
    if ($value !== false) {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    $value = readEnvValue(__DIR__ . '/.env', 'MINDAR_UPLOAD_PASSPHRASE');
    if ($value === null) {
        return null;
    }

    $value = trim($value);
    return $value === '' ? null : $value;
}

function readEnvValue(string $envFilePath, string $key): ?string
{
    if (!is_file($envFilePath) || !is_readable($envFilePath)) {
        return null;
    }

    $raw = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($raw)) {
        return null;
    }

    foreach ($raw as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$candidateKey, $candidateValue] = explode('=', $line, 2);
        if (trim($candidateKey) !== $key) {
            continue;
        }

        $candidateValue = trim($candidateValue);
        $length = strlen($candidateValue);
        if ($length >= 2) {
            $first = $candidateValue[0];
            $last = $candidateValue[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $candidateValue = substr($candidateValue, 1, -1);
            }
        }

        return $candidateValue;
    }

    return null;
}

function loadTargetConfig(): array
{
    if (!is_file(MINDAR_CONFIG_FILE)) {
        return [
            'imageTargetSrc' => MINDAR_DEFAULT_TARGET,
            'updatedAt' => null,
        ];
    }

    $raw = file_get_contents(MINDAR_CONFIG_FILE);
    if ($raw === false) {
        return [
            'imageTargetSrc' => MINDAR_DEFAULT_TARGET,
            'updatedAt' => null,
        ];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            'imageTargetSrc' => MINDAR_DEFAULT_TARGET,
            'updatedAt' => null,
        ];
    }

    $imageTargetSrc = $decoded['imageTargetSrc'] ?? MINDAR_DEFAULT_TARGET;
    if (!is_string($imageTargetSrc) || trim($imageTargetSrc) === '') {
        $imageTargetSrc = MINDAR_DEFAULT_TARGET;
    }

    return [
        'imageTargetSrc' => $imageTargetSrc,
        'updatedAt' => $decoded['updatedAt'] ?? null,
    ];
}

function saveTargetConfig(string $imageTargetSrc): bool
{
    $payload = json_encode([
        'imageTargetSrc' => $imageTargetSrc,
        'updatedAt' => gmdate('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        return false;
    }

    return file_put_contents(MINDAR_CONFIG_FILE, $payload . PHP_EOL, LOCK_EX) !== false;
}
