<?php
session_start();
if ($_SESSION['role_id'] != 1) { // Only Admin
    header("Location: ../auth/login.php");
    exit();
}
require_once "../config/db.php";

// Handle Add
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    header("Location: admin_departments.php");
    exit();
}

// Handle Edit
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE departments SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    header("Location: admin_departments.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM departments WHERE id=$id");
    header("Location: admin_departments.php");
    exit();
}

// Fetch all
$departments = $conn->query("SELECT * FROM departments ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en" x-data="{ openAdd:false, openEdit:false, editId:'', editName:'' }">
<head>
  <meta charset="UTF-8">
  <title>Manage Departments - EduPlusAI</title>
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
      <h1 class="text-2xl font-bold mb-6">🏫 Manage Departments</h1>

      <!-- Add Button -->
      <button @click="openAdd=true" 
        class="mb-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">➕ Add Department</button>

      <!-- Table -->
      <div class="bg-white p-6 rounded-xl shadow overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-200">
              <th class="p-3 text-left">ID</th>
              <th class="p-3 text-left">Department Name</th>
              <th class="p-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $departments->fetch_assoc()): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3"><?= $row['id'] ?></td>
              <td class="p-3"><?= $row['name'] ?></td>
              <td class="p-3 space-x-2">
                <button @click="openEdit=true; editId='<?= $row['id'] ?>'; editName='<?= $row['name'] ?>'" 
                  class="bg-yellow-500 text-white px-3 py-1 rounded">✏️ Edit</button>
                <a href="?delete=<?= $row['id'] ?>" 
                  class="bg-red-600 text-white px-3 py-1 rounded"
                  onclick="return confirm('Delete this department?')">🗑️ Delete</a>
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
      <h2 class="text-lg font-bold mb-4">➕ Add Department</h2>
      <form method="POST">
        <input type="text" name="name" placeholder="Department Name" required 
          class="w-full border p-2 rounded mb-4">
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <button type="button" @click="openAdd=false" class="ml-2">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div x-show="openEdit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" x-transition>
    <div class="bg-white p-6 rounded-lg shadow w-96 relative">
      <h2 class="text-lg font-bold mb-4">✏️ Edit Department</h2>
      <form method="POST">
        <input type="hidden" name="id" :value="editId">
        <input type="text" name="name" :value="editName" required 
          class="w-full border p-2 rounded mb-4">
        <button type="submit" name="edit" class="bg-yellow-600 text-white px-4 py-2 rounded">Update</button>
        <button type="button" @click="openEdit=false" class="ml-2">Cancel</button>
      </form>
    </div>
  </div>
</body>
</html>
