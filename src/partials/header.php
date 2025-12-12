<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'coffee.casper-rs.dev';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$currentUrl = $protocol . '://' . $host . $requestUri;

$canonicalUrl = preg_replace('/[?#].*$/', '', $currentUrl);

$pageTitle = $GLOBALS['pageTitle'] ?? "CoffeeSaver";
$pageDescription = $GLOBALS['pageDescription'] ?? "CoffeeSaver - Monitor en optimaliseer je koffiegebruik. Track je koffieconsumptie, bespaar geld en ontdek je koffiegewoonten met gedetailleerde statistieken.";
$pageImage = $GLOBALS['pageImage'] ?? "https://coffee.casper-rs.dev/src/images/og-image.png";
$siteName = "CoffeeSaver";
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="koffie, koffiegebruik, koffie statistieken, koffie tracker, koffie monitor, koffie analytics, koffie dashboard, koffie besparing, koffie kosten">
    <meta name="author" content="CoffeeSaver">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Dutch">

    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($pageImage, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:locale" content="nl_NL">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($pageImage, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image:alt" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">

    <link rel="icon" type="image/png" href="/src/images/favicon.ico">
    <link rel="apple-touch-icon" href="/src/images/favicon.ico">
    <link rel="manifest" href="/src/images/favicon.json">

    <meta name="theme-color" content="#0b0705">
    <meta name="msapplication-TileColor" content="#0b0705">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/src/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="/src/scripts/toast.js" defer></script>
</head>

<body class="bg-[#0b0705] text-amber-50 font-sans relative overflow-x-hidden">