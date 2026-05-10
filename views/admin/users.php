<h1>اعضا</h1>
<div class="card table-wrap"><table><thead><tr><th>نام</th><th>ایمیل</th><th>کیفیت شناخت‌نامه</th><th>اعتماد</th><th>زمان عضویت</th><th></th></tr></thead><tbody>
<?php foreach ($users as $member): ?><tr><td><?= e($member['first_name'].' '.$member['last_name']) ?></td><td><?= e($member['email']) ?></td><td><span class="pill"><?= (int)round((float)($member['profile_quality_score'] ?? 0)) ?>٪ · <?= e(fa_label($member['profile_quality_level'] ?? 'not_ready')) ?></span></td><td><span class="pill"><?= (int)round((float)($member['trust_score'] ?? 0)) ?>٪ · <?= e(fa_label($member['trust_level'] ?? 'new')) ?></span></td><td><?= e($member['created_at']) ?></td><td><a href="/admin/users/show?id=<?= (int)$member['id'] ?>">جزئیات</a></td></tr><?php endforeach; ?>
</tbody></table></div>
