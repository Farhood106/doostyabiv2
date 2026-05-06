<h1>System Health</h1>
<section class="card table-wrap"><table><tbody>
<tr><th>PHP version</th><td><?= e($report['php_version']) ?></td></tr>
<tr><th>PDO MySQL</th><td><?= $report['pdo_mysql'] ? 'Available' : 'Missing' ?></td></tr>
<tr><th>Database connection</th><td><?= $report['database_ok'] ? 'OK' : 'Failed: '.e($report['database_error']) ?></td></tr>
<tr><th>storage/logs writable</th><td><?= $report['logs_writable'] ? 'Writable' : 'Not writable' ?></td></tr>
<tr><th>Session</th><td><?= e($report['session_status']) ?></td></tr>
<tr><th>Environment</th><td><?= e($report['app_env']) ?></td></tr>
</tbody></table></section>
<section class="card table-wrap"><h2>Schema tables</h2><table><thead><tr><th>Table</th><th>Status</th></tr></thead><tbody><?php foreach ($report['tables'] as $table => $ok): ?><tr><td><?= e($table) ?></td><td><?= $ok ? 'Found' : 'Missing' ?></td></tr><?php endforeach; ?></tbody></table></section>
