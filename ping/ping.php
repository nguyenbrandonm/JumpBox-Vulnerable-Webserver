# Viewer.php page (Command Injection)
<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);

$host = $_GET['host'] ?? '';
$output = '';
$error = '';

/**
 * SAFE implementation:
 * - validates host roughly (IP or hostname)
 * - escapes shell argument
 * If you intentionally want this to be vulnerable in a lab, you'd remove escaping/validation.
 */
function is_valid_host(string $h): bool {
    $h = trim($h);
    if ($h === '') return false;

    // Accept IPv4
    if (filter_var($h, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return true;

    // Accept simple hostnames / domains (basic pattern, not perfect)
    return (bool)preg_match('/^(?=.{1,253}$)([a-zA-Z0-9-]{1,63}\.)*[a-zA-Z0-9-]{1,63}$/', $h);
}

if ($host !== '') {
    if (!is_valid_host($host)) {
        $error = "Invalid host. Enter an IPv4 address or a hostname (e.g., 8.8.8.8 or example.com).";
    } else {
        $cmd = "ping -c 4 " . escapeshellarg($host) . " 2>&1";
        $output = shell_exec($cmd) ?? '';
        if (trim($output) === '') {
            $error = "No output returned.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>JumpBox – Network Ping Utility</title>

    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #0a0a0a;
            color: #00ff00;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        header {
            background-color: #1a1a1a;
            color: #00ff00;
            padding: 20px;
            text-align: center;
            font-size: 2.5rem;
            border-bottom: 2px solid #00ff00;
        }

        /* === NAV TABS (same as index/viewer) === */
        .nav {
            display: flex;
            justify-content: center;
            background-color: #111;
            border-bottom: 1px solid #00ff00;
            flex-wrap: wrap;
        }

        .nav a {
            padding: 15px 25px;
            color: #00ff00;
            text-decoration: none;
            font-size: 1rem;
            border-right: 1px solid #00ff00;
            transition: background 0.2s;
        }

        .nav a:last-child { border-right: none; }

        .nav a:hover { background-color: #1e1e1e; }

        .nav a.active {
            background-color: #00ff00;
            color: #0a0a0a;
            font-weight: bold;
        }

        /* === MAIN LAYOUT === */
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 140px;
            box-sizing: border-box;
            text-align: center;
        }

        h2 {
            font-size: 2rem;
            margin: 20px 0 10px;
        }

        .subtext {
            color: #7cff7c;
            opacity: 0.85;
            margin-bottom: 26px;
            font-size: 0.95rem;
        }

        /* === CARD / PANEL === */
        .panel {
            width: 100%;
            background-color: #1e1e1e;
            border: 1px solid rgba(0,255,0,0.25);
            border-radius: 12px;
            padding: 22px;
            box-sizing: border-box;
            text-align: left;
            box-shadow: 0 0 24px rgba(0,255,0,0.10);
        }

        .panel-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-row {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        label {
            font-size: 1rem;
            min-width: 150px;
        }

        input[type="text"] {
            flex: 1;
            min-width: 260px;
            padding: 12px 12px;
            font-size: 1rem;
            background-color: #0f0f0f;
            color: #00ff00;
            border: 1px solid #00ff00;
            border-radius: 8px;
            font-family: inherit;
            box-sizing: border-box;
        }

        button {
            background-color: #00ff00;
            color: #0a0a0a;
            padding: 12px 18px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            white-space: nowrap;
        }

        button:hover {
            background-color: #00cc00;
        }

        /* === OUTPUT === */
        .output {
            width: 100%;
            margin-top: 18px;
            background-color: #1e1e1e;
            border: 1px solid rgba(0,255,0,0.25);
            border-radius: 12px;
            padding: 18px;
            box-sizing: border-box;
            text-align: left;
        }

        .output pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.4;
        }

        .hintbar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 12px;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .hint {
            border: 1px dashed rgba(0,255,0,0.35);
            padding: 8px 10px;
            border-radius: 10px;
            color: #7cff7c;
        }

        .error {
            color: #ff8080;
        }

        footer {
            background-color: #1a1a1a;
            color: #00ff00;
            text-align: center;
            padding: 10px;
            font-size: 0.8rem;
            position: fixed;
            bottom: 0;
            width: 100%;
            left: 0;
        }
    </style>
</head>

<body>
<header>
    Network Ping Utility
</header>

<nav class="nav">
    <a href="index.php"  class="<?= $currentPage === 'index.php'  ? 'active' : '' ?>">Dashboard</a>
    <a href="upload.php" class="<?= $currentPage === 'upload.php' ? 'active' : '' ?>">Upload</a>
    <a href="viewer.php" class="<?= $currentPage === 'viewer.php' ? 'active' : '' ?>">Viewer</a>
    <a href="ping.php"   class="<?= $currentPage === 'ping.php'   ? 'active' : '' ?>">Ping</a>
</nav>

<div class="container">
    <h2>Ping a Server</h2>
    <div class="subtext">Enter a domain or IPv4 address to test connectivity.</div>

    <div class="panel">
        <div class="panel-title">Ping Console</div>

        <form method="GET" action="ping.php">
            <div class="form-row">
                <label for="host">Server IP/Domain:</label>
                <input type="text" name="host" id="host" placeholder="e.g., 8.8.8.8 or example.com"
                       value="<?= htmlspecialchars($host) ?>" required>
                <button type="submit">Ping</button>
            </div>

            <div class="hintbar">
                <div class="hint">Examples: <code>8.8.8.8</code>, <code>1.1.1.1</code>, <code>example.com</code></div>
                <div class="hint">Tip: keep this lab isolated.</div>
            </div>
        </form>
    </div>

    <?php if ($host !== ''): ?>
        <div class="output">
            <?php if ($error): ?>
                <pre class="error"><?= htmlspecialchars($error) ?></pre>
            <?php else: ?>
                <pre><?= htmlspecialchars($output) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    &copy; 2026 JumpBox Lab | All Rights Reserved
</footer>

</body>
</html>
