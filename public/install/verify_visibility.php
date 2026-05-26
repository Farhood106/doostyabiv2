<?php
require __DIR__ . '/_common.php';
require INSTALL_ROOT . '/src/bootstrap.php';

use App\Core\Database;
use App\Services\VisibilityGuardService;

$loaded = install_load_config();
if (!install_is_authorized($loaded['config'] ?? null)) { install_denied(); }
$db = Database::connection();
$guard = new VisibilityGuardService();
$checks = [];
$violations = ['illegal_visible_pairs'=>0,'illegal_open_chats'=>0,'illegal_mutuals'=>0,'stale_cards_visible'=>0];

$rows = $db->query('SELECT id,user_one_id,user_two_id,match_status FROM matches')->fetchAll();
foreach ($rows as $r) {
  $ev = $guard->evaluateVisibility((int)$r['user_one_id'], (int)$r['user_two_id']);
  if ($ev['allowed']) { continue; }
  if (in_array($r['match_status'], ['suggested','mutual'], true)) { $violations['illegal_visible_pairs']++; }
  if ($r['match_status'] === 'mutual') { $violations['illegal_mutuals']++; }
  $c = $db->prepare('SELECT COUNT(*) FROM chats WHERE match_id=? AND status="open"'); $c->execute([(int)$r['id']]);
  if ((int)$c->fetchColumn() > 0) { $violations['illegal_open_chats']++; }
  $mc = $db->prepare('SELECT COUNT(*) FROM match_cards WHERE match_id=? AND (hidden_until IS NULL OR hidden_until < NOW())'); $mc->execute([(int)$r['id']]);
  if ((int)$mc->fetchColumn() > 0) { $violations['stale_cards_visible']++; }
}
foreach ($violations as $k=>$v) { $checks[] = install_check('Visibility: '.$k, $v===0?'pass':'warn', (string)$v); }
install_render('Visibility verification', $checks);
