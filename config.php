<?php
session_start();

$db_host = 'localhost';
$db_name = 'name';
$db_user = 'db_username';
$db_pass = 'db_password';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Reusable Material 3 Dark Mode CSS
$m3_css = '
<style>
    :root {
        --md-bg: #1C1B1F;
        --md-surface: #2B2930;
        --md-on-surface: #E6E1E5;
        --md-primary: #D0BCFF; /* Change this to your logo accent color */
        --md-on-primary: #381E72;
        --md-outline: #938F99;
        font-family: system-ui, -apple-system, sans-serif;
    }
    body {
        background-color: var(--md-bg);
        color: var(--md-on-surface);
        margin: 0; padding: 20px;
        display: flex; flex-direction: column; align-items: center;
    }
    .container {
        background-color: var(--md-surface);
        padding: 30px; border-radius: 16px;
        width: 100%; max-width: 600px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }
    .header-img { max-width: 200px; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto; }
    input[type="text"], input[type="password"], input[type="url"] {
        width: 100%; padding: 12px; margin: 8px 0 20px 0;
        background: transparent; border: 1px solid var(--md-outline);
        color: var(--md-on-surface); border-radius: 4px; box-sizing: border-box;
    }
    button {
        background-color: var(--md-primary); color: var(--md-on-primary);
        border: none; padding: 12px 24px; border-radius: 20px;
        font-weight: bold; cursor: pointer; width: 100%; transition: opacity 0.2s;
    }
    button:hover { opacity: 0.9; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--md-outline); }
    a { color: var(--md-primary); text-decoration: none; }
    .error { color: #FFB4AB; margin-bottom: 15px; text-align: center; }
</style>
';
?>