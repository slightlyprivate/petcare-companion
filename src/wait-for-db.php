<?php

$host = getenv('DB_HOST') ?: 'db';
$port = (int) (getenv('DB_PORT') ?: 3306);
$timeout = 60;
for ($i = 0; $i < $timeout; $i++) {
    $conn = @fsockopen($host, $port, $errno, $errstr, 1.0);
    if ($conn) {
        fclose($conn);
        exit(0);
    }
    sleep(1);
}
fwrite(STDERR, "MySQL not ready after {$timeout}s at {$host}:{$port}\n");
exit(1);
