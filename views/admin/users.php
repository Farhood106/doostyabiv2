<h1>Members</h1>
<div class="card table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Joined</th><th></th></tr></thead><tbody>
<?php foreach ($users as $member): ?><tr><td><?= e($member['first_name'].' '.$member['last_name']) ?></td><td><?= e($member['email']) ?></td><td><?= e($member['created_at']) ?></td><td><a href="/admin/users/show?id=<?= (int)$member['id'] ?>">Details</a></td></tr><?php endforeach; ?>
</tbody></table></div>
