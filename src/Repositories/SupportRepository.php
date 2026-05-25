<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class SupportRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }
    public function categories(): array { return ['technical_support','emotional_guidance','reporting_help','onboarding_help','conversation_help','safety_help']; }

    public function listForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT sc.*, sp.unread_count FROM support_conversations sc LEFT JOIN support_participants sp ON sp.conversation_id=sc.id AND sp.user_id=? WHERE sc.owner_user_id=? ORDER BY COALESCE(sc.last_activity_at, sc.updated_at) DESC LIMIT 200');
        $stmt->execute([$userId,$userId]);
        return $stmt->fetchAll();
    }

    public function createConversation(int $ownerId, string $category, string $subject, string $message): int
    {
        $category = in_array($category, $this->categories(), true) ? $category : 'technical_support';
        $stmt = $this->db->prepare('INSERT INTO support_conversations (owner_user_id,category,subject,status,last_activity_at) VALUES (?,?,?,"open",NOW())');
        $stmt->execute([$ownerId, $category, $subject]);
        $id = (int)$this->db->lastInsertId();
        $this->db->prepare('INSERT INTO support_participants (conversation_id,user_id,role_in_conversation) VALUES (?,? ,"member")')->execute([$id, $ownerId]);
        $this->addMessage($id, $ownerId, $message, false);
        return $id;
    }

    public function addMessage(int $conversationId, int $senderId, string $body, bool $internal): bool
    {
        $type = $internal ? 'internal_note' : 'member_message';
        $stmt = $this->db->prepare('INSERT INTO support_messages (conversation_id,sender_user_id,body,is_internal_note,message_type) VALUES (?,?,?,?,?)');
        $ok = $stmt->execute([$conversationId, $senderId, $body, $internal ? 1 : 0, $type]);
        if ($ok) {
            $this->db->prepare('UPDATE support_conversations SET updated_at=NOW(), last_activity_at=NOW(), status=IF(status="closed","open",status) WHERE id=?')->execute([$conversationId]);
            $this->db->prepare('UPDATE support_participants SET unread_count=unread_count+1 WHERE conversation_id=? AND user_id<>?')->execute([$conversationId,$senderId]);
            $this->db->prepare('UPDATE support_participants SET unread_count=0 WHERE conversation_id=? AND user_id=?')->execute([$conversationId,$senderId]);
        }
        return $ok;
    }
    public function markRead(int $conversationId,int $userId): void { $this->db->prepare('UPDATE support_participants SET unread_count=0 WHERE conversation_id=? AND user_id=?')->execute([$conversationId,$userId]); }
    public function canAccess(int $conversationId, int $userId, bool $adminBypass): bool { if ($adminBypass) { return true; } $stmt=$this->db->prepare('SELECT 1 FROM support_participants WHERE conversation_id=? AND user_id=? LIMIT 1'); $stmt->execute([$conversationId,$userId]); return (bool)$stmt->fetchColumn(); }
    public function conversation(int $id): ?array { $s=$this->db->prepare('SELECT * FROM support_conversations WHERE id=? LIMIT 1'); $s->execute([$id]); return $s->fetch() ?: null; }
    public function messages(int $id, bool $includeInternal=false): array { $sql='SELECT sm.*, u.first_name, u.last_name, r.name AS role_name FROM support_messages sm JOIN users u ON u.id=sm.sender_user_id JOIN roles r ON r.id=u.role_id WHERE sm.conversation_id=?'.($includeInternal?'':' AND sm.is_internal_note=0').' ORDER BY sm.created_at'; $s=$this->db->prepare($sql); $s->execute([$id]); return $s->fetchAll(); }
    public function allForAdmin(array $f=[]): array { $status=$f['status']??''; $category=$f['category']??''; $priority=$f['priority']??''; $sql='SELECT sc.*, u.first_name, u.last_name, sa.assigned_user_id, au.first_name AS assigned_first_name, au.last_name AS assigned_last_name, COALESCE(SUM(sp.unread_count),0) AS unread_total FROM support_conversations sc JOIN users u ON u.id=sc.owner_user_id LEFT JOIN support_assignments sa ON sa.conversation_id=sc.id LEFT JOIN users au ON au.id=sa.assigned_user_id LEFT JOIN support_participants sp ON sp.conversation_id=sc.id WHERE 1=1'; $p=[]; if($status!==''){ $sql.=' AND sc.status=?'; $p[]=$status; } if($category!==''){ $sql.=' AND sc.category=?'; $p[]=$category; } if($priority!==''){ $sql.=' AND sc.priority=?'; $p[]=$priority; } $sql.=' GROUP BY sc.id ORDER BY COALESCE(sc.last_activity_at, sc.updated_at) DESC LIMIT 300'; $s=$this->db->prepare($sql); $s->execute($p); return $s->fetchAll(); }
    public function assign(int $conversationId,int $adminId,int $assigneeId,string $role): void { $this->db->prepare('INSERT INTO support_assignments (conversation_id,assigned_user_id,assigned_role,assigned_by_user_id) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE assigned_user_id=VALUES(assigned_user_id), assigned_role=VALUES(assigned_role), assigned_by_user_id=VALUES(assigned_by_user_id), assigned_at=NOW()')->execute([$conversationId,$assigneeId,$role,$adminId]); $this->db->prepare('INSERT IGNORE INTO support_participants (conversation_id,user_id,role_in_conversation) VALUES (?,?,?)')->execute([$conversationId,$assigneeId,$role]); }
    public function close(int $id): void { $this->db->prepare('UPDATE support_conversations SET status="closed", closed_at=NOW() WHERE id=?')->execute([$id]); }
    public function reopen(int $id): void { $this->db->prepare('UPDATE support_conversations SET status="open", closed_at=NULL WHERE id=?')->execute([$id]); }
    public function archive(int $id): void { $this->db->prepare('UPDATE support_conversations SET status="archived" WHERE id=?')->execute([$id]); }
    public function quickReplies(): array { return $this->db->query('SELECT * FROM support_quick_replies ORDER BY category, sort_order, id')->fetchAll(); }
    public function saveQuickReply(array $data): int { $id=(int)($data['id']??0); if($id>0){$this->db->prepare('UPDATE support_quick_replies SET category=?,title=?,body=?,sort_order=?,is_active=? WHERE id=?')->execute([$data['category'],$data['title'],$data['body'],(int)($data['sort_order']??0),!empty($data['is_active'])?1:0,$id]); return $id;} $this->db->prepare('INSERT INTO support_quick_replies (category,title,body,sort_order,is_active,created_by_user_id) VALUES (?,?,?,?,?,?)')->execute([$data['category'],$data['title'],$data['body'],(int)($data['sort_order']??0),!empty($data['is_active'])?1:0,(int)$data['created_by_user_id']]); return (int)$this->db->lastInsertId(); }
    public function deleteQuickReply(int $id): void { $this->db->prepare('DELETE FROM support_quick_replies WHERE id=?')->execute([$id]); }
}
