<?php
require_once __DIR__ . '/bootstrap.php';

// Allow access without login to this page
// If already logged in, redirect
if (isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = $_POST['username'] ?? '';
  $p = $_POST['password'] ?? '';
  if ($u && $p) {
    $u = trim($u);
    $pdo = \Hospital\DB::getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u');
    $stmt->execute([':u' => $u]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($row && password_verify($p, $row['password_hash'])) {
      session_regenerate_id(true);
      $_SESSION['user'] = ['username' => $row['username'], 'role' => $row['role']];
      header('Location: index.php');
      exit;
    }
  }
  $err = 'Invalid credentials';
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>.login-card{max-width:420px;margin:60px auto}</style>
</head>
<body>
  <div class="container">
    <div class="login-card card">
      <h2>Sign in</h2>
      <?php if ($err): ?><div style="color:var(--danger)"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
      <form method="post">
        <div class="form-row"><div class="field"><input name="username" placeholder="Username" class="input"></div></div>
        <div class="form-row"><div class="field"><input name="password" placeholder="Password" type="password" class="input"></div></div>
        <div style="margin-top:8px"><button class="btn" type="submit">Sign in</button></div>
      </form>
    </div>
  </div>
</body>
</html>
