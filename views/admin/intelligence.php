<h1>هوشمندی و سلامت معرفی‌ها</h1>
<p class="muted">این صفحه برای مدیران است و فقط نشانه‌های قابل‌توضیح کیفیت، اعتماد و رفتارهای مشکوک را نشان می‌دهد. این محاسبات داخلی به اعضا نمایش داده نمی‌شود و در MVP باعث مسدودسازی خودکار نمی‌شود.</p>
<section class="grid four">
    <div class="card stat-card"><strong>اعضای بررسی‌شده</strong><p class="metric"><?= count($members ?? []) ?></p></div>
    <div class="card stat-card"><strong>شناخت‌نامه کم‌کیفیت</strong><p class="metric"><?= count(array_filter($members ?? [], fn($m) => (float)$m['profile_quality_score'] < 45)) ?></p></div>
    <div class="card stat-card"><strong>اعتماد نیازمند پایش</strong><p class="metric"><?= count(array_filter($members ?? [], fn($m) => in_array($m['trust_level'], ['watch','low'], true))) ?></p></div>
    <div class="card stat-card"><strong>گزارش یا پیام پرچم‌دار</strong><p class="metric"><?= count(array_filter($members ?? [], fn($m) => (int)$m['reports_received'] > 0 || (int)$m['flagged_messages'] > 0)) ?></p></div>
</section>
<section class="card table-wrap">
<table><thead><tr><th>عضو</th><th>کیفیت شناخت‌نامه</th><th>اعتماد</th><th>تکمیل شناخت</th><th>خستگی/رد اختیاری</th><th>نشانه‌های کیفیت</th><th>نشانه‌های اعتماد</th><th>گزارش‌ها</th></tr></thead><tbody>
<?php foreach ($members ?? [] as $member): ?>
<?php $qualityFlags = json_decode((string)($member['quality_flags_json'] ?? '[]'), true) ?: []; $trustFlags = json_decode((string)($member['trust_flags_json'] ?? '[]'), true) ?: []; ?>
<tr>
    <td><strong><?= e(trim($member['first_name'].' '.$member['last_name'])) ?></strong><small><?= e($member['email']) ?></small></td>
    <td><span class="pill"><?= (int)round((float)$member['profile_quality_score']) ?>٪ · <?= e(fa_label($member['profile_quality_level'])) ?></span></td>
    <td><span class="pill"><?= (int)round((float)$member['trust_score']) ?>٪ · <?= e(fa_label($member['trust_level'])) ?></span></td>
    <td><?= !empty($member['is_complete']) ? 'کامل' : 'در حال تکمیل' ?><small><?= e($member['onboarding_updated_at'] ?? '') ?></small></td>
    <td><?= (int)($member['skipped_optional_count'] ?? 0) ?> پرسش اختیاری<small><?= (int)round((float)($member['fatigue_score'] ?? 0)) ?>٪ خستگی احتمالی</small></td>
    <td><?php if (!$qualityFlags): ?><span class="muted">نشانه خاصی نیست</span><?php endif; ?><?php foreach ($qualityFlags as $flag): ?><span class="pill"><?= e(fa_label($flag)) ?></span> <?php endforeach; ?></td>
    <td><?php if (!$trustFlags): ?><span class="muted">نشانه خاصی نیست</span><?php endif; ?><?php foreach ($trustFlags as $flag): ?><span class="pill"><?= e(fa_label($flag)) ?></span> <?php endforeach; ?></td>
    <td><?= (int)$member['reports_received'] ?> گزارش · <?= (int)$member['flagged_messages'] ?> پیام پرچم‌دار</td>
</tr>
<?php endforeach; ?>
</tbody></table>
</section>
<section class="card table-wrap"><h2>معرفی‌های کم‌اطمینان</h2><p class="muted">این فهرست کمک می‌کند معرفی‌هایی را ببینید که داده کافی ندارند و ممکن است به شناخت‌نامه کامل‌تر یا توضیح ملایم‌تر نیاز داشته باشند.</p><table><thead><tr><th>شناسه</th><th>اعضا</th><th>سازگاری</th><th>اطمینان</th><th>تازگی/نمایش</th><th>وضعیت</th></tr></thead><tbody><?php foreach ($lowConfidenceMatches ?? [] as $match): ?><tr><td><?= (int)$match['id'] ?></td><td><?= e($match['user_one_name']) ?> ↔ <?= e($match['user_two_name']) ?></td><td><?= (int)round((float)$match['compatibility_score']) ?>٪</td><td><?= (int)round((float)$match['confidence_score']) ?>٪</td><td><?= (int)round((float)($match['avg_freshness_score'] ?? 0)) ?>٪ · <?= (int)($match['total_shown_count'] ?? 0) ?> نمایش</td><td><?= e(fa_label($match['match_status'])) ?></td></tr><?php endforeach; ?></tbody></table><?php if (empty($lowConfidenceMatches)): ?><p class="muted">در حال حاضر معرفی کم‌اطمینان برجسته‌ای وجود ندارد.</p><?php endif; ?></section>
<section class="card promise-card"><h2>راهنمای رسیدگی آرام</h2><ul class="soft-list"><li>امتیاز پایین به‌تنهایی دلیل اقدام تنبیهی نیست؛ ابتدا زمینه و گزارش‌ها را بررسی کنید.</li><li>نشانه‌های تکرار یا عجله فقط برای کاهش کیفیت/اعتماد و اولویت‌بندی بررسی استفاده می‌شوند.</li><li>هدف این صفحه افزایش امنیت و کیفیت معرفی‌هاست، نه قضاوت درباره اعضا.</li></ul></section>
