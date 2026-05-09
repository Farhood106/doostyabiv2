<h1>سلامت سامانه</h1>
<section class="card table-wrap"><table><tbody>
<tr><th>نسخه PHP</th><td><?= e($report['php_version']) ?></td></tr>
<tr><th>PDO MySQL</th><td><?= $report['pdo_mysql'] ? 'در دسترس' : 'موجود نیست' ?></td></tr>
<tr><th>اتصال پایگاه داده</th><td><?= $report['database_ok'] ? 'درست' : 'ناموفق: '.e($report['database_error']) ?></td></tr>
<tr><th>قابلیت نوشتن storage/logs</th><td><?= $report['logs_writable'] ? 'قابل نوشتن' : 'قابل نوشتن نیست' ?></td></tr>
<tr><th>نشست</th><td><?= e($report['session_status']) ?></td></tr>
<tr><th>محیط</th><td><?= e($report['app_env']) ?></td></tr>
</tbody></table></section>
<section class="card table-wrap"><h2>جدول‌های ساختار</h2><table><thead><tr><th>جدول</th><th>وضعیت</th></tr></thead><tbody><?php foreach ($report['tables'] as $table => $ok): ?><tr><td><?= e($table) ?></td><td><?= $ok ? 'موجود' : 'موجود نیست' ?></td></tr><?php endforeach; ?></tbody></table></section>
