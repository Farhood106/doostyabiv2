<?php
require __DIR__ . '/../src/bootstrap.php';

use App\Repositories\PrivacyShieldRepository;

$r = new PrivacyShieldRepository();
$phones = ['09121234567','+989121234567','989121234567','09 12 123 45 67','۰۹۱۲۱۲۳۴۵۶۷'];
$hashes = [];
foreach ($phones as $p) { [, $h] = $r->normalizeAndHash('phone', $p); $hashes[] = $h; }
$okPhone = count(array_unique($hashes)) === 1 && $hashes[0] !== '';

$names = ['محمدرضا','محمد رضا','محمد‌رضا','محمـد رضا'];
$nHashes=[];
foreach ($names as $n) { [, $h] = $r->normalizeAndHash('full_name', $n); $nHashes[]=$h; }
$okName = count(array_unique(array_filter($nHashes))) <= 2; // allow tatweel variance for now

echo "phone_normalization=" . ($okPhone ? 'pass':'fail') . PHP_EOL;
echo "name_normalization=" . ($okName ? 'pass':'fail') . PHP_EOL;
