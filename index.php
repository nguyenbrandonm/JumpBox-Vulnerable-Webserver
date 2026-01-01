# Home rendering page
<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JumpBox Control Panel</title>

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

        /* === NAVIGATION === */
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

        /* === MAIN CONTAINER (RESPONSIVE FIX) === */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-sizing: border-box;
        }
        h2 {
            font-size: 2rem;
            margin: 20px 0;
        }

        /* === IMAGE === */
        .hacker-image {
            margin: 25px 0;
            width: 90%;
            max-width: 420px;
            border: 2px solid #00ff00;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.4);
        }

        /* === BUTTON === */
        .button {
            padding: 20px;
            font-size: 1.2rem;
            color: #0a0a0a;
            background-color: #00ff00;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
            transition: all 0.3s;
        }

        .button:hover {
            background-color: #00cc00;
            transform: scale(1.05);
        }

        /* === TERMINAL (WIDER + RESPONSIVE) === */
        .terminal {
            background-color: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            width: 95%;
            max-width: 1000px;
            min-height: 220px;
            border-radius: 8px;
            overflow-y: auto;
            font-size: 1rem;
            white-space: pre-wrap;
            margin-top: 25px;
            box-sizing: border-box;
        }

        #commandInput {
            width: 95%;
            max-width: 1000px;
            margin-top: 10px;
            background-color: #1e1e1e;
            color: #00ff00;
            border: 1px solid #00ff00;
            border-radius: 5px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            box-sizing: border-box;
        }

        .blinking-cursor {
            animation: blink 0.8s infinite step-start;
        }

        @keyframes blink {
            50% { opacity: 0; }
        }

        .info-text {
            font-size: 0.95rem;
            margin-top: 20px;
            color: #888;
        }

        footer {
            background-color: #1a1a1a;
            color: #00ff00;
            text-align: center;
            padding: 12px;
            font-size: 0.8rem;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
        }

        .hidden-content {
            display: none;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>

<body>

<header>
    JUMPBOX CONTROL PANEL
</header>

<nav class="nav">
    <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="upload.php" class="<?= $currentPage === 'upload.php' ? 'active' : '' ?>">Upload</a>
    <a href="viewer.php" class="<?= $currentPage === 'viewer.php' ? 'active' : '' ?>">Viewer</a>
    <a href="ping.php" class="<?= $currentPage === 'ping.php' ? 'active' : '' ?>">Ping</a>
</nav>

<div class="container">
    <h2>Welcome to the JumpBox Interface</h2>

    <img src="/assets/JumpBox.png" alt="JumpBox Hacker Interface" class="hacker-image">

    <button class="button" onclick="revealTerminal()">Enter Command Mode</button>

    <div class="hidden-content" id="terminalContainer">
        <div class="terminal" id="terminal">
            <div>Welcome to the JumpBox Terminal</div>
            <div>Type <code>help</code> for available commands.</div>
            <div class="blinking-cursor">_</div>
        </div>
        <input type="text" id="commandInput" placeholder="Type a command..." onkeydown="handleCommand(event)">
    </div>

    <div class="info-text">
        For educational purposes only. Do not attempt to hack real systems.
    </div>
</div>

<footer>
    &copy; 2026 JumpBox Lab | All Rights Reserved
</footer>

<script>
function revealTerminal() {
    document.getElementById('terminalContainer').style.display = 'flex';
    document.querySelector('.button').style.display = 'none';
}

function handleCommand(event) {
    if (event.key === 'Enter') {
        processCommand(event.target.value.trim());
        event.target.value = '';
    }
}

function processCommand(command) {
    const terminal = document.getElementById('terminal');
    const cmd = command.toLowerCase();

    terminal.innerHTML += `<div>&gt; ${command}</div>`;

    switch (cmd) {
        case 'help':
            terminal.innerHTML += `
                <div>Available commands:</div>
                <div>- upload     → Go to upload interface</div>
                <div>- download   → View exposed files</div>
                <div>- ping       → Network utility</div>
                <div>- matrix     → ???</div>
            `;
            break;

        case 'upload':
            window.location.href = 'upload.php';
            return;

        case 'download':
            window.location.href = 'download.php';
            return;

        case 'ping':
            window.location.href = 'ping.php';
            return;

        case 'matrix':
            runMatrixEffect(terminal);
            return;

        default:
            terminal.innerHTML += `<div>Command not found. Type <code>help</code>.</div>`;
    }

    terminal.scrollTop = terminal.scrollHeight;
    terminal.innerHTML += `<div class="blinking-cursor">_</div>`;
}

/* === FUN EASTER EGG === */
function runMatrixEffect(terminal) {
    const lines = [
        "[+] Initializing JumpBox Subsystem...",
        "[+] Loading neural interface modules...",
        "[+] Bypassing reality constraints...",
        "[!] WARNING: This is not the real world",
        "[+] Injecting green text illusion...",
        "[+] System status: YOU ARE NOT ROOT",
        "[+] Just kidding. Or am I?",
        "[✓] Matrix mode disengaged."
    ];

    let i = 0;
    const interval = setInterval(() => {
        if (i < lines.length) {
            terminal.innerHTML += `<div>${lines[i]}</div>`;
            terminal.scrollTop = terminal.scrollHeight;
            i++;
        } else {
            clearInterval(interval);
            terminal.innerHTML += `<div class="blinking-cursor">_</div>`;
        }
    }, 350);
}
</script>

</body>
</html>
