<h1>Admin dashboard</h1>
<div class="grid two">
    <div class="card"><h2>Dynamic questions</h2><p class="metric"><?= count($questions) ?></p><a href="/admin/forms">Manage form builder</a></div>
    <div class="card"><h2>Members</h2><p class="metric"><?= count($users) ?></p><a href="/admin/users">View members</a></div>
</div>
