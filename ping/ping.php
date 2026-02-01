<?php
session_start();

/**
 * File location: /ping/ping.php
 *
 * UI route: /ping/ (landing page we can add next)
 * Vulnerable endpoint: /ping/ping.php
 *
 * By default this runs in SAFE mode.
 * To intentionally enable command injection for the lab:
 *   - set ?mode=vuln in the URL
 * Example:
 *   /ping/ping.php?host=8.8.8.8&mode=vuln
 */

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = '/' . trim($requestPath, '/');

function is_active(string $section, string $path): bool {
    if ($section === 'dashboard') {
        return $path === '/' || $path === '';
    }
    return str_starts_with($path . '/', '/' . $section . '/');
}

$host = $_GET['host'] ?? '';
$mode = $_GET['mode'] ?? 'safe'; // safe | vuln
$output = '';
$error = '';

/**
 * SAFE implementation:
 * - validates host roughly (IP or hostname)
 * - escapes shell argument
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
    if ($mode === 'vuln') {
        /**
         * INTENTIONALLY VULNERABLE (Command Injection)
         * No escaping, no validation.
         */
        $cmd = "ping -c 4 " . $host . " 2>&1";
        $output = shell_exec($cmd) ?? '';
        if (trim($output) === '') {
            $error = "No output returned.";
        }
    } else {
        /**
         * SAFE MODE
         */
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
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .badge {
            border: 1px solid rgba(0,255,0,0.35);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.85rem;
            color: #7cff7c;
        }

        .badge.vuln {
            border-color: rgba(255,128,128,0.5);
            color: #ff8080;
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
    <a href="/" class="<?= is_active('dashboard', $path) ? 'active' : '' ?>">Dashboard</a>
    <a href="/uploads/" class="<?= is_active('uploads', $path) ? 'active' : '' ?>">Upload</a>
    <a href="/dir/" class="<?= is_active('dir', $path) ? 'active' : '' ?>">Viewer</a>
    <a href="/ping/" class="<?= is_active('ping', $path) ? 'active' : '' ?>">Ping</a>
</nav>

<div class="container">
    <h2>Ping a Server</h2>
    <div class="subtext">Enter a domain or IPv4 address to test connectivity.</div>

    <div class="panel">
        <div class="panel-title">
            <span>Ping Console</span>
            <?php if ($mode === 'vuln'): ?>
                <span class="badge vuln">mode: vuln</span>
            <?php else: ?>
                <span class="badge">mode: safe</span>
            <?php endif; ?>
        </div>

        <form method="GET" action="/ping/ping.php">
            <div class="form-row">
                <label for="host">Server IP/Domain:</label>
                <input type="text" name="host" id="host" placeholder="e.g., 8.8.8.8 or example.com"
                       value="<?= htmlspecialchars($host) ?>" required>

                <!-- keep mode sticky -->
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

                <button type="submit">Ping</button>
            </div>

            <div class="hintbar">
                <div class="hint">Examples: <code>8.8.8.8</code>, <code>1.1.1.1</code>, <code>example.com</code></div>
                <div class="hint">Tip: keep this lab isolated.</div>

                <?php if ($mode !== 'vuln'): ?>
                    <div class="hint">Lab: add <code>&amp;mode=vuln</code> for the vulnerable variant.</div>
                <?php endif; ?>
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
