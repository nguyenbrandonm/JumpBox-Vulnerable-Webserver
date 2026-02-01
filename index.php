<?php
session_start();
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = '/' . trim($requestPath, '/'); // normalize

function is_active(string $section, string $path): bool {
    if ($section === 'dashboard') {
        return $path === '/' || $path === '';
    }
    // Match first path segment
    return str_starts_with($path . '/', '/' . $section . '/');
}

$nav = [
    'dashboard' => ['label' => 'Dashboard', 'href' => '/'],
    'uploads'   => ['label' => 'Upload',    'href' => '/uploads/'],
    'dir'       => ['label' => 'Viewer',    'href' => '/dir/'],
    'ping'      => ['label' => 'Ping',      'href' => '/ping/'],
];
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
