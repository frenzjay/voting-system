<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voter_id = trim($_POST['voter_id']);
    $votes = $_POST['votes'] ?? []; 
    
    $stmt = $pdo->prepare("SELECT * FROM voters WHERE voter_id = ?");
    $stmt->execute([$voter_id]);
    $voter = $stmt->fetch();

    if ($voter) {
        $checkVotes = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE voter_id = ?");
        $checkVotes->execute([$voter_id]);
        $hasVoted = $checkVotes->fetchColumn();

        if ($hasVoted > 0) {
            setFlash("Double voting is not allowed.", "error");
        } elseif (empty($votes)) {
            setFlash("You must select at least one candidate before submitting.", "error");
        } else {
            try {
                $pdo->beginTransaction();
                $insertVote = $pdo->prepare("INSERT INTO votes (voter_id, candidate_id) VALUES (?, ?)");
                foreach ($votes as $position_key => $candidate_id) {
                    $insertVote->execute([$voter_id, $candidate_id]);
                }
                $pdo->commit();
                setFlash("Official ballot submitted successfully!");
            } catch (PDOException $e) {
                $pdo->rollBack();
                setFlash("An error occurred while casting your vote.", "error");
            }
        }
    } else {
        setFlash("Invalid Voter ID. Please register first.", "error");
    }
    header("Location: index.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM candidates ORDER BY position, name");
$allCandidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

$candidatesByPosition = [];
$parties = [];
foreach ($allCandidates as $c) {
    $candidatesByPosition[$c['position']][] = $c;
    
    if ($c['party'] !== 'N/A' && !in_array($c['party'], $parties)) {
        $parties[] = $c['party'];
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System!</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 md:p-10">
    <div class="w-full max-w-3xl mx-auto bg-white p-6 md:p-8 rounded-xl shadow-lg">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Official Election Ballot</h1>
                <p class="text-gray-500 text-sm mt-1">Select one candidate per position</p>
            </div>
            <div class="flex gap-3 w-full sm:w-auto">
                <a href="register.php" class="flex-1 sm:flex-none text-center bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-200">Register ID</a>
                <a href="admin_login.php" class="flex-1 sm:flex-none text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200">Admin</a>
            </div>
        </div>
        
        <?php if ($flash) echo "<div class='mb-6 p-4 rounded-lg " . ($flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') . " font-medium'>{$flash['text']}</div>"; ?>
        
        <form method="POST">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <label class="block mb-2 font-bold text-gray-700">Verify Your Identity</label>
                <input type="text" name="voter_id" placeholder="Enter your registered Voter ID" required class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <?php if (!empty($parties)): ?>
            <div class="mb-8 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                <label class="block mb-3 font-bold text-indigo-900">Quick Vote</label>
                <div class="flex gap-2 flex-wrap">
                    <?php foreach ($parties as $party): ?>
                        <button type="button" onclick="voteParty('<?php echo htmlspecialchars($party); ?>')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow text-sm font-bold transition">
                            Vote all <?php echo htmlspecialchars($party); ?>
                        </button>
                    <?php endforeach; ?>
                    <button type="button" onclick="clearVotes()" class="bg-white hover:bg-gray-100 text-gray-800 border border-gray-300 px-4 py-2 rounded shadow text-sm font-bold transition">Clear Choices</button>
                </div>
            </div>

            <script>
                function voteParty(partyName) {
                    let radios = document.querySelectorAll('input[type="radio"]');
                    radios.forEach(radio => {
                        if (radio.dataset.party === partyName) {
                            radio.checked = true;
                        }
                    });
                }
                function clearVotes() {
                    let radios = document.querySelectorAll('input[type="radio"]');
                    radios.forEach(radio => radio.checked = false);
                }
            </script>
            <?php endif; ?>
            
            <?php if (empty($candidatesByPosition)): ?>
                <div class="text-center py-10 text-gray-500">
                    <p>No candidates have been registered for this election yet.</p>
                </div>
            <?php else: ?>
                <?php 
                $groupIndex = 0; 
                foreach ($candidatesByPosition as $position => $candidates): 
                    $groupIndex++;
                ?>
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 border-b-2 border-gray-200 pb-2 mb-4 uppercase tracking-wider">
                            For <?php echo htmlspecialchars($position); ?>
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($candidates as $c): ?>
                                <label class="border-2 border-gray-200 p-4 rounded-xl cursor-pointer hover:border-emerald-500 hover:bg-emerald-50 flex items-start transition bg-white">
                                    <input type="radio" name="votes[<?php echo $groupIndex; ?>]" value="<?php echo $c['id']; ?>" data-party="<?php echo htmlspecialchars($c['party']); ?>" class="mt-1 mr-4 w-5 h-5 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($c['name']); ?></div>
                                        <div class="text-sm text-gray-500 font-medium mt-1">
                                            Party: <?php echo htmlspecialchars($c['party']); ?>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-10 border-t pt-6">
                    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white p-4 rounded-xl font-bold text-xl transition shadow-lg">
                        Cast Official Ballot
                    </button>
                    <p class="text-center text-xs text-gray-400 mt-3">Warning: Once submitted, ballots cannot be changed.</p>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>