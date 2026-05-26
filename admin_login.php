<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    if ($password === 'frnz') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        setFlash("-1", "error");
    }
}
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-sm bg-white p-8 rounded-xl shadow-lg border border-gray-100">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold text-gray-800">Admin Login</h2>
            <p class="text-gray-500 text-sm mt-2">Ballot manage</p>
        </div>
        
        <?php if ($flash) echo "<div class='text-red-700 bg-red-50 p-3 rounded-lg mb-6 text-sm font-medium'>{$flash['text']}</div>"; ?>
        
        <form method="POST">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input type="password" name="password" placeholder="••••••••" required class="w-full border border-gray-300 p-3 mb-6 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
            
            <button class="w-full bg-gray-900 hover:bg-black text-white font-bold p-3 rounded-lg transition shadow-md mb-4">Login</button>
        </form>
        
        <a href="index.php" class="block text-center text-sm text-blue-600 hover:underline">Return to Voting</a>
    </div>
</body>
</html>