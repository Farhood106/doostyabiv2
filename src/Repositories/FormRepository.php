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
        $steps = $this->db->query('SELECT * FROM form_steps WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
        foreach ($steps as &$step) {
            $step['groups'] = $this->activeGroupsWithQuestions((int)$step['id']);
        }
        return $steps;
    }

    public function allSteps(): array { return $this->db->query('SELECT * FROM form_steps ORDER BY sort_order, id')->fetchAll(); }
    public function allGroups(): array { return $this->db->query('SELECT qg.*, fs.title AS step_title FROM question_groups qg JOIN form_steps fs ON fs.id=qg.form_step_id ORDER BY fs.sort_order, qg.sort_order')->fetchAll(); }
    public function allQuestions(): array { return $this->db->query('SELECT q.*, qg.title AS group_title FROM questions q JOIN question_groups qg ON qg.id=q.question_group_id ORDER BY qg.sort_order, q.sort_order')->fetchAll(); }

    public function activeGroupsWithQuestions(int $stepId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM question_groups WHERE form_step_id=? AND is_active=1 ORDER BY sort_order, id');
        $stmt->execute([$stepId]);
        $groups = $stmt->fetchAll();
        foreach ($groups as &$group) {
            $group['questions'] = $this->activeQuestionsForGroup((int)$group['id']);
        }
        return $groups;
    }

    public function activeQuestionsForGroup(int $groupId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE question_group_id=? AND is_active=1 ORDER BY sort_order, id');
        $stmt->execute([$groupId]);
        $questions = $stmt->fetchAll();
        foreach ($questions as &$question) {
            $question['options'] = $this->optionsForQuestion((int)$question['id'], true);
        }
        return $questions;
    }

    public function optionsForQuestion(int $questionId, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM question_options WHERE question_id=?' . ($activeOnly ? ' AND is_active=1' : '') . ' ORDER BY sort_order, id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public function findQuestion(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id=?');
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
            $stmt = $this->db->prepare("UPDATE questions SET $sets WHERE id=?");
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
        $this->db->prepare('DELETE FROM question_options WHERE question_id=?')->execute([$questionId]);
        $stmt = $this->db->prepare('INSERT INTO question_options (question_id,label,value,sort_order,is_active) VALUES (?,?,?,?,?)');
        foreach ($options as $i => $option) {
            $label = trim($option['label'] ?? '');
            $value = trim($option['value'] ?? $label);
            if ($label !== '') {
                $stmt->execute([$questionId, $label, $value, (int)($option['sort_order'] ?? ($i + 1) * 10), !empty($option['is_active']) ? 1 : 0]);
            }
        }
    }


    public function deleteQuestion(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM questions WHERE id=?');
        $stmt->execute([$id]);
    }

    public function saveStep(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO form_steps (title,description,sort_order,is_active) VALUES (?,?,?,?)');
        $stmt->execute([$data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0]);
    }

    public function saveGroup(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO question_groups (form_step_id,title,description,sort_order,is_active) VALUES (?,?,?,?,?)');
        $stmt->execute([(int)$data['form_step_id'], $data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0]);
    }
}
