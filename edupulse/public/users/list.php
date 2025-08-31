<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");
include("../../includes/auth.php");
check_role('admin');

$res = mysqli_query($conn, "SELECT id, name, email, role, dept_id FROM users ORDER BY role, name");
?>

<div class="max-w-5xl mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-xl font-bold mb-4">All Users</h2>
  <table class="w-full border-collapse border">
    <tr class="bg-gray-200">
      <th class="p-2 border">ID</th>
      <th class="p-2 border">Name</th>
      <th class="p-2 border">Email</th>
      <th class="p-2 border">Role</th>
      <th class="p-2 border">Dept</th>
    </tr>
    <?php while($u = mysqli_fetch_assoc($res)): ?>
    <tr>
      <td class="p-2 border"><?php echo $u['id']; ?></td>
      <td class="p-2 border"><?php echo $u['name']; ?></td>
      <td class="p-2 border"><?php echo $u['email']; ?></td>
      <td class="p-2 border"><?php echo ucfirst($u['role']); ?></td>
      <td class="p-2 border"><?php echo $u['dept_id']; ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<?php include("../../includes/footer.php"); ?>
