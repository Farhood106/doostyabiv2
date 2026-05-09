<?php
require dirname(__DIR__) . '/src/bootstrap.php';

use App\Services\MatchService;

$limit = 20;
foreach ($argv ?? [] as $arg) {
    if (strpos($arg, '--limit=') === 0) { $limit = max(1, (int)substr($arg, 8)); }
}
$count = (new MatchService())->runBatch($limit);
echo "Generated or updated {$count} match recommendations." . PHP_EOL;
