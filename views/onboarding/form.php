<?php
$totalQuestions = 0; $answeredQuestions = 0;
foreach ($steps as $step) { foreach ($step['groups'] as $group) { foreach ($group['questions'] as $question) { $totalQuestions++; if (!empty($existingAnswers[$question['id']])) { $answeredQuestions++; } } } }
$percent = $totalQuestions ? (int)round(($answeredQuestions / $totalQuestions) * 100) : 0;
$hasErrors = !empty($errors);
?>
<section class="section-title onboarding-title">
    <div><span class="eyebrow">Private onboarding</span><h1>Shape your compatibility profile</h1><p class="muted">Answer at your pace. Your private answers guide matching and are not turned into a public profile.</p></div>
    <span class="pill progress-pill"><?= $percent ?>% complete</span>
</section>
<section class="card progress-card">
    <div class="progress"><span style="width: <?= $percent ?>%"></span></div>
    <div class="section-title"><p class="help"><?= (int)$answeredQuestions ?> of <?= (int)$totalQuestions ?> questions answered.</p><a href="/safety">Review safety settings</a></div>
</section>
<?php if (!$steps): ?><section class="card empty-state"><h2>Onboarding is being prepared</h2><p>No active onboarding steps are available yet. Please check back after an admin publishes the form.</p></section><?php endif; ?>
<?php if ($hasErrors): ?><section class="alert error"><strong>Some answers need attention.</strong><p>Please review the highlighted questions below. Required answers are marked with an asterisk.</p></section><?php endif; ?>
<form method="post" action="/onboarding" class="onboarding-form">
<?= csrf_field() ?>
<section class="card goal-card"><div class="section-title"><div><h2>Your relationship goals</h2><p class="muted">Choose the goals that best describe what you are open to. These help avoid mismatched introductions.</p></div><span class="pill">Private signal</span></div><?php if (!$goals): ?><p class="muted">Goals are not available yet.</p><?php else: ?><div class="checks choice-cloud"><?php foreach ($goals as $goal): ?><label><input type="checkbox" name="goals[]" value="<?= (int)$goal['id'] ?>" <?= in_array((int)$goal['id'], $selectedGoalIds ?? [], true) ? 'checked' : '' ?>> <?= e($goal['title']) ?></label><?php endforeach; ?></div><?php endif; ?></section>
<?php foreach ($steps as $stepIndex => $step): ?>
<section class="card onboarding-step" id="step-<?= (int)$step['id'] ?>">
    <div class="section-title"><div><span class="eyebrow">Step <?= $stepIndex + 1 ?></span><h2><?= e($step['title']) ?></h2><?php if ($step['description']): ?><p class="muted"><?= e($step['description']) ?></p><?php else: ?><p class="muted">Share what feels relevant for better compatibility.</p><?php endif; ?></div><span class="pill"><?= count($step['groups']) ?> section<?= count($step['groups']) === 1 ? '' : 's' ?></span></div>
    <?php foreach ($step['groups'] as $group): ?>
        <fieldset class="question-group"><legend><?= e($group['title']) ?></legend><?php if ($group['description']): ?><p class="help"><?= e($group['description']) ?></p><?php endif; ?>
        <?php foreach ($group['questions'] as $question): ?>
            <div class="field question-card <?= !empty($errors[$question['id']]) ? 'has-error' : '' ?>">
                <label><span><?= e($question['title']) ?><?= $question['is_required'] ? ' *' : '' ?></span><?php if ($question['description']): ?><small><?= e($question['description']) ?></small><?php endif; ?><?= render_question_input($question, $cities, $existingAnswers[$question['id']] ?? null) ?></label>
                <?php if (!empty($errors[$question['id']])): ?><span class="error-text"><?= e($errors[$question['id']]) ?></span><?php endif; ?>
                <?php if ($question['help_text']): ?><small class="muted"><?= e($question['help_text']) ?></small><?php endif; ?>
            </div>
        <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>
    <button class="button secondary" type="submit" name="save_continue" value="<?= (int)$step['id'] ?>">Save this step</button>
</section>
<?php endforeach; ?>
<section class="card save-panel"><h2>Ready to save?</h2><p class="muted">You can return anytime to update your answers as your preferences become clearer.</p><button class="button large">Save onboarding</button></section>
</form>
<?php
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
        case 'boolean': $b = $existing['answer_boolean'] ?? ''; return '<select name="'.$name.'"'.$req.'><option value="">Choose</option><option value="1"'.($b === 1 || $b === '1' ? ' selected' : '').'>Yes</option><option value="0"'.($b === 0 || $b === '0' ? ' selected' : '').'>No</option></select>';
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
    $selected = array_map('intval', $existing['options'] ?? []);
    if (!$multi) { $html = '<select name="'.$name.'"'.$req.'><option value="">Choose</option>'; foreach ($q['options'] as $opt) { $sel = in_array((int)$opt['id'], $selected, true) ? ' selected' : ''; $html .= '<option value="'.(int)$opt['id'].'"'.$sel.'>'.e($opt['label']).'</option>'; } return $html.'</select>'; }
    $html = '<div class="checks choice-cloud">'; foreach ($q['options'] as $opt) { $checked = in_array((int)$opt['id'], $selected, true) ? ' checked' : ''; $html .= '<label><input type="checkbox" name="'.$name.'" value="'.(int)$opt['id'].'"'.$checked.'> '.e($opt['label']).'</label>'; } return $html.'</div>';
}
function render_cities(array $cities, string $name, ?array $existing, bool $multi, string $req): string
{
    $selected = array_map('intval', $existing['cities'] ?? []);
    $attr = $multi ? ' multiple size="6"' : '';
    $html = '<select name="'.$name.'"'.$attr.$req.'>'.(!$multi ? '<option value="">Choose city</option>' : '');
    foreach ($cities as $city) { $sel = in_array((int)$city['id'], $selected, true) ? ' selected' : ''; $html .= '<option value="'.(int)$city['id'].'"'.$sel.'>'.e($city['province_name'].' — '.$city['name']).'</option>'; }
    return $html.'</select>';
}
?>
