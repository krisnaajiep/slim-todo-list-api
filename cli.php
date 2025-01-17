<?php

function updateEnvVariable($filePath, $variable, $newValue)
{
    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $updated = false;

    foreach ($lines as &$line) {
        if (strpos($line, "$variable=") === 0) {
            $line = "$variable=$newValue";
            $updated = true;
        }
    }

    if (!$updated) {
        $lines[] = "$variable=$newValue";
    }

    file_put_contents($filePath, implode(PHP_EOL, $lines));

    echo "\nJWT secret:\n\n$newValue\n\n";
}

if (isset($argv[1]) && $argv[1] == 'jwt:secret') {
    try {
        $filePath = '.env';
        $variable = 'JWT_SECRET';
        $newValue = bin2hex(random_bytes(32));

        updateEnvVariable($filePath, $variable, $newValue);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
