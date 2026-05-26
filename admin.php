<?php
require_once 'db.php';
require_once 'dsa_engine.php';

if (empty($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_candidate') {
        $name = trim($_POST['name']);
        $position = empty(trim($_POST['position'])) ? 'N/A' : trim($_POST['position']);
        $party = empty(trim($_POST['party'])) ? 'N/A' : trim($_POST['party']);
        
        $stmt = $pdo->prepare("INSERT INTO candidates (name, position, party) VALUES (?, ?, ?)");
        $stmt->execute([$name, $position, $party]);
        setFlash("Candidate added.");
    }
    
    if ($_POST['action'] === 'reset') {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE votes; TRUNCATE TABLE candidates; TRUNCATE TABLE voters; SET FOREIGN_KEY_CHECKS = 1;");
        setFlash("System wiped out.");
    }
    header("Location: admin.php");
    exit;
}

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header("Location: index.php");
    exit;
}

$stmt = $pdo->query("SELECT c.*, COUNT(v.id) as votes FROM candidates c LEFT JOIN votes v ON c.id = v.candidate_id GROUP BY c.id");
$candidatesArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

$bst = new ResultBST();
$partyVotes = [];

foreach ($candidatesArray as $candidate) {
    $bst->insert($candidate);
    
    $p = $candidate['party'];
    if ($p !== 'N/A') {
        if (!isset($partyVotes[$p])) {
            $partyVotes[$p] = 0;
        }
        $partyVotes[$p] += $candidate['votes'];
    }
}

arsort($partyVotes);

$ranked = [];
$bst->getRankedResults($ranked);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-4 md:p-8">
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-6 md:gap-8">
        
        <div class="w-full lg:w-1/3 order-2 lg:order-1">
            <div class="bg-white p-6 rounded-xl shadow-md mb-6 border border-gray-100">
                <h2 class="font-bold text-xl text-gray-800 mb-4">Add Candidate</h2>
                <?php if ($flash && $flash['type'] === 'success') echo "<p class='text-green-700 bg-green-50 p-3 rounded mb-4'>{$flash['text']}</p>"; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="add_candidate">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Name (Required)</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 p-2.5 mb-4 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Position (Optional)</label>
                    <input type="text" name="position" class="w-full border border-gray-300 p-2.5 mb-4 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Party (Optional)</label>
                    <input type="text" name="party" class="w-full border border-gray-300 p-2.5 mb-6 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition">Register Candidate</button>
                </form>
            </div>
            
            <div class="bg-red-50 p-6 rounded-xl border border-red-200">
                <h3 class="text-red-800 font-bold mb-2">Danger Zone</h3>
                <form method="POST" onsubmit="return confirm('WARNING: This will delete ALL voters, candidates, and votes. Are you sure?');">
                    <input type="hidden" name="action" value="reset">
                    <button class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-bold transition shadow-sm">Reset Entire System</button>
                </form>
            </div>
            
            <a href="?logout=1" class="block w-full text-center mt-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">Logout Admin</a>
        </div>

        <div class="w-full lg:w-2/3 order-1 lg:order-2 flex flex-col gap-6">
            
            <?php if (!empty($partyVotes)): ?>
            <div class="bg-indigo-900 rounded-xl shadow-md border border-indigo-800 overflow-hidden text-white">
                <div class="bg-indigo-950 p-4 border-b border-indigo-800">
                    <h2 class="font-bold text-lg">Overall Party Standings</h2>
                </div>
                <div class="p-4 flex gap-4 overflow-x-auto">
                    <?php foreach ($partyVotes as $partyName => $pVotes): ?>
                    <div class="bg-indigo-800 px-6 py-3 rounded-lg text-center min-w-[120px]">
                        <div class="text-xs text-indigo-300 uppercase font-bold tracking-wider mb-1"><?php echo htmlspecialchars($partyName); ?></div>
                        <div class="text-2xl font-black"><?php echo $pVotes; ?> <span class="text-sm font-normal text-indigo-200">votes</span></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 p-6 border-b border-gray-200">
                    <h2 class="font-bold text-xl text-gray-800">Candidate Results</h2>
                    <p class="text-sm text-gray-500 mt-1">Sorted dynamically by Binary Search Tree (BST)</p>
                </div>
                
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-4 font-semibold">Rank</th>
                                <th class="p-4 font-semibold">Name</th>
                                <th class="p-4 font-semibold">Position</th>
                                <th class="p-4 font-semibold">Party</th>
                                <th class="p-4 font-semibold text-right">Votes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(empty($ranked)): ?>
                                <tr><td colspan="5" class="p-8 text-center text-gray-500">No candidates registered yet.</td></tr>
                            <?php else: ?>
                                <?php $rank = 1; foreach ($ranked as $c): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold <?php echo $rank === 1 && $c['votes'] > 0 ? 'text-yellow-600' : 'text-gray-500'; ?>">#<?php echo $rank++; ?></td>
                                    <td class="p-4 font-bold text-gray-900"><?php echo htmlspecialchars($c['name']); ?></td>
                                    <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($c['position']); ?></td>
                                    <td class="p-4 text-sm text-gray-600">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars($c['party']); ?></span>
                                    </td>
                                    <td class="p-4 font-black text-indigo-600 text-right text-lg"><?php echo $c['votes']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</body>
</html>