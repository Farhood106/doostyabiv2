<div class="card narrow">
    <h1>Create account</h1>
    <form method="post" action="/register">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required></label><span class="error-text"><?= e($errors['email'] ?? '') ?></span>
        <label>First name <input name="first_name" value="<?= e($old['first_name'] ?? '') ?>" required></label><span class="error-text"><?= e($errors['first_name'] ?? '') ?></span>
        <label>Last name <input name="last_name" value="<?= e($old['last_name'] ?? '') ?>"></label>
        <label>Gender <input name="gender" value="<?= e($old['gender'] ?? '') ?>"></label>
        <label>Birthdate <input type="date" name="birthdate" value="<?= e($old['birthdate'] ?? '') ?>"></label>
        <label>Password <input type="password" name="password" required></label><span class="error-text"><?= e($errors['password'] ?? '') ?></span>
        <label>Confirm password <input type="password" name="password_confirm" required></label><span class="error-text"><?= e($errors['password_confirm'] ?? '') ?></span>
        <button class="button">Register</button>
    </form>
</div>
