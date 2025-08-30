<?php
session_start();
if ($_SESSION['role_id'] != 2) { // Ensure only faculty
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: false, openQR: false }">
<head>
  <meta charset="UTF-8">
  <title>Faculty Dashboard - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs"></script>
</head>
<body :class="darkMode ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900'" class="flex">

  <!-- Sidebar -->
  <?php include("../assets/components/sidebar.php"); ?>

  <!-- Main Content -->
  <div class="flex-1">
    <?php include("../assets/components/header.php"); ?>

    <main class="p-6">

      <!-- Welcome -->
      <h1 class="text-2xl font-bold mb-6">👨‍🏫 Faculty Dashboard</h1>

      <!-- Quick Stats -->
      <div class="grid grid-cols-3 gap-6 mb-8">
        <div class="p-6 bg-white rounded-xl shadow hover:shadow-lg transition">
          <h2 class="text-lg font-semibold">Total Classes</h2>
          <p class="text-3xl mt-2">8</p>
        </div>

        <div class="p-6 bg-white rounded-xl shadow hover:shadow-lg transition">
          <h2 class="text-lg font-semibold">Average Attendance</h2>
          <p class="text-3xl mt-2">87%</p>
        </div>

        <div class="p-6 bg-white rounded-xl shadow hover:shadow-lg transition flex flex-col justify-between">
          <h2 class="text-lg font-semibold">Today’s Session</h2>
          <button @click="openQR = true" 
            class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            ➕ Generate QR Code
          </button>
        </div>
      </div>

      <!-- Recent Sessions -->
      <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">📅 Recent Sessions</h2>
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-200">
              <th class="p-3 text-left">#</th>
              <th class="p-3 text-left">Subject</th>
              <th class="p-3 text-left">Date</th>
              <th class="p-3 text-left">Attendance %</th>
              <th class="p-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b hover:bg-gray-100">
              <td class="p-3">1</td>
              <td class="p-3">Data Structures</td>
              <td class="p-3">2025-08-29</td>
              <td class="p-3">92%</td>
              <td class="p-3">
                <button class="bg-green-500 text-white px-3 py-1 rounded">View</button>
              </td>
            </tr>
            <tr class="border-b hover:bg-gray-100">
              <td class="p-3">2</td>
              <td class="p-3">Operating Systems</td>
              <td class="p-3">2025-08-28</td>
              <td class="p-3">85%</td>
              <td class="p-3">
                <button class="bg-green-500 text-white px-3 py-1 rounded">View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- QR Code Modal -->
  <div x-show="openQR" 
       class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
       x-transition>
    <div class="bg-white rounded-xl shadow-lg p-6 w-96 relative">
      <h2 class="text-xl font-bold mb-4">📲 Generate Attendance QR</h2>
      
<form id="qrForm" class="space-y-4">
  <label class="block text-sm">Select Subject</label>
  <select name="subject_id" class="w-full p-2 border rounded">
    <option value="1">Data Structures</option>
    <option value="2">Operating Systems</option>
  </select>

  <label class="block text-sm">Session Date & Time</label>
  <input type="datetime-local" name="session_date" class="w-full p-2 border rounded">

  <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['user_id']; ?>">

  <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg">
    ✅ Generate QR
  </button>
</form>
<div id="qrPreview" class="mt-4 text-center hidden">
  <p class="mb-2">📲 Share this QR with students:</p>
  <img id="qrImage" src="" alt="QR Code" class="mx-auto">
</div>

      <button @click="openQR = false" 
        class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">✖</button>
    </div>
  </div>

</body>
</html>

<script>
document.getElementById("qrForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  let formData = new FormData(e.target);

  let res = await fetch("../attendance/generate_qr.php", {
    method: "POST",
    body: formData
  });
  let data = await res.json();

  if (data.success) {
    document.getElementById("qrImage").src = data.qr_url;
    document.getElementById("qrPreview").classList.remove("hidden");
  }
});
</script>