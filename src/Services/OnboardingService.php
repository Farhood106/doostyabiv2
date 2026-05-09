<?php
namespace App\Services;

use App\Repositories\AnswerRepository;
use App\Repositories\FormRepository;
use App\Repositories\GoalRepository;
use App\Repositories\LocationRepository;

class OnboardingService
{
    public function submit(int $userId, array $input): array
    {
        $form = new FormRepository();
        $steps = $form->activeSteps();
        $questions = [];
        foreach ($steps as $step) { foreach ($step['groups'] as $group) { foreach ($group['questions'] as $question) { $questions[$question['id']] = $question; } } }
        $errors = [];
        $normalized = [];
        foreach ($questions as $id => $question) {
            $value = $input['answers'][$id] ?? null;
            if ($question['is_required'] && ($value === null || $value === '' || $value === [])) { $errors[$id] = 'This answer is required.'; continue; }
            if ($value === null || $value === '' || $value === []) { continue; }
            [$valid, $clean] = $this->validateAndNormalize($question, $value, $form);
            if (!$valid) { $errors[$id] = 'Invalid answer format.'; continue; }
            $normalized[$id] = $clean;
        }
        if ($errors) { return [false, $errors]; }
        (new GoalRepository())->syncUserGoals($userId, $input['goals'] ?? []);
        $answers = new AnswerRepository();
        foreach ($normalized as $id => $value) { $answers->saveAnswer($userId, $questions[$id], $value); }
        $answeredRequired = 0;
        $existing = $answers->existingForUser($userId);
        foreach ($questions as $id => $question) { if (!$question['is_required'] || isset($existing[$id]) || isset($normalized[$id])) { $answeredRequired++; } }
        $complete = $answeredRequired === count($questions);
        $answers->updateProgress($userId, $complete ? null : ($steps[0]['id'] ?? null), $complete ? count($steps) : 0, $complete);
        return [true, []];
    }

    private function validateAndNormalize(array $question, mixed $value, FormRepository $form): array
    {
        return match ($question['answer_type']) {
            'number' => is_numeric($value) ? [true, (string)$value] : [false, null],
            'scale' => is_numeric($value) && (float)$value >= 1 && (float)$value <= 10 ? [true, (string)$value] : [false, null],
            'boolean' => in_array($value, ['0','1'], true) ? [true, $value] : [false, null],
            'date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value) === 1 ? [true, (string)$value] : [false, null],
            'single_choice','select' => $this->validateOptions($form, (int)$question['id'], [$value], false),
            'multi_choice','multi_select' => is_array($value) ? $this->validateOptions($form, (int)$question['id'], $value, true) : [false, null],
            'city_single' => $this->validateCities([$value], false),
            'city_multi' => is_array($value) ? $this->validateCities($value, true) : [false, null],
            'text','textarea','range' => is_scalar($value) && strlen(trim((string)$value)) <= 5000 ? [true, trim((string)$value)] : [false, null],
            default => [false, null],
        };
    }
    private function validateOptions(FormRepository $form, int $questionId, array $values, bool $multi): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $values))));
        if (!$ids || (!$multi && count($ids) !== 1)) { return [false, null]; }
        $valid = $form->validOptionIdsForQuestion($questionId, $ids);
        sort($ids); sort($valid);
        return $ids === $valid ? [true, $multi ? $ids : (string)$ids[0]] : [false, null];
    }
    private function validateCities(array $values, bool $multi): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $values))));
        if (!$ids || (!$multi && count($ids) !== 1)) { return [false, null]; }
        $valid = (new LocationRepository())->validCityIds($ids);
        sort($ids); sort($valid);
        return $ids === $valid ? [true, $multi ? $ids : (string)$ids[0]] : [false, null];
    }
}
