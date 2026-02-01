<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JumpBox Control Panel</title>

<style>
body {
    margin: 0;
    font-family: "Courier New", monospace;
    background: #0a0a0a;
    color: #00ff00;
}

header {
    background: #111;
    padding: 22px;
    text-align: center;
    font-size: 2.4rem;
    border-bottom: 2px solid #00ff00;
}

.nav {
    display: flex;
    justify-content: center;
    background: #000;
    border-bottom: 1px solid #00ff00;
}

.nav a {
    padding: 14px 26px;
    color: #00ff00;
    text-decoration: none;
    border-right: 1px solid #00ff00;
}

.nav a:last-child { border-right: none; }

.nav a:hover {
    background: #1a1a1a;
}

.nav a.active {
    background: #00ff00;
    color: #000;
    font-weight: bold;
}

.container {
    max-width: 1100px;
    margin: auto;
    padding: 40px 20px 140px;
    text-align: center;
}

h2 {
    margin-bottom: 20px;
}

.hero {
    max-width: 420px;
    width: 90%;
    border: 2px solid #00ff00;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0,255,0,.4);
    margin-bottom: 30px;
}

.button {
    background: #00ff00;
    color: #000;
    border: none;
    padding: 18px 26px;
    font-size: 1.1rem;
    border-radius: 8px;
    cursor: pointer;
}

.button:hover { background: #00cc00; }

.terminal {
    display: none;
    margin: 40px auto 0;
    background: #1e1e1e;
    padding: 20px;
    border-radius: 8px;
    max-width: 900px;
    text-align: left;
}

#cmd {
    width: 100%;
    margin-top: 10px;
    background: #000;
    color: #00ff00;
    border: 1px solid #00ff00;
    padding: 10px;
    font-family: inherit;
}

footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background: #111;
    padding: 10px;
    font-size: 0.8rem;
    text-align: center;
}
</style>
</head>

<body>

<header>JUMPBOX CONTROL PANEL</header>

<nav class="nav">
    <a href="/" class="active">Dashboard</a>
    <a href="/uploads/uploads.php">Upload</a>
    <a href="/dir/viewer.php">Viewer</a>
    <a href="/ping/ping.php">Ping</a>
</nav>

<div class="container">
    <h2>Welcome to the JumpBox Interface</h2>

    <img src="/assets/JumpBox.png" class="hero" alt="JumpBox">

    <br>
    <button class="button" onclick="openTerminal()">Enter Command Mode</button>

    <div class="terminal" id="terminal">
        <div id="output">
            Welcome to the JumpBox Terminal<br>
            Type <code>help</code> for commands.<br><br>
        </div>
        <input id="cmd" placeholder="Type a command..." onkeydown="handleCmd(event)">
    </div>

    <p style="color:#888;margin-top:20px;">
        For educational purposes only. Do not attack real systems.
    </p>
</div>

<footer>&copy; 2026 JumpBox Lab</footer>

<script>
function openTerminal() {
    document.querySelector(".button").style.display = "none";
    document.getElementById("terminal").style.display = "block";
}

function handleCmd(e) {
    if (e.key !== "Enter") return;

    const cmd = e.target.value.trim().toLowerCase();
    const out = document.getElementById("output");

    out.innerHTML += "&gt; " + cmd + "<br>";
    e.target.value = "";

    switch(cmd) {
        case "help":
            out.innerHTML +=
                "upload  - go to upload interface<br>" +
                "viewer  - go to file viewer<br>" +
                "ping    - network utility<br>" +
                "matrix  - ???<br><br>";
            break;
        case "upload":
            window.location = "/uploads/uploads.php";
            return;
        case "viewer":
            window.location = "/dir/viewer.php";
            return;
        case "ping":
            window.location = "/ping/ping.php";
            return;
        case "matrix":
            out.innerHTML += "Wake up, Neo...<br><br>";
            break;
        default:
            out.innerHTML += "Command not found.<br><br>";
    }

    out.scrollTop = out.scrollHeight;
}
</script>

</body>
</html>
