<nav class="bg-blue-600 text-white p-4 shadow-lg flex justify-between">
  <div class="text-lg font-bold">EduPulse</div>
  <div>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="dashboard.php" class="px-3">Dashboard</a>
      <?php if($_SESSION['role']=='admin'): ?>
        <a href="classes/manage.php" class="px-3">Manage Classes</a>
      <?php endif; ?>
      <a href="logout.php" class="px-3">Logout</a>
    <?php else: ?>
      <a href="login.php" class="px-3">Login</a>
      <a href="register.php" class="px-3">Register</a>
    <?php endif; ?>
  </div>
</nav>
