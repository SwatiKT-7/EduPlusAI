<?php
session_start();
if ($_SESSION['role_id'] != 1) { // Only Admin
    header("Location: ../auth/login.php");
    exit();
}
require_once "../config/db.php";

// Fetch Departments
$departments = $conn->query("SELECT * FROM departments ORDER BY name");

// Handle Add
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $roll_no = $_POST['roll_no'];
    $parent_email = $_POST['parent_email'];
    $dept_id = $_POST['dept_id'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role_id = 3; // Student

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role_id,dept_id,roll_no,parent_email) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssisss", $name,$email,$password,$role_id,$dept_id,$roll_no,$parent_email);
    $stmt->execute();
    header("Location: admin_students.php");
    exit();
}

// Handle Edit
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $roll_no = $_POST['roll_no'];
    $parent_email = $_POST['parent_email'];
    $dept_id = $_POST['dept_id'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET name=?,email=?,password=?,dept_id=?,roll_no=?,parent_email=? WHERE id=? AND role_id=3");
        $stmt->bind_param("sssissi", $name,$email,$password,$dept_id,$roll_no,$parent_email,$id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?,email=?,dept_id=?,roll_no=?,parent_email=? WHERE id=? AND role_id=3");
        $stmt->bind_param("ssisss", $name,$email,$dept_id,$roll_no,$parent_email,$id);
    }
    $stmt->execute();
    header("Location: admin_students.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id AND role_id=3");
    header("Location: admin_students.php");
    exit();
}

// Fetch students
$students = $conn->query("SELECT u.*, d.name as dept FROM users u 
                          LEFT JOIN departments d ON u.dept_id=d.id 
                          WHERE role_id=3 ORDER BY u.id DESC");
?>
<!DOCTYPE html>
<html lang="en" x-data="{ openAdd:false, openEdit:false, editId:'', editName:'', editEmail:'', editRoll:'', editParent:'', editDept:'' }">
<head>
  <meta charset="UTF-8">
  <title>Manage Students - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs"></script>
</head>
<body class="bg-gray-100 text-gray-900 flex">

  <!-- Sidebar -->
  <?php include("../assets/components/sidebar.php"); ?>

  <!-- Main -->
  <div class="flex-1">
    <?php include("../assets/components/header.php"); ?>
    <main class="p-6">
      <h1 class="text-2xl font-bold mb-6">🎓 Manage Students</h1>

      <!-- Add Button -->
      <button @click="openAdd=true" 
        class="mb-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">➕ Add Student</button>

      <!-- Table -->
      <div class="bg-white p-6 rounded-xl shadow overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-200">
              <th class="p-3 text-left">ID</th>
              <th class="p-3 text-left">Roll No</th>
              <th class="p-3 text-left">Name</th>
              <th class="p-3 text-left">Email</th>
              <th class="p-3 text-left">Parent Email</th>
              <th class="p-3 text-left">Department</th>
              <th class="p-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $students->fetch_assoc()): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3"><?= $row['id'] ?></td>
              <td class="p-3"><?= $row['roll_no'] ?></td>
              <td class="p-3"><?= $row['name'] ?></td>
              <td class="p-3"><?= $row['email'] ?></td>
              <td class="p-3"><?= $row['parent_email'] ?></td>
              <td class="p-3"><?= $row['dept'] ?></td>
              <td class="p-3 space-x-2">
                <button 
                  @click="openEdit=true; editId='<?= $row['id'] ?>'; editName='<?= $row['name'] ?>'; editEmail='<?= $row['email'] ?>'; editRoll='<?= $row['roll_no'] ?>'; editParent='<?= $row['parent_email'] ?>'; editDept='<?= $row['dept_id'] ?>'" 
                  class="bg-yellow-500 text-white px-3 py-1 rounded">✏️ Edit</button>
                <a href="?delete=<?= $row['id'] ?>" 
                  class="bg-red-600 text-white px-3 py-1 rounded"
                  onclick="return confirm('Delete this student?')">🗑️ Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- Add Modal -->
  <div x-show="openAdd" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" x-transition>
    <div class="bg-white p-6 rounded-lg shadow w-96 relative">
      <h2 class="text-lg font-bold mb-4">➕ Add Student</h2>
      <form method="POST" class="space-y-4">
        <input type="text" name="roll_no" placeholder="Roll No" required class="w-full border p-2 rounded">
        <input type="text" name="name" placeholder="Full Name" required class="w-full border p-2 rounded">
        <input type="email" name="email" placeholder="Email" required class="w-full border p-2 rounded">
        <input type="email" name="parent_email" placeholder="Parent Email" required class="w-full border p-2 rounded">
        <select name="dept_id" required class="w-full border p-2 rounded">
          <option value="">Select Department</option>
          <?php 
          $departments->data_seek(0);
          while($d=$departments->fetch_assoc()) echo "<option value='{$d['id']}'>{$d['name']}</option>"; 
          ?>
        </select>
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2 rounded">
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <button type="button" @click="openAdd=false" class="ml-2">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div x-show="openEdit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" x-transition>
    <div class="bg-white p-6 rounded-lg shadow w-96 relative">
      <h2 class="text-lg font-bold mb-4">✏️ Edit Student</h2>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="id" :value="editId">
        <input type="text" name="roll_no" :value="editRoll" required class="w-full border p-2 rounded">
        <input type="text" name="name" :value="editName" required class="w-full border p-2 rounded">
        <input type="email" name="email" :value="editEmail" required class="w-full border p-2 rounded">
        <input type="email" name="parent_email" :value="editParent" required class="w-full border p-2 rounded">
        <select name="dept_id" required class="w-full border p-2 rounded">
          <option value="">Select Department</option>
          <?php 
          $departments->data_seek(0);
          while($d=$departments->fetch_assoc()) echo "<option value='{$d['id']}'>{$d['name']}</option>"; 
          ?>
        </select>
        <input type="password" name="password" placeholder="(Leave blank to keep old)" class="w-full border p-2 rounded">
        <button type="submit" name="edit" class="bg-yellow-600 text-white px-4 py-2 rounded">Update</button>
        <button type="button" @click="openEdit=false" class="ml-2">Cancel</button>
      </form>
    </div>
  </div>
</body>
</html>
