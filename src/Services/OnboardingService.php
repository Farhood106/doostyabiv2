<?php
namespace App\Services;

use App\Repositories\AnswerRepository;
use App\Repositories\FormRepository;
use App\Repositories\GoalRepository;

class OnboardingService
{
    public function submit(int $userId, array $input): array
    {
        $form = new FormRepository();
        $steps = $form->activeSteps();
        $questions = [];
        foreach ($steps as $step) { foreach ($step['groups'] as $group) { foreach ($group['questions'] as $question) { $questions[$question['id']] = $question; } } }
        $errors = [];
        foreach ($questions as $id => $question) {
            $value = $input['answers'][$id] ?? null;
            if ($question['is_required'] && ($value === null || $value === '' || $value === [])) { $errors[$id] = 'This answer is required.'; continue; }
            if ($value === null || $value === '' || $value === []) { continue; }
            if (!$this->validForType($question, $value)) { $errors[$id] = 'Invalid answer format.'; }
        }
        if ($errors) { return [false, $errors]; }
        (new GoalRepository())->syncUserGoals($userId, $input['goals'] ?? []);
        $answers = new AnswerRepository();
        foreach ($questions as $id => $question) {
            $value = $input['answers'][$id] ?? null;
            if ($value !== null && $value !== '' && $value !== []) { $answers->saveAnswer($userId, $question, $value); }
        }
        $answers->updateProgress($userId, null, count($steps), true);
        return [true, []];
    }

    private function validForType(array $question, mixed $value): bool
    {
        return match ($question['answer_type']) {
            'number','scale' => is_numeric($value),
            'boolean' => in_array($value, ['0','1'], true),
            'date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value) === 1,
            'multi_choice','multi_select','city_multi' => is_array($value),
            default => is_scalar($value) || is_array($value),
        };
    }
}
