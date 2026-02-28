<?php
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: /url/login");
    exit;
}

// Handle URL Deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM short_urls WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header("Location: /url/dashboard"); 
    exit;
}

// Generate random 4-letter string
function generateRandomString($length = 4) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Handle URL Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['long_url'])) {
    $long_url = $_POST['long_url'];
    
    if (!preg_match("~^(?:f|ht)tps?://~i", $long_url)) {
        $long_url = "https://" . $long_url;
    }

    $short_code = '';
    $is_unique = false;

    while (!$is_unique) {
        $short_code = generateRandomString(4);
        $stmt = $pdo->prepare("SELECT id FROM short_urls WHERE short_code = ?");
        $stmt->execute([$short_code]);
        if (!$stmt->fetch()) {
            $is_unique = true;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO short_urls (short_code, long_url) VALUES (?, ?)");
    $stmt->execute([$short_code, $long_url]);
    header("Location: /url/dashboard");
    exit;
}

// Fetch existing links
$stmt = $pdo->query("SELECT * FROM short_urls ORDER BY created_at DESC");
$links = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tag.re URL</title>
    <link rel="icon" href="image/tag_re_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        /* Theme Variables */
        :root {
            /* Default Dark Theme */
            --md-bg: #121413;
            --md-surface: #1E221F;
            --md-on-surface: #E8EFEA;
            --md-primary: #ffff00; /* Yellow Accent */
            --md-on-primary: #000000; /* Switched to pure black for best contrast on yellow */
            --md-outline: #4A534D;
            --md-error: #FF5A5F;
            --card-border: rgba(255,255,255,0.03);
            --link-secondary: #A0ABA5;
        }

        [data-theme="light"] {
            /* Light Theme Overrides */
            --md-bg: #F7F9F8;
            --md-surface: #FFFFFF;
            --md-on-surface: #121413;
            --md-primary: #ffff00; /* Yellow Accent */
            --md-on-primary: #000000; 
            --md-outline: #D1D8D4;
            --md-error: #BA1A1A;
            --card-border: rgba(0,0,0,0.08);
            --link-secondary: #5A635D;
        }

        body { 
            padding: 0; margin: 0;
            background-color: var(--md-bg); 
            color: var(--md-on-surface);
            font-family: 'Inter', sans-serif; 
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        h2, h3 { font-weight: 800; letter-spacing: -0.5px; }

        .top-nav {
            width: 100%; background-color: var(--md-surface);
            padding: 15px 30px; box-sizing: border-box;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--card-border);
            position: sticky; top: 0; z-index: 100;
            transition: background-color 0.3s ease;
        }
        
        .nav-actions { display: flex; align-items: center; gap: 20px; }

        .btn-theme {
            background: transparent; color: var(--md-on-surface);
            border: 1px solid var(--md-outline); border-radius: 100px;
            padding: 8px 16px; font-size: 0.9rem; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-theme:hover { background-color: var(--md-outline); color: var(--md-bg); }

        .main-container {
            width: 100%; max-width: 800px; padding: 40px 20px; box-sizing: border-box;
            margin: 0 auto;
        }
        
        .creation-card {
            background: var(--md-surface); border-radius: 24px;
            padding: 35px; margin-bottom: 40px; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }
        
        input[type="url"], input[type="text"] {
            font-family: 'Inter', sans-serif;
            font-size: 1.1rem; padding: 16px 20px; border-radius: 12px;
            background: var(--md-bg); border: 2px solid var(--md-outline);
            color: var(--md-on-surface);
            transition: border-color 0.3s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.3s, background-color 0.3s ease;
            box-sizing: border-box; width: 100%;
        }
        
        input[type="url"]:focus, input[type="text"]:focus { 
            border-color: var(--md-primary); 
            outline: none; 
            /* Updated rgba to match #ffff00 for the focus ring */
            box-shadow: 0 0 0 4px rgba(255, 255, 0, 0.15); 
        }
        
        .search-container { margin-bottom: 24px; }

        /* General M3 Primary Button */
        .m3-btn {
            font-family: 'Inter', sans-serif; font-size: 1.1rem; font-weight: 600;
            background-color: var(--md-primary); color: var(--md-on-primary);
            border: none; padding: 16px 32px; border-radius: 100px; 
            cursor: pointer; position: relative; overflow: hidden;
            transition: transform 0.2s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.2s;
        }
        .m3-btn:active { transform: scale(0.97); } 
        
        .ripple {
            position: absolute; background: rgba(0, 0, 0, 0.15);
            border-radius: 50%; transform: scale(0);
            animation: ripple-anim 0.6s linear; pointer-events: none;
        }
        @keyframes ripple-anim { to { transform: scale(4); opacity: 0; } }

        /* Card List */
        .link-card {
            background: var(--md-surface); border-radius: 16px;
            padding: 24px; margin-bottom: 16px; display: flex;
            justify-content: space-between; align-items: center;
            border: 1px solid var(--card-border);
            transition: transform 0.3s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.3s, background-color 0.3s ease;
        }
        .link-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
        
        .link-info { flex-grow: 1; overflow: hidden; margin-right: 20px; min-width: 0; }
        
        .short-url-wrapper { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
        .short-url { font-size: 1.4rem; font-weight: 800; color: var(--md-primary); text-decoration: none; }
        
        .btn-copy {
            background: transparent; color: var(--md-on-surface);
            border: 1px solid var(--md-outline); border-radius: 8px;
            padding: 4px 10px; font-size: 0.8rem; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-copy:hover { background-color: var(--md-outline); color: var(--md-bg); }

        .long-url { color: var(--link-secondary); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; text-decoration: none; }
        
        .action-buttons { display: flex; gap: 10px; }
        .btn-delete { 
            background-color: transparent; color: var(--md-error); 
            border: 2px solid var(--md-error); padding: 10px 20px; 
            font-size: 0.95rem; border-radius: 100px; cursor: pointer;
            font-family: 'Inter', sans-serif; font-weight: 600; transition: background-color 0.2s;
        }
        .btn-delete:hover { background-color: rgba(255, 90, 95, 0.1); }
        
        .footer { text-align: center; padding: 40px 20px; color: var(--link-secondary); font-size: 0.9rem; font-weight: 600; }
        
        /* Mobile Optimizations */
        @media (max-width: 600px) {
            .top-nav { padding: 12px 20px; }
            #brandLogo { margin-bottom: 0; height: 24px !important; }
            .nav-actions { gap: 15px; }
            .btn-theme { padding: 6px 12px; font-size: 0.85rem; }

            .form-row { flex-direction: column; gap: 16px !important; }
            .form-row input, .form-row button { width: 100%; }
            
            .link-card { flex-direction: column; align-items: flex-start; padding: 20px; }
            .link-info { margin-right: 0; width: 100%; margin-bottom: 16px; }
            
            .short-url-wrapper { flex-direction: row; justify-content: space-between; width: 100%; }
            .short-url { font-size: 1.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            
            .action-buttons { margin-top: 0; width: 100%; }
            .action-buttons form { width: 100%; }
            .btn-delete { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

    <div class="top-nav">
        <img id="brandLogo" src="image/tag_re_dark_logo.png" alt="Tag.re" style="height: 30px; object-fit: contain;">
        <div class="nav-actions">
            <button id="themeToggle" class="btn-theme">☀️ Light</button>
            <a href="logout.php" style="color: var(--md-primary); font-weight: 600; text-decoration: none;">Log Out</a>
        </div>
    </div>

    <div class="main-container">
        
        <div class="creation-card">
            <h2 style="margin-top: 0; margin-bottom: 24px;">Shorten a new link</h2>
            <form method="POST" class="form-row" style="display: flex; gap: 12px;">
                <input type="url" name="long_url" placeholder="Paste your long URL here..." required style="flex-grow: 1; margin: 0;">
                <button type="submit" class="m3-btn">Shorten</button>
            </form>
        </div>

        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search your links..." onkeyup="filterLinks()">
        </div>

        <div id="linksContainer">
            <?php foreach ($links as $link): ?>
            <div class="link-card">
                <div class="link-info">
                    <div class="short-url-wrapper">
                        <a href="/url/<?= $link['short_code'] ?>" target="_blank" class="short-url">tag.re/url/<?= $link['short_code'] ?></a>
                        <button type="button" class="btn-copy" onclick="copyURL('https://tag.re/url/<?= $link['short_code'] ?>', this)">Copy</button>
                    </div>
                    <a href="<?= htmlspecialchars($link['long_url']) ?>" target="_blank" class="long-url" title="<?= htmlspecialchars($link['long_url']) ?>">
                        <?= htmlspecialchars($link['long_url']) ?>
                    </a>
                </div>
                <div class="action-buttons">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this link?');" style="margin: 0;">
                        <input type="hidden" name="delete_id" value="<?= $link['id'] ?>">
                        <button type="submit" class="btn-delete">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($links)): ?>
                <div style="text-align: center; color: var(--link-secondary); padding: 40px 0; font-weight: 600;">No links found. Create one above!</div>
            <?php endif; ?>
        </div>

    </div>

    <div class="footer">
        Crafted with care from Keralam by Tagrelate 🌴
    </div>

    <script>
        // --- Theme Toggle & Logo Swap Logic ---
        const themeToggle = document.getElementById('themeToggle');
        const brandLogo = document.getElementById('brandLogo');
        
        // Check local storage for user preference on load
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            themeToggle.innerText = '🌙 Dark';
            brandLogo.src = 'image/tag_re_light_logo.png'; // Set light logo on load
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'light') {
                // Switching to Dark Mode
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'dark');
                themeToggle.innerText = '☀️ Light';
                brandLogo.src = 'image/tag_re_dark_logo.png'; // Swap to dark logo
            } else {
                // Switching to Light Mode
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                themeToggle.innerText = '🌙 Dark';
                brandLogo.src = 'image/tag_re_light_logo.png'; // Swap to light logo
            }
        });

        // --- Copy to Clipboard Logic ---
        function copyURL(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                const originalText = btn.innerText;
                btn.innerText = 'Copied!';
                btn.style.backgroundColor = 'var(--md-primary)';
                btn.style.color = 'var(--md-on-primary)';
                btn.style.borderColor = 'var(--md-primary)';
                
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.style.backgroundColor = 'transparent';
                    btn.style.color = 'var(--md-on-surface)';
                    btn.style.borderColor = 'var(--md-outline)';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        }

        // --- Material 3 Ripple Effect ---
        const buttons = document.querySelectorAll('.m3-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const circle = document.createElement('span');
                circle.classList.add('ripple');
                circle.style.left = `${x}px`;
                circle.style.top = `${y}px`;
                
                const diameter = Math.max(rect.width, rect.height);
                circle.style.width = circle.style.height = `${diameter}px`;
                
                this.appendChild(circle);
                setTimeout(() => circle.remove(), 600);
            });
        });

        // --- Instant Search Filtering ---
        function filterLinks() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('link-card');
            
            for (let i = 0; i < cards.length; i++) {
                let cardText = cards[i].innerText.toLowerCase();
                if (cardText.includes(input)) {
                    cards[i].style.display = "flex";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>