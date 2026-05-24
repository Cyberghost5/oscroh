<?php
// SECURITY: Delete this file immediately after use.

$secret = 'oscroh';

if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    http_response_code(403);
    exit('Forbidden');
}

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->call('route:list');

echo '<pre>';
echo "route:clear exit code: {$status}\n";
echo $kernel->output();
echo '</pre>';
echo '<p><strong>Done. Delete this file from your server now.</strong></p>';
