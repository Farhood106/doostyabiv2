<?php
namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\FormRepository;

class FormBuilderService
{
    public const TYPES = ['text','textarea','number','boolean','single_choice','multi_choice','select','multi_select','scale','range','city_single','city_multi','date'];

    public function saveQuestion(array $input, int $adminId): array
    {
        $errors = [];
        if (empty($input['question_group_id'])) { $errors['question_group_id'] = 'Group is required.'; }
        if (trim($input['title'] ?? '') === '') { $errors['title'] = 'Title is required.'; }
        if (!in_array($input['answer_type'] ?? '', self::TYPES, true)) { $errors['answer_type'] = 'Invalid answer type.'; }
        if ($errors) { return [false, $errors, null]; }
        $type = $input['answer_type'];
        $data = [
            'id' => $input['id'] ?? null,
            'question_group_id' => (int)$input['question_group_id'],
            'title' => trim($input['title']),
            'description' => trim($input['description'] ?? '') ?: null,
            'help_text' => trim($input['help_text'] ?? '') ?: null,
            'placeholder' => trim($input['placeholder'] ?? '') ?: null,
            'answer_type' => $type,
            'answer_source' => in_array($type, ['single_choice','multi_choice','select','multi_select'], true) ? 'options' : (in_array($type, ['city_single','city_multi'], true) ? 'cities' : 'manual'),
            'applies_to' => trim($input['applies_to'] ?? 'all') ?: 'all',
            'visibility_scope' => $input['visibility_scope'] ?? 'private',
            'is_required' => !empty($input['is_required']) ? 1 : 0,
            'is_active' => !empty($input['is_active']) ? 1 : 0,
            'is_sensitive' => !empty($input['is_sensitive']) ? 1 : 0,
            'privacy_level' => $input['privacy_level'] ?? 'medium',
            'show_in_match_card' => !empty($input['show_in_match_card']) ? 1 : 0,
            'match_card_priority' => (int)($input['match_card_priority'] ?? 0),
            'is_matchable' => !empty($input['is_matchable']) ? 1 : 0,
            'match_weight' => (float)($input['match_weight'] ?? 1),
            'match_rule' => trim($input['match_rule'] ?? 'exact') ?: 'exact',
            'sort_order' => (int)($input['sort_order'] ?? 0),
        ];
        $repo = new FormRepository();
        $id = $repo->saveQuestion($data);
        if ($data['answer_source'] === 'options') {
            $repo->replaceOptions($id, $input['options'] ?? []);
        }
        (new AuditLogRepository())->record($adminId, empty($input['id']) ? 'created' : 'updated', 'question', $id, ['title' => $data['title']]);
        return [true, [], $id];
    }
}
