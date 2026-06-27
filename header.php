<?php
// PHP Header Partial
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($title) ? $title : 'Cyber Security LAB – Week 4'; ?> | CyberLAB</title>
  <link rel="stylesheet" href="/public/css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <div class="nav-brand">
    <a href="/index.php" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:0.5rem;">
      <span class="nav-icon">🔐</span>
      <span>CyberLAB <span class="nav-week">Week 4</span></span>
    </a>
  </div>
  <ul class="nav-links">
    <li><a href="/index.php" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">🏠 Home</a></li>
    <li class="nav-dropdown">
      <a href="/file-upload-1.php" class="<?php echo str_starts_with($current_page, 'file-upload') ? 'active' : ''; ?>">📁 File Upload</a>
      <ul class="dropdown-menu">
        <li><a href="/file-upload-1.php">Level 1: No Validation</a></li>
        <li><a href="/file-upload-2.php">Level 2: Blacklist Filter</a></li>
        <li><a href="/file-upload-3.php">Level 3: Extension Whitelist</a></li>
        <li><a href="/file-upload-fixed.php">Level 4: Secure Version</a></li>
      </ul>
    </li>
    <li class="nav-dropdown">
      <a href="/info-disclosure.php" class="<?php echo str_starts_with($current_page, 'info-disclosure') ? 'active' : ''; ?>">🔍 Info Disclosure</a>
      <ul class="dropdown-menu">
        <li><a href="/info-disclosure.php">⚠️ Vulnerable</a></li>
        <li><a href="/info-disclosure-fixed.php">✅ Fixed</a></li>
      </ul>
    </li>
    <li class="nav-dropdown">
      <a href="/race-condition.php" class="<?php echo str_starts_with($current_page, 'race-condition') ? 'active' : ''; ?>">⚡ Race Condition</a>
      <ul class="dropdown-menu">
        <li><a href="/race-condition.php">⚠️ Vulnerable</a></li>
        <li><a href="/race-condition-fixed.php">✅ Fixed</a></li>
      </ul>
    </li>
    <li>
      <a href="/recon.php" class="<?php echo $current_page === 'recon' ? 'active' : ''; ?>" style="color:<?php echo $current_page === 'recon' ? '#b39ddb' : ''; ?>">🕵️ Recon Lab</a>
    </li>
  </ul>
</nav>

<div class="warning-banner">
  <span class="warn-icon">⚠️</span>
  <strong>PERINGATAN:</strong> Lab ini dibuat <strong>hanya untuk pembelajaran lokal</strong> dan tidak boleh digunakan untuk menyerang sistem nyata.
</div>
