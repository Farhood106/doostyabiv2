<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class FormRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function activeSteps(): array
    {
        $steps = $this->db->query('SELECT * FROM form_steps WHERE is_active=1 AND deleted_at IS NULL ORDER BY sort_order, id')->fetchAll();
        foreach ($steps as &$step) { $step['groups'] = $this->activeGroupsWithQuestions((int)$step['id']); }
        return $steps;
    }
    public function allSteps(): array { return $this->db->query('SELECT * FROM form_steps WHERE deleted_at IS NULL ORDER BY sort_order, id')->fetchAll(); }
    public function allGroups(): array { return $this->db->query('SELECT qg.*, fs.title AS step_title FROM question_groups qg JOIN form_steps fs ON fs.id=qg.form_step_id WHERE qg.deleted_at IS NULL AND fs.deleted_at IS NULL ORDER BY fs.sort_order, qg.sort_order')->fetchAll(); }
    public function allQuestions(): array { return $this->db->query('SELECT q.*, qg.title AS group_title FROM questions q JOIN question_groups qg ON qg.id=q.question_group_id WHERE q.deleted_at IS NULL ORDER BY qg.sort_order, q.sort_order')->fetchAll(); }

    public function activeGroupsWithQuestions(int $stepId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM question_groups WHERE form_step_id=? AND is_active=1 AND deleted_at IS NULL ORDER BY sort_order, id');
        $stmt->execute([$stepId]);
        $groups = $stmt->fetchAll();
        foreach ($groups as &$group) { $group['questions'] = $this->activeQuestionsForGroup((int)$group['id']); }
        return $groups;
    }
    public function activeQuestionsForGroup(int $groupId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE question_group_id=? AND is_active=1 AND deleted_at IS NULL ORDER BY sort_order, id');
        $stmt->execute([$groupId]);
        $questions = $stmt->fetchAll();
        foreach ($questions as &$question) { $question['options'] = $this->optionsForQuestion((int)$question['id'], true); }
        return $questions;
    }
    public function optionsForQuestion(int $questionId, bool $activeOnly = false): array
    {
        $sql = 'SELECT qo.*, (SELECT COUNT(*) FROM user_answer_options uao WHERE uao.question_option_id=qo.id) AS answer_count FROM question_options qo WHERE qo.question_id=? AND qo.deleted_at IS NULL' . ($activeOnly ? ' AND qo.is_active=1' : '') . ' ORDER BY qo.sort_order, qo.id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }
    public function validOptionIdsForQuestion(int $questionId, array $optionIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $optionIds))));
        if (!$ids) { return []; }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id FROM question_options WHERE question_id=? AND id IN ($in) AND is_active=1 AND deleted_at IS NULL");
        $stmt->execute([$questionId, ...$ids]);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }
    public function findQuestion(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT q.*, qg.title AS group_title, fs.title AS step_title FROM questions q JOIN question_groups qg ON qg.id=q.question_group_id JOIN form_steps fs ON fs.id=qg.form_step_id WHERE q.id=? AND q.deleted_at IS NULL');
        $stmt->execute([$id]);
        $question = $stmt->fetch() ?: null;
        if ($question) { $question['options'] = $this->optionsForQuestion($id); }
        return $question;
    }
    public function saveQuestion(array $data): int
    {
        $fields = ['question_group_id','title','description','help_text','placeholder','answer_type','answer_source','applies_to','visibility_scope','is_required','is_active','is_sensitive','privacy_level','show_in_match_card','match_card_priority','is_matchable','match_weight','match_rule','sort_order'];
        if (!empty($data['id'])) {
            $sets = implode(',', array_map(fn($f) => "$f=?", $fields));
            $stmt = $this->db->prepare("UPDATE questions SET $sets WHERE id=? AND deleted_at IS NULL");
            $stmt->execute([...array_map(fn($f) => $data[$f], $fields), $data['id']]);
            return (int)$data['id'];
        }
        $cols = implode(',', $fields);
        $placeholders = rtrim(str_repeat('?,', count($fields)), ',');
        $stmt = $this->db->prepare("INSERT INTO questions ($cols) VALUES ($placeholders)");
        $stmt->execute(array_map(fn($f) => $data[$f], $fields));
        return (int)$this->db->lastInsertId();
    }
    public function replaceOptions(int $questionId, array $options): void
    {
        $keptIds = [];
        foreach ($options as $i => $option) {
            $label = trim($option['label'] ?? '');
            if ($label === '') { continue; }
            $value = trim($option['value'] ?? $label) ?: $label;
            $description = trim($option['description'] ?? '') ?: null;
            $active = !empty($option['is_active']) ? 1 : 0;
            if (!empty($option['id'])) {
                $optionId = (int)$option['id'];
                $stmt = $this->db->prepare('UPDATE question_options SET label=?, value=?, description=?, sort_order=?, is_active=?, deleted_at=NULL WHERE id=? AND question_id=?');
                $stmt->execute([$label, $value, $description, (int)($option['sort_order'] ?? ($i + 1) * 10), $active, $optionId, $questionId]);
                $keptIds[] = $optionId;
            } else {
                $stmt = $this->db->prepare('INSERT INTO question_options (question_id,label,value,description,sort_order,is_active) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$questionId, $label, $value, $description, (int)($option['sort_order'] ?? ($i + 1) * 10), $active]);
                $keptIds[] = (int)$this->db->lastInsertId();
            }
        }
        if ($keptIds) {
            $in = implode(',', array_fill(0, count($keptIds), '?'));
            $stmt = $this->db->prepare("UPDATE question_options SET is_active=0, deleted_at=NOW() WHERE question_id=? AND id NOT IN ($in)");
            $stmt->execute([$questionId, ...$keptIds]);
        } else {
            $this->db->prepare('UPDATE question_options SET is_active=0, deleted_at=NOW() WHERE question_id=?')->execute([$questionId]);
        }
    }
    public function saveStep(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE form_steps SET title=?, description=?, sort_order=?, is_active=? WHERE id=? AND deleted_at IS NULL');
            $stmt->execute([$data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0, (int)$data['id']]);
            return (int)$data['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO form_steps (title,description,sort_order,is_active) VALUES (?,?,?,?)');
        $stmt->execute([$data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }
    public function saveGroup(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE question_groups SET form_step_id=?, title=?, description=?, sort_order=?, is_active=? WHERE id=? AND deleted_at IS NULL');
            $stmt->execute([(int)$data['form_step_id'], $data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0, (int)$data['id']]);
            return (int)$data['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO question_groups (form_step_id,title,description,sort_order,is_active) VALUES (?,?,?,?,?)');
        $stmt->execute([(int)$data['form_step_id'], $data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }
    public function softDeleteStep(int $id): void { $this->db->prepare('UPDATE form_steps SET is_active=0, deleted_at=NOW() WHERE id=?')->execute([$id]); }
    public function softDeleteGroup(int $id): void { $this->db->prepare('UPDATE question_groups SET is_active=0, deleted_at=NOW() WHERE id=?')->execute([$id]); }
    public function softDeleteQuestion(int $id): void { $this->db->prepare('UPDATE questions SET is_active=0, deleted_at=NOW() WHERE id=?')->execute([$id]); }

    public function groupedQuestionsWithAnswersForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT fs.title AS step_title, qg.title AS group_title, q.title, q.answer_type, q.privacy_level, q.is_matchable,
            ua.answer_text, ua.answer_number, ua.answer_boolean, ua.answer_date,
            GROUP_CONCAT(DISTINCT qo.label ORDER BY qo.sort_order SEPARATOR ', ') AS option_labels,
            GROUP_CONCAT(DISTINCT CONCAT(c.name, ', ', p.name) ORDER BY p.sort_order,c.sort_order SEPARATOR ', ') AS city_labels
            FROM questions q
            JOIN question_groups qg ON qg.id=q.question_group_id
            JOIN form_steps fs ON fs.id=qg.form_step_id
            LEFT JOIN user_answers ua ON ua.question_id=q.id AND ua.user_id=?
            LEFT JOIN user_answer_options uao ON uao.user_answer_id=ua.id
            LEFT JOIN question_options qo ON qo.id=uao.question_option_id
            LEFT JOIN user_answer_cities uac ON uac.user_answer_id=ua.id
            LEFT JOIN cities c ON c.id=uac.city_id
            LEFT JOIN provinces p ON p.id=c.province_id
            WHERE q.deleted_at IS NULL AND qg.deleted_at IS NULL AND fs.deleted_at IS NULL
            GROUP BY fs.id, qg.id, q.id, fs.title, qg.title, q.title, q.answer_type, q.privacy_level, q.is_matchable, ua.answer_text, ua.answer_number, ua.answer_boolean, ua.answer_date
            ORDER BY fs.sort_order, qg.sort_order, q.sort_order");
        $stmt->execute([$userId]);
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[$row['step_title']][$row['group_title']][] = $row;
        }
        return $grouped;
    }
}
