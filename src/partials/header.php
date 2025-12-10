<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? "CoffeeSaver"; ?></title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- App CSS -->
    <link rel="stylesheet" href="/src/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Toast (moet in de head want hele site gebruikt het) -->
    <script src="/src/JS/toast.js" defer></script>
</head>

<body class="bg-[#0b0705] text-amber-50 font-sans relative overflow-x-hidden">