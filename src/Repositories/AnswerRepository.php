<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class AnswerRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function saveAnswer(int $userId, array $question, array|string|null $value): void
    {
        $this->db->beginTransaction();
        $type = $question['answer_type'];
        $text = $number = $bool = $date = null;
        if (in_array($type, ['text','textarea','range'], true)) { $text = is_array($value) ? null : trim((string)$value); }
        if (in_array($type, ['number','scale'], true)) { $number = is_numeric($value) ? $value : null; }
        if ($type === 'boolean') { $bool = $value === '1' ? 1 : 0; }
        if ($type === 'date') { $date = $value ?: null; }
        $stmt = $this->db->prepare('INSERT INTO user_answers (user_id, question_id, answer_text, answer_number, answer_boolean, answer_date) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE answer_text=VALUES(answer_text), answer_number=VALUES(answer_number), answer_boolean=VALUES(answer_boolean), answer_date=VALUES(answer_date)');
        $stmt->execute([$userId, $question['id'], $text, $number, $bool, $date]);
        $answerId = (int)$this->db->lastInsertId();
        if ($answerId === 0) {
            $find = $this->db->prepare('SELECT id FROM user_answers WHERE user_id=? AND question_id=?');
            $find->execute([$userId, $question['id']]);
            $answerId = (int)$find->fetchColumn();
        }
        $this->db->prepare('DELETE FROM user_answer_options WHERE user_answer_id=?')->execute([$answerId]);
        $this->db->prepare('DELETE FROM user_answer_cities WHERE user_answer_id=?')->execute([$answerId]);
        if (in_array($type, ['single_choice','multi_choice','select','multi_select'], true)) {
            $values = is_array($value) ? $value : [$value];
            $ins = $this->db->prepare('INSERT IGNORE INTO user_answer_options (user_answer_id, question_option_id) VALUES (?,?)');
            foreach (array_filter(array_map('intval', $values)) as $optionId) { $ins->execute([$answerId, $optionId]); }
        }
        if (in_array($type, ['city_single','city_multi'], true)) {
            $values = is_array($value) ? $value : [$value];
            $ins = $this->db->prepare('INSERT IGNORE INTO user_answer_cities (user_answer_id, city_id) VALUES (?,?)');
            foreach (array_filter(array_map('intval', $values)) as $cityId) { $ins->execute([$answerId, $cityId]); }
        }
        $this->db->commit();
    }

    public function existingForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM user_answers WHERE user_id=?');
        $stmt->execute([$userId]);
        $answers = [];
        foreach ($stmt->fetchAll() as $answer) {
            $id = (int)$answer['id'];
            $answer['option_ids'] = $this->idsFor('user_answer_options', 'question_option_id', $id);
            $answer['city_ids'] = $this->idsFor('user_answer_cities', 'city_id', $id);
            $answers[(int)$answer['question_id']] = $answer;
        }
        return $answers;
    }

    private function idsFor(string $table, string $column, int $answerId): array
    {
        $stmt = $this->db->prepare("SELECT $column FROM $table WHERE user_answer_id=?");
        $stmt->execute([$answerId]);
        return array_map('intval', array_column($stmt->fetchAll(), $column));
    }

    public function readableForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT q.title, q.answer_type, ua.answer_text, ua.answer_number, ua.answer_boolean, ua.answer_date,
            GROUP_CONCAT(DISTINCT qo.label ORDER BY qo.sort_order SEPARATOR ', ') AS option_labels,
            GROUP_CONCAT(DISTINCT CONCAT(c.name, ', ', p.name) ORDER BY p.sort_order,c.sort_order SEPARATOR ', ') AS city_labels
            FROM user_answers ua
            JOIN questions q ON q.id=ua.question_id
            LEFT JOIN user_answer_options uao ON uao.user_answer_id=ua.id
            LEFT JOIN question_options qo ON qo.id=uao.question_option_id
            LEFT JOIN user_answer_cities uac ON uac.user_answer_id=ua.id
            LEFT JOIN cities c ON c.id=uac.city_id
            LEFT JOIN provinces p ON p.id=c.province_id
            WHERE ua.user_id=?
            GROUP BY ua.id, q.title, q.answer_type, ua.answer_text, ua.answer_number, ua.answer_boolean, ua.answer_date
            ORDER BY q.sort_order");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function updateProgress(int $userId, ?int $currentStepId, int $completedSteps, bool $complete): void
    {
        $stmt = $this->db->prepare('INSERT INTO user_onboarding_progress (user_id,current_step_id,completed_steps,is_complete) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE current_step_id=VALUES(current_step_id), completed_steps=VALUES(completed_steps), is_complete=VALUES(is_complete)');
        $stmt->execute([$userId, $currentStepId, $completedSteps, $complete ? 1 : 0]);
    }
}
