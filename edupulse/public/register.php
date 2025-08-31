<?php
session_start();
require_once "../config/db.php";

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    $role     = $_POST['role'];
    $dept_id  = intval($_POST['dept_id']);

    $sql = "INSERT INTO users (tenant_id, role, name, email, dept_id, password_hash)
            VALUES (1, '$role', '$name', '$email', $dept_id, '$password')";
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Registered successfully! Please <a href='login.php' class='text-blue-600 underline'>login</a>.";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}

$departments = mysqli_query($conn, "SELECT id, name FROM departments");
include("../includes/header.php");
?>

<div class="max-w-md mx-auto bg-white shadow-lg rounded-lg p-6 mt-10">
  <h2 class="text-2xl font-bold mb-4 text-center">Register</h2>
  <?php if($message): ?>
    <p class="mb-3 text-center <?php echo strpos($message,'✅')!==false?'text-green-600':'text-red-600'; ?>">
      <?php echo $message; ?>
    </p>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-4">
      <label class="block text-gray-700">Name</label>
      <input type="text" name="name" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700">Email</label>
      <input type="email" name="email" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700">Password</label>
      <input type="password" name="password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700">Role</label>
      <select name="role" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
      </select>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700">Department</label>
      <select name="dept_id" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
        <?php while($d = mysqli_fetch_assoc($departments)): ?>
          <option value="<?php echo $d['id']; ?>"><?php echo $d['name']; ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 transition">
      Register
    </button>
  </form>
  <p class="mt-4 text-center text-sm">Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a></p>
</div>

<?php include("../includes/footer.php"); ?>
