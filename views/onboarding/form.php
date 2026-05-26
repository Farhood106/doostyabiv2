<?php
$totalQuestions = 0; $answeredQuestions = 0; $requiredTotal = 0; $requiredAnswered = 0; $matchableAnswered = 0; $unansweredPriority = [];
$maxOptionalPerStep = max(0, (int)($settings['max_optional_questions_per_step'] ?? 3));
foreach ($steps as $step) {
    foreach ($step['groups'] as $group) {
        foreach ($group['questions'] as $question) {
            $qid = (int)$question['id'];
            $answered = !empty($existingAnswers[$qid]);
            $totalQuestions++;
            if ($answered) { $answeredQuestions++; }
            if (!empty($question['is_required'])) { $requiredTotal++; if ($answered) { $requiredAnswered++; } }
            if (!empty($question['is_matchable']) && $answered) { $matchableAnswered++; }
            if (!$answered) { $unansweredPriority[] = $question; }
        }
    }
}
usort($unansweredPriority, fn($a, $b) => question_priority($b) <=> question_priority($a));
$percent = $totalQuestions ? (int)round(($answeredQuestions / $totalQuestions) * 100) : 0;
$hasErrors = !empty($errors);
$qualityScore = (float)($quality['profile_quality_score'] ?? 0);
?>
<section class="section-title onboarding-title">
    <div><span class="eyebrow">شناخت خصوصی</span><h1>شناخت‌نامه سازگاری خود را بسازید</h1><p class="muted">با آرامش پاسخ دهید. لازم نیست همه چیز را یک‌باره کامل کنید؛ پاسخ‌های خصوصی فقط برای معرفی بهتر استفاده می‌شوند و پروفایل عمومی نمی‌سازند.</p></div>
    <span class="pill progress-pill"><?= $percent ?>% تکمیل شده</span>
</section>
<section class="card progress-card">
    <div class="progress"><span style="width: <?= $percent ?>%"></span></div>
    <div class="section-title"><p class="help"><?= (int)$answeredQuestions ?> از <?= (int)$totalQuestions ?> پرسش پاسخ داده شده است. <?= (int)$requiredAnswered ?> از <?= (int)$requiredTotal ?> پرسش ضروری کامل شده و <?= (int)$matchableAnswered ?> پاسخ قابل استفاده برای معرفی دارید.</p><a href="/safety">مرور تنظیمات امنیت</a></div>
</section>
<?php if (!empty($quality)): ?><section class="card quality-card"><div class="section-title"><div><span class="eyebrow">بازخورد آرام شناخت‌نامه</span><h2><?= $qualityScore < 55 ? 'شناخت‌نامه شما هنوز جای کامل‌تر شدن دارد' : 'شناخت‌نامه شما برای معرفی‌های دقیق‌تر آماده‌تر شده است' ?></h2><p class="muted">با چند پاسخ بیشتر، معرفی‌ها دقیق‌تر می‌شوند. این بازخورد فقط برای کمک به شماست و جزئیات داخلی اعتماد یا امتیازها به دیگر اعضا نمایش داده نمی‌شود.</p></div><span class="pill"><?= e(fa_label($quality['profile_quality_level'] ?? 'not_ready')) ?></span></div><p class="help">اگر خسته شدید، پرسش‌های اختیاری را برای بعد بگذارید. پاسخ به پرسش‌های ضروری و چند پرسش سازگاری بیشترین اثر را دارد.</p></section><?php endif; ?>
<?php if ($unansweredPriority): ?><section class="card"><div class="section-title"><div><span class="eyebrow">پیشنهاد بعدی</span><h2>چند پرسش مهم‌تر برای ادامه</h2><p class="muted">این‌ها بر اساس ضروری بودن، اثر در سازگاری و حساسیت حریم خصوصی جلوتر آمده‌اند.</p></div></div><div class="checks choice-cloud"><?php foreach (array_slice($unansweredPriority, 0, 3) as $question): ?><a class="pill" href="#question-<?= (int)$question['id'] ?>"><?= e($question['title']) ?></a><?php endforeach; ?></div></section><?php endif; ?>
<?php if (!$steps): ?><section class="card empty-state"><h2>فرم شناخت در حال آماده‌سازی است</h2><p>هنوز مرحله فعالی برای شناخت وجود ندارد. پس از انتشار فرم توسط مدیر دوباره سر بزنید.</p></section><?php endif; ?>
<?php if ($hasErrors): ?><section class="alert error"><strong>چند پاسخ نیاز به توجه دارد.</strong><p>لطفاً پرسش‌های مشخص‌شده را بررسی کنید. پاسخ‌های ضروری با ستاره مشخص شده‌اند.</p></section><?php endif; ?>
<form method="post" action="/onboarding" class="onboarding-form">
<?= csrf_field() ?>
<section class="card goal-card"><div class="section-title"><div><h2>هدف‌های ارتباطی شما</h2><p class="muted">هدف‌هایی را انتخاب کنید که با خواسته امروز شما نزدیک‌ترند. این کار از معرفی‌های ناهماهنگ جلوگیری می‌کند.</p></div><span class="pill">نشانه خصوصی</span></div><?php if (!$goals): ?><p class="muted">هنوز هدفی تعریف نشده است.</p><?php else: ?><div class="checks choice-cloud"><?php foreach ($goals as $goal): ?><label><input type="checkbox" name="goals[]" value="<?= (int)$goal['id'] ?>" <?= in_array((int)$goal['id'], $selectedGoalIds ?? [], true) ? 'checked' : '' ?>> <?= e($goal['title']) ?></label><?php endforeach; ?></div><?php endif; ?></section>
<?php foreach ($steps as $stepIndex => $step): ?>
<section class="card onboarding-step" id="step-<?= (int)$step['id'] ?>">
    <div class="section-title"><div><span class="eyebrow">مرحله <?= $stepIndex + 1 ?></span><h2><?= e($step['title']) ?></h2><?php if ($step['description']): ?><p class="muted"><?= e($step['description']) ?></p><?php else: ?><p class="muted">پرسش‌های ضروری و چند پرسش اثرگذارتر را جلوتر می‌بینید؛ بقیه اختیاری‌ها را می‌توانید بعداً پاسخ دهید.</p><?php endif; ?></div><span class="pill">حداکثر <?= (int)$maxOptionalPerStep ?> پرسش اختیاری تازه</span></div>
    <?php foreach ($step['groups'] as $group): ?>
        <?php [$questionsToShow, $hiddenOptional] = adaptive_questions_for_group($group['questions'], $existingAnswers, $maxOptionalPerStep); ?>
        <fieldset class="question-group"><legend><?= e($group['title']) ?></legend><?php if ($group['description']): ?><p class="help"><?= e($group['description']) ?></p><?php endif; ?>
        <?php foreach ($questionsToShow as $question): ?>
            <?php $qid = (int)$question['id']; $isOptional = empty($question['is_required']); ?>
            <div id="question-<?= $qid ?>" class="field question-card <?= !empty($errors[$qid]) ? 'has-error' : '' ?> <?= !empty($question['is_required']) ? 'required-question' : 'optional-question' ?>">
                <label><span><?= e($question['title']) ?><?= $question['is_required'] ? ' *' : '' ?></span><?php if ($question['description']): ?><small><?= e($question['description']) ?></small><?php endif; ?><?= render_question_input($question, $cities, $existingAnswers[$qid] ?? null) ?></label>
                <?php if ($isOptional): ?><p class="help"><span class="pill">اختیاری</span> اگر امروز انرژی ندارید، این پرسش را خالی بگذارید و بعداً پاسخ دهید.</p><?php endif; ?>
                <?php if (!empty($errors[$qid])): ?><span class="error-text"><?= e($errors[$qid]) ?></span><?php endif; ?>
                <?php if ($question['help_text']): ?><details><summary>چرا این سؤال مهم است؟</summary><p class="muted"><?= e($question['help_text']) ?></p></details><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($hiddenOptional > 0): ?><p class="help"><?= (int)$hiddenOptional ?> پرسش اختیاری برای کاهش خستگی فعلاً پنهان شده است. بعد از ذخیره یا بازگشت دوباره می‌توانید سراغشان بروید.</p><?php endif; ?>
        </fieldset>
    <?php endforeach; ?>
    <button class="button secondary" type="submit" name="save_continue" value="<?= (int)$step['id'] ?>">ذخیره این مرحله</button>
</section>
<?php endforeach; ?>
<section class="card save-panel"><h2>آماده ذخیره هستید؟</h2><p class="muted">هر زمان خواسته‌هایتان روشن‌تر شد می‌توانید پاسخ‌ها را به‌روزرسانی کنید. پرسش‌های اختیاری بی‌پاسخ، بعداً دوباره با اولویت مناسب نمایش داده می‌شوند.</p><button class="button large">ذخیره شناخت‌نامه</button></section>
</form>
<?php
function adaptive_questions_for_group(array $questions, array $existingAnswers, int $maxOptionalPerStep): array
{
    usort($questions, fn($a, $b) => question_priority($b, !empty($existingAnswers[(int)$b['id']])) <=> question_priority($a, !empty($existingAnswers[(int)$a['id']])));
    $shown = []; $optionalFresh = 0; $hiddenOptional = 0;
    foreach ($questions as $question) {
        $qid = (int)$question['id'];
        $answered = !empty($existingAnswers[$qid]);
        if (!empty($question['is_required']) || $answered) { $shown[] = $question; continue; }
        if ($optionalFresh < $maxOptionalPerStep) { $shown[] = $question; $optionalFresh++; continue; }
        $hiddenOptional++;
    }
    return [$shown, $hiddenOptional];
}
function question_priority(array $question, bool $answered = false): float
{
    $score = $answered ? -20 : 0;
    if (!empty($question['is_required'])) { $score += 100; }
    if (!empty($question['is_matchable'])) { $score += 45 + ((float)($question['match_weight'] ?? 1) * 10); }
    if (!empty($question['is_sensitive']) || in_array($question['privacy_level'] ?? '', ['high','medium'], true)) { $score += 18; }
    if (($question['visibility_scope'] ?? '') === 'matches') { $score += 8; }
    return $score + max(0, 20 - (int)($question['sort_order'] ?? 0) / 10);
}
function render_question_input(array $q, array $cities, ?array $existing): string
{
    $name = 'answers[' . (int)$q['id'] . ']';
    $req = $q['is_required'] ? ' required' : '';
    $ph = e($q['placeholder'] ?? '');
    $textValue = e((string)($existing['answer_text'] ?? $existing['answer_number'] ?? $existing['answer_date'] ?? ''));
    switch ($q['answer_type']) {
        case 'textarea': return '<textarea name="'.$name.'" placeholder="'.$ph.'"'.$req.'>'.$textValue.'</textarea>';
        case 'number': return '<input type="number" step="any" name="'.$name.'" placeholder="'.$ph.'" value="'.$textValue.'"'.$req.'>';
        case 'date': return '<input type="date" name="'.$name.'" value="'.$textValue.'"'.$req.'>';
        case 'boolean': $b = $existing['answer_boolean'] ?? ''; return '<select name="'.$name.'"'.$req.'><option value="">انتخاب کنید</option><option value="1"'.($b === 1 || $b === '1' ? ' selected' : '').'>بله</option><option value="0"'.($b === 0 || $b === '0' ? ' selected' : '').'>خیر</option></select>';
        case 'scale': return '<input type="range" min="1" max="10" name="'.$name.'" value="'.($textValue ?: '5').'"'.$req.'>';
        case 'single_choice': case 'select': return render_options($q, $name, $existing, false, $req);
        case 'multi_choice': case 'multi_select': return render_options($q, $name.'[]', $existing, true, $req);
        case 'city_single': return render_cities($cities, $name, $existing, false, $req);
        case 'city_multi': return render_cities($cities, $name.'[]', $existing, true, $req);
        default: return '<input name="'.$name.'" placeholder="'.$ph.'" value="'.$textValue.'"'.$req.'>';
    }
}
function render_options(array $q, string $name, ?array $existing, bool $multi, string $req): string
{
    $selected = array_map('intval', $existing['option_ids'] ?? $existing['options'] ?? []);
    if (!$multi) { $html = '<select name="'.$name.'"'.$req.'><option value="">انتخاب کنید</option>'; foreach ($q['options'] as $opt) { $sel = in_array((int)$opt['id'], $selected, true) ? ' selected' : ''; $html .= '<option value="'.(int)$opt['id'].'"'.$sel.'>'.e($opt['label']).'</option>'; } return $html.'</select>'; }
    $html = '<div class="checks choice-cloud">'; foreach ($q['options'] as $opt) { $checked = in_array((int)$opt['id'], $selected, true) ? ' checked' : ''; $html .= '<label><input type="checkbox" name="'.$name.'" value="'.(int)$opt['id'].'"'.$checked.'> '.e($opt['label']).'</label>'; } return $html.'</div>';
}
function render_cities(array $cities, string $name, ?array $existing, bool $multi, string $req): string
{
    $selected = array_map('intval', $existing['city_ids'] ?? $existing['cities'] ?? []);
    $attr = $multi ? ' multiple size="6"' : '';
    $html = '<select name="'.$name.'"'.$attr.$req.'>'.(!$multi ? '<option value="">انتخاب شهر</option>' : '');
    foreach ($cities as $city) { $sel = in_array((int)$city['id'], $selected, true) ? ' selected' : ''; $html .= '<option value="'.(int)$city['id'].'"'.$sel.'>'.e($city['province_name'].' — '.$city['name']).'</option>'; }
    return $html.'</select>';
}
?>
