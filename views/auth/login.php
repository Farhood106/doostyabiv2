<div class="card narrow">
    <h1>Login</h1>
    <form method="post" action="/login">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" required></label>
        <label>Password <input type="password" name="password" required></label>
        <button class="button">Login</button>
    </form>
    <p>No account? <a href="/register">Register</a></p>
</div>
