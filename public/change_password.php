<?php
require_once __DIR__ . '/bootstrap.php';
use Hospital\Repositories\UsersRepository;

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$usersRepo = new UsersRepository();
$err = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$current || !$new || !$confirm) {
        $err = 'All fields required';
    } elseif ($new !== $confirm) {
        $err = 'New passwords do not match';
    } else {
        // verify current
        $pdo = \Hospital\DB::getPDO();
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $_SESSION['user']['username']]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || !password_verify($current, $row['password_hash'])) {
            $err = 'Current password incorrect';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $usersRepo->updatePassword($_SESSION['user']['username'], $hash);
            $ok = 'Password updated successfully';
        }
    }
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Change Password</title>
  <link rel="stylesheet" href="assets/style.css">
  </head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">U</div><div><div style="font-weight:700">Change Password</div><div class="small">Update your account password</div></div></div>
    <div class="controls"><a class="btn secondary" href="index.php">Back</a></div>
  </div>

  <div class="card">
    <?php if ($err): ?><div style="color:#b91c1c;margin-bottom:8px"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <?php if ($ok): ?><div style="color:#15803d;margin-bottom:8px"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>
    <form method="post">
      <div class="form-row">
        <div class="field"><label class="small">Current password</label><input class="input" type="password" name="current"></div>
      </div>
      <div class="form-row">
        <div class="field"><label class="small">New password</label><input class="input" type="password" name="new"></div>
      </div>
      <div class="form-row">
        <div class="field"><label class="small">Confirm new password</label><input class="input" type="password" name="confirm"></div>
      </div>
      <button class="btn" type="submit">Change Password</button>
    </form>
  </div>
</div>
</body>
</html>
