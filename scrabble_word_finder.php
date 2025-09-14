<?php
// scrabble_word_finder.php
// Scrabble word finder backend with database wordlist pre-loading,
// grouped, sorted, and paginated results.

header("Content-Type: application/json");

// ---- DB INIT ----
$dbname = "scrabbleAnagrams.sqlite";
$WORDLIST_FILE = __DIR__ . "/CSW24_modified.txt";

try {
    $conn = new PDO("sqlite:" . $dbname);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => "DB connection failed: " . $e->getMessage()]));
}

$conn->exec("
    CREATE TABLE IF NOT EXISTS words (
        id INTEGER PRIMARY KEY,
        word VARCHAR(32) UNIQUE,
        length TINYINT,
        score SMALLINT
    )
");

// ---- WORDLIST PRE-LOADING ----
function scrabbleScore($word) {
    $scores = [
        'a' => 1, 'b' => 3, 'c' => 3, 'd' => 2, 'e' => 1, 'f' => 4, 'g' => 2, 'h' => 4, 'i' => 1, 'j' => 8,
        'k' => 5, 'l' => 1, 'm' => 3, 'n' => 1, 'o' => 1, 'p' => 3, 'q' => 10, 'r' => 1, 's' => 1, 't' => 1,
        'u' => 1, 'v' => 4, 'w' => 4, 'x' => 8, 'y' => 4, 'z' => 10,
    ];
    $score = 0;
    foreach (str_split($word) as $letter) {
        $score += $scores[$letter] ?? 0;
    }
    return $score;
}

$res = $conn->query("SELECT COUNT(*) AS c FROM words");
$row = $res->fetch(PDO::FETCH_ASSOC);

if ($row['c'] == 0) {
    if (!is_readable($WORDLIST_FILE)) {
        die(json_encode(["error" => "Wordlist not found: $WORDLIST_FILE"]));
    }
    $fh = fopen($WORDLIST_FILE, "r");
    $conn->beginTransaction();
    $stmt = $conn->prepare("INSERT OR IGNORE INTO words (word,length,score) VALUES (?,?,?)");

    while (($line = fgets($fh)) !== false) {
        $w = strtolower(trim($line));
        if ($w === "" || preg_match('/[^a-z]/', $w)) continue;

        $len = strlen($w);
        $score = scrabbleScore($w);

        $stmt->execute([$w, $len, $score]);
    }
    $conn->commit();
    fclose($fh);
}

// ---- CONCURRENCY LIMIT (3 users max) ----
$lockFile = sys_get_temp_dir() . "/scrabble_finder.lock";
$maxSlots = 3;

$lockHandle = fopen($lockFile, "c+");
if (!$lockHandle) {
    echo json_encode(["error" => "Unable to open lock file."]);
    exit;
}

flock($lockHandle, LOCK_EX);
$slots = intval(stream_get_contents($lockHandle));
if ($slots >= $maxSlots) {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    echo json_encode(["error" => "Server busy, please wait and try again."]);
    exit;
} else {
    ftruncate($lockHandle, 0);
    rewind($lockHandle);
    fwrite($lockHandle, $slots + 1);
    fflush($lockHandle);
    flock($lockHandle, LOCK_UN);
}

// ---- REQUEST HANDLING ----
$rack   = isset($_GET["rack"]) ? strtolower(preg_replace("/[^a-z?]/", "", $_GET["rack"])) : "";
$perPage = 50; // Items per page, but handled by JS now
if (!$rack) {
    releaseSlot($lockFile, $lockHandle);
    echo json_encode(["results" => []]);
    exit;
}

// ---- FETCH CANDIDATE WORDS ----
$sql = "SELECT word, length, score
        FROM words
        WHERE length <= " . strlen($rack) . "
        ORDER BY length DESC, word ASC";

$result = $conn->query($sql);

$groupedCandidates = [];
if ($result) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if (canFormWord($row["word"], $rack)) {
            $len = $row['length'];
            if (!isset($groupedCandidates[$len])) {
                $groupedCandidates[$len] = [];
            }
            $groupedCandidates[$len][] = $row;
        }
    }
}

// ---- RELEASE SLOT ----
releaseSlot($lockFile, $lockHandle);

// ---- OUTPUT ----
echo json_encode([
    "results"  => $groupedCandidates
]);

// ---- HELPER: Release concurrency slot ----
function releaseSlot($file, $handle) {
    flock($handle, LOCK_EX);
    fseek($handle, 0);
    $slots = intval(stream_get_contents($handle));
    $slots = max(0, $slots - 1);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, $slots);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

// ---- HELPER: Check if word can be formed ----
function canFormWord($word, $rack) {
    $rackLetters = countLetters($rack);
    $wordLetters = countLetters($word);

    foreach ($wordLetters as $letter => $needed) {
        if (!isset($rackLetters[$letter])) {
            if (!isset($rackLetters["?"]) || $rackLetters["?"] < $needed) {
                return false;
            } else {
                $rackLetters["?"] -= $needed;
            }
        } else {
            if ($rackLetters[$letter] >= $needed) {
                $rackLetters[$letter] -= $needed;
            } else {
                $extra = $needed - $rackLetters[$letter];
                if (!isset($rackLetters["?"]) || $rackLetters["?"] < $extra) {
                    return false;
                }
                $rackLetters["?"] -= $extra;
                $rackLetters[$letter] = 0;
            }
        }
    }
    return true;
}

// ---- HELPER: Count letters in a string ----
function countLetters($str) {
    $letters = [];
    for ($i = 0; $i < strlen($str); $i++) {
        $ch = $str[$i];
        $letters[$ch] = ($letters[$ch] ?? 0) + 1;
    }
    return $letters;
}