<?php
use Dompdf\Dompdf;
use Dompdf\Options;

require __DIR__ . '/../../vendor/autoload.php';

function generateAttendancePDF($studentName, $courseData) {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    // Build HTML table
    $html = "<h2>Attendance Report - $studentName</h2>
             <table border='1' cellspacing='0' cellpadding='6' width='100%'>
             <tr><th>Course</th><th>Total Classes</th><th>Present</th><th>Absent</th><th>% Attendance</th></tr>";

    foreach ($courseData as $course) {
        $color = ($course['percentage'] < 75) ? " style='color:red;'" : "";
        $html .= "<tr>
                    <td>{$course['course_name']}</td>
                    <td>{$course['total_classes']}</td>
                    <td>{$course['present']}</td>
                    <td>{$course['absent']}</td>
                    <td{$color}>{$course['percentage']}%</td>
                  </tr>";
    }

    $html .= "</table>";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output(); // return PDF binary
}
