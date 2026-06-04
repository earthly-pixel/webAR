<?php
declare(strict_types=1);

require dirname(__DIR__) . '/backend-config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$config = loadTargetConfig();
echo json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
