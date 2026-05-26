<?php
$progress = $progress ?? [];
$profile = $profile ?? [];
$goals = $goals ?? [];
$answers = $answers ?? [];
$qualityFlags = array_map('fa_label', json_decode((string)($profile['quality_flags_json'] ?? '[]'), true) ?: []);
$trustFlags = array_map('fa_label', json_decode((string)($profile['trust_flags_json'] ?? '[]'), true) ?: []);
?>
<h1>جزئیات عضو</h1>
<section class="card grid two">
    <div><h2>خلاصه پروفایل</h2><p><strong>نام:</strong> <?= e($profile['first_name'].' '.$profile['last_name']) ?></p><p><strong>ایمیل:</strong> <?= e($profile['email']) ?></p><p><strong>جنسیت:</strong> <?= e($profile['gender']) ?></p><p><strong>تاریخ تولد:</strong> <?= e($profile['birthdate']) ?></p></div>
    <div><h2>شناخت‌نامه</h2><p><span class="pill"><?= !empty($progress['is_complete']) ? 'کامل' : 'ناکامل' ?></span></p><p><strong>مرحله‌های تکمیل‌شده:</strong> <?= (int)($progress['completed_steps'] ?? 0) ?></p><p><strong>آخرین به‌روزرسانی:</strong> <?= e($progress['updated_at'] ?? 'شروع نشده') ?></p><p><strong>کیفیت:</strong> <span class="pill"><?= (int)round((float)($profile['profile_quality_score'] ?? 0)) ?>٪ · <?= e(fa_label($profile['profile_quality_level'] ?? 'not_ready')) ?></span></p><p><strong>اعتماد:</strong> <span class="pill"><?= (int)round((float)($profile['trust_score'] ?? 0)) ?>٪ · <?= e(fa_label($profile['trust_level'] ?? 'new')) ?></span></p></div>
</section>
<section class="card"><h2>نشانه‌های هوشمندی</h2><div class="grid three"><div><strong>نشانه‌های کیفیت</strong><p class="muted"><?= e($qualityFlags ? implode('، ', $qualityFlags) : 'نشانه خاصی نیست') ?></p></div><div><strong>نشانه‌های اعتماد</strong><p class="muted"><?= e($trustFlags ? implode('، ', $trustFlags) : 'نشانه خاصی نیست') ?></p></div><div><strong>خستگی شناخت‌نامه</strong><p class="muted"><?= (int)($progress['skipped_optional_count'] ?? 0) ?> پرسش اختیاری رد شده · <?= (int)round((float)($progress['fatigue_score'] ?? 0)) ?>٪</p></div></div></section>
<section class="card"><h2>هدف‌های انتخاب‌شده</h2><?php if (!$goals): ?><p>هدفی انتخاب نشده است.</p><?php endif; ?><div class="checks"><?php foreach ($goals as $goal): ?><span class="pill"><?= e($goal['title']) ?></span><?php endforeach; ?></div></section>
<section class="card"><h2>پاسخ‌های فرم</h2><?php foreach ($answers as $stepTitle => $groups): ?><section class="builder-section"><h3><?= e($stepTitle) ?></h3><?php foreach ($groups as $groupTitle => $rows): ?><h4><?= e($groupTitle) ?></h4><div class="table-wrap"><table><thead><tr><th>پرسش</th><th>پاسخ</th><th>حریم خصوصی</th><th>سازگاری</th></tr></thead><tbody><?php foreach ($rows as $answer): $value = $answer['option_labels'] ?: ($answer['city_labels'] ?: ($answer['answer_text'] ?? $answer['answer_number'] ?? $answer['answer_date'] ?? ($answer['answer_boolean'] === null ? '' : ($answer['answer_boolean'] ? 'بله' : 'خیر')))); ?><tr><td><?= e($answer['title']) ?></td><td><?= e((string)$value) ?></td><td><?= e(fa_label($answer['privacy_level'])) ?></td><td><?= $answer['is_matchable'] ? 'بله' : 'خیر' ?></td></tr><?php endforeach; ?></tbody></table></div><?php endforeach; ?></section><?php endforeach; ?></section>
