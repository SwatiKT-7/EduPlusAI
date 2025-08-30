<?php
session_start();
if (!in_array($_SESSION['role_id'], [1, 2])) { // Admin or Faculty only
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/db.php";

// For demo: fetch department-wide summary
$attendanceData = $conn->query("
    SELECT s.name as subject, COUNT(a.id) as total,
           SUM(a.status='Present') as present
    FROM subjects s
    JOIN sessions ss ON s.id = ss.subject_id
    JOIN attendance a ON a.session_id = ss.id
    GROUP BY s.id
");

// Low attendance students (<75%)
$lowAttendance = $conn->query("
    SELECT u.name, s.name as subject, 
           COUNT(a.id) as total, SUM(a.status='Present') as present
    FROM users u
    JOIN attendance a ON u.id = a.student_id
    JOIN sessions ss ON a.session_id = ss.id
    JOIN subjects s ON ss.subject_id = s.id
    WHERE u.role_id = 3
    GROUP BY u.id, s.id
    HAVING (SUM(a.status='Present') / COUNT(a.id)) * 100 < 75
    ORDER BY u.name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics Dashboard - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 text-gray-900 flex">

  <!-- Sidebar -->
  <?php include("../assets/components/sidebar.php"); ?>

  <!-- Main Content -->
  <div class="flex-1">
    <?php include("../assets/components/header.php"); ?>

    <main class="p-6">
      <h1 class="text-2xl font-bold mb-6">📊 Analytics Dashboard</h1>

      <!-- Charts -->
      <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-lg font-semibold mb-4">📈 Subject Attendance Overview</h2>
        <canvas id="subjectAttendance" style="height:300px;"></canvas>
      </div>

      <!-- Low Attendance Students -->
      <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-lg font-semibold mb-4">⚠️ Students Below 75%</h2>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-200">
                <th class="p-3 text-left">Student</th>
                <th class="p-3 text-left">Subject</th>
                <th class="p-3 text-left">Attendance %</th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($row = $lowAttendance->fetch_assoc()) {
                  $percent = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100, 2) : 0;
                  echo "<tr class='border-b hover:bg-gray-50'>
                          <td class='p-3'>{$row['name']}</td>
                          <td class='p-3'>{$row['subject']}</td>
                          <td class='p-3 font-bold text-red-600'>{$percent}%</td>
                        </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- AI Insights Section -->
      <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-semibold mb-4">🤖 AI Insights</h2>
        <div id="insightsBox" class="p-4 bg-gray-100 rounded-lg text-gray-700">
          Loading insights...
        </div>
      </div>
    </main>
  </div>
<a href="../notifications/send_low_attendance.php" 
   class="mt-4 inline-block bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
   📧 Send Low Attendance Alerts
</a>

  <!-- Chart.js Script -->
  <script>
    const ctx = document.getElementById('subjectAttendance');
    const subjectAttendance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: [
          <?php
          $attendanceData->data_seek(0);
          while ($row = $attendanceData->fetch_assoc()) {
              echo "'" . $row['subject'] . "',";
          }
          ?>
        ],
        datasets: [{
          label: 'Attendance %',
          data: [
            <?php
            $attendanceData->data_seek(0);
            $attendanceData->data_seek(0);
            $attendanceData = $conn->query("
                SELECT s.name as subject, COUNT(a.id) as total,
                       SUM(a.status='Present') as present
                FROM subjects s
                JOIN sessions ss ON s.id = ss.subject_id
                JOIN attendance a ON a.session_id = ss.id
                GROUP BY s.id
            ");
            while ($row = $attendanceData->fetch_assoc()) {
                $percent = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100, 2) : 0;
                echo $percent . ",";
            }
            ?>
          ],
          backgroundColor: 'rgba(16, 185, 129, 0.7)', // teal
          borderColor: 'rgba(5, 150, 105, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, max: 100 }
        }
      }
    });

    // Fetch AI Insights
    fetch("insights.php")
      .then(res => res.json())
      .then(data => {
        document.getElementById("insightsBox").innerText = data.insight;
      });
  </script>
</body>
</html>
