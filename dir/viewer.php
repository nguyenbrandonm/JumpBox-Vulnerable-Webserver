<?php
session_start();

/**
 * File location: /dir/viewer.php
 *
 * This viewer is intended to be vulnerable to directory traversal in "vuln" mode.
 * For a safe comparison, you can switch to: ?mode=safe
 *
 * Examples:
 *  - /dir/viewer.php?file=notes.txt
 *  - /dir/viewer.php?file=../../../../etc/passwd&mode=vuln
 */

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = '/' . trim($requestPath, '/');

function is_active(string $section, string $path): bool {
    if ($section === 'dashboard') {
        return $path === '/' || $path === '';
    }
    return str_starts_with($path . '/', '/' . $section . '/');
}

$currentFile = $_GET['file'] ?? '';
$mode = $_GET['mode'] ?? 'vuln'; // vuln | safe

/**
 * Base directory for the "legit" files list.
 * Your repo has /files at the root, so from /dir/viewer.php it's one level up.
 * This avoids hard-coding /var/www/html and works under /var/www/jumpbox.
 */
$baseDir = realpath(__DIR__ . '/../files') ?: (__DIR__ . '/../files');

// “Suggested” files displayed as clickable pills
$availableFiles = [
    'motivation.txt',
    'notes.txt',
    'todo.txt',
    'changelog.txt',
    'backup.txt'
];

$content = null;
$error = null;

if ($currentFile !== '') {

    if ($mode === 'safe') {
        /**
         * SAFE MODE:
         * Only allow files that resolve INSIDE $baseDir.
         */
        $candidate = realpath($baseDir . '/' . $currentFile);

        if ($candidate && str_starts_with($candidate, rtrim($baseDir, '/') . '/') && file_exists($candidate)) {
            $content = file_get_contents($candidate);
        } else {
            $error = 'File not found or access denied.';
        }

    } else {
        /**
         * VULN MODE (Directory Traversal):
         * - We still use realpath(), but we do NOT enforce that the resolved path stays under $baseDir.
         * - That makes traversal possible if the file exists and permissions allow it.
         */
        $candidate = realpath($baseDir . '/' . $currentFile);

        if ($candidate && file_exists($candidate)) {
            $content = file_get_contents($candidate);
        } else {
            $error = 'File not found or access denied.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>JumpBox – File Viewer</title>

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

        /* === NAV TABS === */
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

        .nav a:last-child {
            border-right: none;
        }

        .nav a:hover {
            background-color: #1e1e1e;
        }

        .nav a.active {
            background-color: #00ff00;
            color: #0a0a0a;
            font-weight: bold;
        }

        /* === MAIN CONTAINER === */
        .container {
            width: 100%;
            max-width: 1200px;
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
            font-size: 0.95rem;
            margin: 0 0 20px;
            opacity: 0.85;
        }

        .panel {
            background-color: #1e1e1e;
            width: 100%;
            padding: 18px;
            border-radius: 10px;
            margin: 0 auto;
            text-align: left;
            box-sizing: border-box;
            border: 1px solid rgba(0, 255, 0, 0.25);
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

        .badge.safe {
            border-color: rgba(0,255,0,0.35);
        }

        .badge.vuln {
            border-color: rgba(255,128,128,0.5);
            color: #ff8080;
        }

        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .file-pill {
            display: inline-block;
            padding: 8px 14px;
            border: 1px solid #00ff00;
            border-radius: 999px;
            text-decoration: none;
            color: #00ff00;
            background: transparent;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .file-pill:hover {
            background-color: #00ff00;
            color: #0a0a0a;
        }

        .file-pill.active {
            background-color: #00ff00;
            color: #0a0a0a;
            font-weight: bold;
        }

        .form-group {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        input[type="text"] {
            width: min(520px, 100%);
            padding: 10px 12px;
            background-color: #0f0f0f;
            color: #00ff00;
            border: 1px solid #00ff00;
            border-radius: 6px;
            font-family: inherit;
            box-sizing: border-box;
        }

        button {
            background-color: #00ff00;
            color: #0a0a0a;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
        }

        button:hover {
            background-color: #00cc00;
        }

        .output {
            background-color: #1e1e1e;
            width: 100%;
            margin: 22px auto 0;
            padding: 18px;
            border-radius: 10px;
            text-align: left;
            box-sizing: border-box;
            border: 1px solid rgba(0, 255, 0, 0.25);
        }

        .output pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.4;
        }

        .notice {
            color: #7cff7c;
            opacity: 0.9;
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
    JumpBox – File Viewer
</header>

<nav class="nav">
    <a href="/" class="<?= is_active('dashboard', $path) ? 'active' : '' ?>">Dashboard</a>
    <a href="/uploads/" class="<?= is_active('uploads', $path) ? 'active' : '' ?>">Upload</a>
    <a href="/dir/" class="<?= is_active('dir', $path) ? 'active' : '' ?>">Viewer</a>
    <a href="/ping/" class="<?= is_active('ping', $path) ? 'active' : '' ?>">Ping</a>
</nav>

<div class="container">
    <h2>View Text Files</h2>
    <div class="subtext">Select a file below or enter a filename manually.</div>

    <div class="panel">
        <div class="panel-title">
            <span>Available Files</span>
            <?php if ($mode === 'safe'): ?>
                <span class="badge safe">mode: safe</span>
            <?php else: ?>
                <span class="badge vuln">mode: vuln</span>
            <?php endif; ?>
        </div>

        <div class="file-list">
            <?php foreach ($availableFiles as $f): ?>
                <a class="file-pill <?= ($currentFile === $f ? 'active' : '') ?>"
                   href="/dir/viewer.php?file=<?= urlencode($f) ?>&mode=<?= urlencode($mode) ?>">
                    <?= htmlspecialchars($f) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="/dir/viewer.php" class="form-group">
            <input
                type="text"
                name="file"
                placeholder="Filename (e.g., notes.txt)"
                value="<?= htmlspecialchars($currentFile) ?>"
                required
            />
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>" />
            <button type="submit">View</button>
        </form>

        <?php if ($mode !== 'safe'): ?>
            <div class="subtext" style="margin-top: 16px;">
                Lab hint: switch to <code>?mode=safe</code> for the hardened variant.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($currentFile !== ''): ?>
        <div class="output">
            <?php if ($content !== null): ?>
                <pre><?= htmlspecialchars($content) ?></pre>
            <?php else: ?>
                <pre class="notice"><?= htmlspecialchars($error ?? 'File not found or access denied.') ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    &copy; 2026 JumpBox Lab | All Rights Reserved
</footer>

</body>
</html>
