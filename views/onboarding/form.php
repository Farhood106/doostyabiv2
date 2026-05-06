<h1>Onboarding</h1>
<p class="muted">Questions, options, goals, and cities are loaded from the database.</p>
<form method="post" action="/onboarding">
<?= csrf_field() ?>
<section class="card"><h2>Your goals</h2><div class="checks"><?php foreach ($goals as $goal): ?><label><input type="checkbox" name="goals[]" value="<?= (int)$goal['id'] ?>" <?= in_array((int)$goal['id'], $selectedGoalIds ?? [], true) ? 'checked' : '' ?>> <?= e($goal['title']) ?></label><?php endforeach; ?></div></section>
<?php foreach ($steps as $step): ?><section class="card"><h2><?= e($step['title']) ?></h2><p><?= e($step['description']) ?></p><?php foreach ($step['groups'] as $group): ?><fieldset><legend><?= e($group['title']) ?></legend><?php foreach ($group['questions'] as $question): ?><div class="field"><label><?= e($question['title']) ?><?= $question['is_required'] ? ' *' : '' ?><?php if ($question['description']): ?><small><?= e($question['description']) ?></small><?php endif; ?><?= render_question_input($question, $cities, $existingAnswers[$question['id']] ?? null) ?></label><?php if (!empty($errors[$question['id']])): ?><span class="error-text"><?= e($errors[$question['id']]) ?></span><?php endif; ?><?php if ($question['help_text']): ?><small class="muted"><?= e($question['help_text']) ?></small><?php endif; ?></div><?php endforeach; ?></fieldset><?php endforeach; ?></section><?php endforeach; ?>
<button class="button large">Save onboarding</button>
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
        case 'range': return '<input name="'.$name.'" placeholder="'.$ph.'" value="'.$textValue.'"'.$req.'>';
        case 'single_choice': return choice_inputs($q, $name, false, $req, $existing['option_ids'] ?? []);
        case 'multi_choice': return choice_inputs($q, $name.'[]', true, '', $existing['option_ids'] ?? []);
        case 'select': return select_input($q, $name, false, $req, $existing['option_ids'] ?? []);
        case 'multi_select': return select_input($q, $name.'[]', true, $req, $existing['option_ids'] ?? []);
        case 'city_single': return city_select($cities, $name, false, $req, $existing['city_ids'] ?? []);
        case 'city_multi': return city_select($cities, $name.'[]', true, $req, $existing['city_ids'] ?? []);
        default: return '<input name="'.$name.'" placeholder="'.$ph.'" value="'.$textValue.'"'.$req.'>';
    }
}
function choice_inputs(array $q, string $name, bool $multi, string $req, array $selected): string { $type = $multi ? 'checkbox' : 'radio'; $html = '<div class="checks">'; foreach ($q['options'] as $o) { $sel = in_array((int)$o['id'], $selected, true) ? ' checked' : ''; $html .= '<label><input type="'.$type.'" name="'.$name.'" value="'.(int)$o['id'].'"'.$req.$sel.'> '.e($o['label']).'</label>'; } return $html.'</div>'; }
function select_input(array $q, string $name, bool $multi, string $req, array $selected): string { $html = '<select name="'.$name.'"'.($multi ? ' multiple' : '').$req.'>'; if (!$multi) { $html .= '<option value="">Choose</option>'; } foreach ($q['options'] as $o) { $sel = in_array((int)$o['id'], $selected, true) ? ' selected' : ''; $html .= '<option value="'.(int)$o['id'].'"'.$sel.'>'.e($o['label']).'</option>'; } return $html.'</select>'; }
function city_select(array $cities, string $name, bool $multi, string $req, array $selected): string { $html = '<select name="'.$name.'"'.($multi ? ' multiple' : '').$req.'>'; if (!$multi) { $html .= '<option value="">Choose</option>'; } foreach ($cities as $c) { $sel = in_array((int)$c['id'], $selected, true) ? ' selected' : ''; $html .= '<option value="'.(int)$c['id'].'"'.$sel.'>'.e($c['name'].', '.$c['province_name']).'</option>'; } return $html.'</select>'; }
?>
