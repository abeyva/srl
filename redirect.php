<?php
require 'config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $stmt = $pdo->prepare("SELECT long_url FROM short_urls WHERE short_code = ?");
    $stmt->execute([$code]);
    $result = $stmt->fetch();

    if ($result) {
        // Increment click counter
        $update = $pdo->prepare("UPDATE short_urls SET clicks = clicks + 1 WHERE short_code = ?");
        $update->execute([$code]);

        // Redirect
        header('Location: ' . $result['long_url']);
        exit;
    } else {
        echo "URL not found.";
    }
} else {
    echo "Invalid request.";
}
?>