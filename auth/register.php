<?php
session_start();
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role']; // 1=Admin, 2=Faculty, 3=Student
    $dept_id = $_POST['dept_id'] ?? NULL;

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role_id, dept_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $name, $email, $password, $role, $dept_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: login.php");
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - EduPlusAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="w-full max-w-md bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4 text-center">Register</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required class="w-full p-2 mb-3 border rounded">
            <input type="email" name="email" placeholder="Email" required class="w-full p-2 mb-3 border rounded">
            <input type="password" name="password" placeholder="Password" required class="w-full p-2 mb-3 border rounded">

            <select name="role" class="w-full p-2 mb-3 border rounded" required>
                <option value="">Select Role</option>
                <option value="1">Admin</option>
                <option value="2">Faculty</option>
                <option value="3">Student</option>
            </select>

            <input type="number" name="dept_id" placeholder="Department ID (for Faculty/Student)" class="w-full p-2 mb-3 border rounded">

            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Register</button>
        </form>
        <p class="mt-3 text-center">Already have an account? <a href="login.php" class="text-blue-600">Login</a></p>
    </div>
</body>
</html>
