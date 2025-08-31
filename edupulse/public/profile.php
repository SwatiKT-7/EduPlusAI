<?php
session_start();
require_once "../config/db.php";
include("../includes/header.php");
include("../includes/auth.php");
check_login();

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id=$user_id LIMIT 1";
$res = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($res);
?>

<div class="max-w-lg mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-xl font-bold mb-4">My Profile</h2>
  <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
  <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
  <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
  <p><strong>Department ID:</strong> <?php echo $user['dept_id']; ?></p>
  <p><strong>Enrolled No:</strong> <?php echo $user['enrollment_no']; ?></p>
</div>

<?php include("../includes/footer.php"); ?>
