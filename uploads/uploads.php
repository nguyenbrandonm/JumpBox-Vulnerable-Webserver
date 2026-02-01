<?php
session_start();

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = '/' . trim($requestPath, '/');

function is_active(string $section, string $path): bool {
    if ($section === 'dashboard') {
        return $path === '/' || $path === '';
    }
    return str_starts_with($path . '/', '/' . $section . '/');
}

// Upload storage directory (filesystem path)
// This stores uploaded files directly in the /uploads folder (same folder as this script).
$uploadDirFs = __DIR__ . '/';
$uploadDirUrl = '/uploads/';

$message = null;

if (isset($_POST['submit'])) {
    if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
        $message = "Error uploading file.";
    } else {
        $filename = basename($_FILES["fileToUpload"]["name"]);
        $targetFs = $uploadDirFs . $filename;

        // Keep your original size check
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            $message = "Sorry, your file is too large.";
        } else {
            // Intentionally insecure: no filetype validation.
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFs)) {
                // Helpful output for the lab (shows where it landed)
                $message = "File uploaded successfully: " . htmlspecialchars($uploadDirUrl . $filename);
            } else {
                $message = "Error uploading file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JumpBox – File Upload</title>

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

        /* === MAIN CONTAINER === */
        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px 140px;
            box-sizing: border-box;
            text-align: center;
        }

        h2 {
            font-size: 2rem;
            margin: 20px 0;
        }

        .subtext {
            color: #7cff7c;
            font-size: 0.95rem;
            opacity: 0.85;
        }

        .upload-form {
            background-color: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            border: 1px solid rgba(0, 255, 0, 0.25);
        }

        input[type="file"] {
            margin-bottom: 15px;
            font-family: inherit;
            color: #00ff00;
        }

        button {
            background-color: #00ff00;
            color: #0a0a0a;
            padding: 10px 22px;
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
            color: #00ff00;
            width: 100%;
            padding: 18px;
            border-radius: 10px;
            margin-top: 25px;
            border: 1px solid rgba(0, 255, 0, 0.25);
            word-break: break-word;
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
    JumpBox – File Upload
</header>

<nav class="nav">
    <a href="/" class="<?= is_active('dashboard', $path) ? 'active' : '' ?>">Dashboard</a>
    <a href="/uploads/" class="<?= is_active('uploads', $path) ? 'active' : '' ?>">Upload</a>
    <a href="/dir/" class="<?= is_active('dir', $path) ? 'active' : '' ?>">Files</a>
    <a href="/ping/" class="<?= is_active('ping', $path) ? 'active' : '' ?>">Ping</a>
</nav>

<div class="container">
    <h2>Upload a File</h2>
    <div class="subtext">Upload any file type (including PHP scripts)</div>

    <form method="POST" enctype="multipart/form-data">
        <div class="upload-form">
            <input type="file" name="fileToUpload" id="fileToUpload" required><br>
            <button type="submit" name="submit">Upload</button>
        </div>
    </form>

    <?php if ($message !== null): ?>
        <div class="output"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</div>

<footer>
    &copy; 2026 JumpBox Lab | All Rights Reserved
</footer>

</body>
</html>
