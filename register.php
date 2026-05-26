<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voter_id = trim($_POST['voter_id']);
    $name = trim($_POST['name']);
    
    if (!empty($voter_id) && !empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO voters (voter_id, name) VALUES (?, ?)");
            $stmt->execute([$voter_id, $name]);
            setFlash("Account created! You can now vote.");
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            setFlash("Error: Voter ID already exists.", "error");
        }
    }
}
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Voter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 md:p-10 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-6 md:p-8 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Register to Vote</h2>
        <?php if ($flash) echo "<p class='mb-4 text-red-500 bg-red-50 p-3 rounded'>{$flash['text']}</p>"; ?>
        <form method="POST">
            <label class="block text-sm font-semibold text-gray-600 mb-1">Full Name</label>
            <input type="text" name="name" placeholder="Juan Dela Cruz" required class="w-full border border-gray-300 p-3 mb-4 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            
            <label class="block text-sm font-semibold text-gray-600 mb-1">Create Voter ID</label>
            <input type="text" name="voter_id" placeholder="e.g. ID-12345" required class="w-full border border-gray-300 p-3 mb-6 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold p-3 rounded-lg transition">Register</button>
        </form>
        <a href="index.php" class="block mt-6 text-blue-500 hover:text-blue-700 text-center font-medium">Back to Voting</a>
    </div>
</body>
</html>