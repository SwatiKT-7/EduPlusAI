<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

$message="";
if ($_SERVER['REQUEST_METHOD']=="POST") {
    $name=mysqli_real_escape_string($conn,$_POST['name']);
    $email=mysqli_real_escape_string($conn,$_POST['email']);
    $password=md5($_POST['password']);
    $role=$_POST['role'];
    $dept_id=intval($_POST['dept_id']);

    $sql="INSERT INTO users (tenant_id, role, name, email, dept_id, password_hash) 
          VALUES (1,'$role','$name','$email',$dept_id,'$password')";
    if (mysqli_query($conn,$sql)) $message="✅ User added successfully!";
    else $message="❌ Error: ".mysqli_error($conn);
}

$depts=mysqli_query($conn,"SELECT id,name FROM departments ORDER BY name ASC");
$users=mysqli_query($conn,"SELECT u.*,d.name as dept_name 
                           FROM users u 
                           LEFT JOIN departments d ON u.dept_id=d.id
                           ORDER BY u.created_at DESC");
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
  <?php include("includes/sidebar.php"); ?>

  <div class="flex-1 p-6">
    <h2 class="text-3xl font-bold text-indigo-600 mb-6">👥 Users Management</h2>

    <!-- Message -->
    <?php if($message): ?>
      <div class="mb-4 p-3 rounded-lg 
                  <?= strpos($message,'✅')!==false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg mb-8">
      <h3 class="text-xl font-semibold mb-4">➕ Add New User</h3>
      <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" name="name" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" placeholder="Full Name" required>
        <input type="email" name="email" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" placeholder="Email Address" required>
        <input type="password" name="password" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" placeholder="Password" required>

        <select name="role" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" required>
          <option value="">Select Role</option>
          <option value="student">Student</option>
          <option value="faculty">Faculty</option>
          <option value="admin">Admin</option>
        </select>

        <select name="dept_id" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" required>
          <option value="">Select Department</option>
          <?php while($d=mysqli_fetch_assoc($depts)): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
          <?php endwhile; ?>
        </select>

        <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg col-span-1 md:col-span-2 shadow">
          Add User
        </button>
      </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
      <h3 class="text-xl font-semibold mb-4">📋 All Users</h3>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm rounded-lg overflow-hidden shadow">
          <thead class="bg-indigo-50 dark:bg-gray-700 text-left">
            <tr>
              <th class="p-3">Name</th>
              <th class="p-3">Email</th>
              <th class="p-3">Role</th>
              <th class="p-3">Department</th>
              <th class="p-3">Created</th>
            </tr>
          </thead>
          <tbody>
            <?php while($u=mysqli_fetch_assoc($users)): ?>
            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="p-3 font-medium"><?= htmlspecialchars($u['name']) ?></td>
              <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
              <td class="p-3"><?= ucfirst($u['role']) ?></td>
              <td class="p-3"><?= htmlspecialchars($u['dept_name']) ?></td>
              <td class="p-3 text-xs"><?= $u['created_at'] ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include("../../includes/footer.php"); ?>
