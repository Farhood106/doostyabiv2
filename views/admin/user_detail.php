<h1>User detail</h1>
<section class="card"><h2>Profile</h2><p><strong>Name:</strong> <?= e($profile['first_name'].' '.$profile['last_name']) ?></p><p><strong>Email:</strong> <?= e($profile['email']) ?></p><p><strong>Gender:</strong> <?= e($profile['gender']) ?></p><p><strong>Birthdate:</strong> <?= e($profile['birthdate']) ?></p></section>
<section class="card"><h2>Selected goals</h2><?php if (!$goals): ?><p>No goals selected.</p><?php endif; ?><ul><?php foreach ($goals as $goal): ?><li><?= e($goal['title']) ?></li><?php endforeach; ?></ul></section>
<section class="card table-wrap"><h2>Dynamic answers</h2><table><thead><tr><th>Question</th><th>Answer</th></tr></thead><tbody>
<?php foreach ($answers as $answer): $value = $answer['option_labels'] ?: ($answer['city_labels'] ?: ($answer['answer_text'] ?? $answer['answer_number'] ?? $answer['answer_date'] ?? ($answer['answer_boolean'] === null ? '' : ($answer['answer_boolean'] ? 'Yes' : 'No')))); ?>
<tr><td><?= e($answer['title']) ?></td><td><?= e((string)$value) ?></td></tr><?php endforeach; ?>
</tbody></table></section>
