<div x-data="{ open: false }" class="relative">
  <!-- Mobile Header with Hamburger -->
  <div class="md:hidden flex items-center justify-between bg-gray-800 text-white p-4 shadow">
    <h2 class="text-lg font-bold">Admin Panel</h2>
    <button @click="open = !open" class="focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" 
           viewBox="0 0 24 24" stroke="currentColor">
        <path :class="{'hidden':open,'block':!open}" class="block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M4 6h16M4 12h16M4 18h16" />
        <path :class="{'block':open,'hidden':!open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <!-- Sidebar -->
  <aside :class="open ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
         class="fixed md:static inset-y-0 left-0 w-64 bg-gray-800 text-white p-6 transform md:transform-none transition-transform duration-300 z-50">
    <h2 class="text-xl font-bold mb-6">Admin Panel</h2>
    <ul class="space-y-3">
      <li><a href="dashboard.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">🏠 Dashboard</a></li>
      <li><a href="departments.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">🏫 Departments</a></li>
      <li><a href="courses.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">📘 Courses</a></li>
      <li><a href="users.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">👥 Users</a></li>
      <li><a href="classes.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">📅 Classes</a></li>
      <li><a href="attendance.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">📝 Attendance</a></li>
      <li><a href="alerts.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">🚨 Alerts</a></li>
      <li><a href="reports.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">📊 Reports</a></li>
      <li><a href="settings.php" class="block px-3 py-2 rounded hover:bg-gray-700 hover:text-blue-300">⚙️ Settings</a></li>
    </ul>
  </aside>

  <!-- Overlay on Mobile -->
  <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 md:hidden" @click="open=false"></div>
</div>
