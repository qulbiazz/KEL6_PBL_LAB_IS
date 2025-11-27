<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'InLet - Information and Learning Engineering Technology'; ?></title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="<?php echo $bodyClass ?? 'bg-gray-50'; ?>">
    <!-- Navigation -->
    <?php
    // Get current page from URL parameter
    $current_page_param = $_GET['page'] ?? 'home';

    function isActive($page_param)
    {
        global $current_page_param;
        return ($current_page_param == $page_param) ? 'text-blue-600 font-semibold' : 'text-gray-700 hover:text-blue-600';
    }

    function isSectionLink()
    {
        return 'text-gray-700 hover:text-blue-600 nav-section-link';
    }
    ?>
    <nav class="bg-white shadow-md sticky top-0 z-40">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center">
                        <img src="assets/images/inlet_logo.png" alt="InLet Logo" class="h-12 md:h-14 lg:h-16 w-auto">
                    </a>
                </div>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="index.php" class="nav-home-link <?php echo isActive('home'); ?> transition">Home</a>
                    <a href="index.php?page=about" class="<?php echo isActive('about'); ?> transition">About</a>
                    <a href="index.php#products" data-section="products" class="<?php echo isSectionLink(); ?> transition">Products</a>
                    <a href="index.php#team" data-section="team" class="<?php echo isSectionLink(); ?> transition">Team</a>
                    <a href="index.php#news" data-section="news" class="<?php echo isSectionLink(); ?> transition">News</a>
                    <a href="index.php#gallery" data-section="gallery" class="<?php echo isSectionLink(); ?> transition">Gallery</a>
                    <a href="index.php#research" data-section="research" class="<?php echo isSectionLink(); ?> transition">Research</a>
                    <a href="index.php?page=borrowing" class="block py-2 <?php echo isActive('borrowing'); ?>">Peminjaman</a>
                    <a href="index.php?page=attendance" class="<?php echo ($current_page_param == 'attendance') ? 'bg-blue-700 text-white' : 'bg-transparent text-blue-700 hover:bg-blue-700 hover:text-white'; ?> border border-blue-700 px-4 py-2 rounded-lg transition flex items-center">
                        <i class="fas fa-clipboard-check mr-2"></i>Absen
                    </a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-700">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <a href="index.php" class="nav-home-link block py-2 <?php echo isActive('home'); ?>">Home</a>
                <a href="index.php?page=about" class="block py-2 <?php echo isActive('about'); ?>">About</a>
                <a href="index.php#products" data-section="products" class="block py-2 <?php echo isSectionLink(); ?>">Products</a>
                <a href="index.php#team" data-section="team" class="block py-2 <?php echo isSectionLink(); ?>">Team</a>
                <a href="index.php#news" data-section="news" class="block py-2 <?php echo isSectionLink(); ?>">News</a>
                <a href="index.php#gallery" data-section="gallery" class="block py-2 <?php echo isSectionLink(); ?>">Gallery</a>
                <a href="index.php#research" data-section="research" class="block py-2 <?php echo isSectionLink(); ?>">Research</a>
                <a href="index.php?page=borrowing" class="block py-2 <?php echo isActive('borrowing'); ?>">Peminjaman</a>
                <a href="index.php?page=attendance" class="<?php echo ($current_page_param == 'attendance') ? 'bg-blue-700 text-white' : 'bg-transparent text-blue-700 hover:bg-blue-700 hover:text-white'; ?> border border-blue-700 px-4 py-2 rounded-lg transition flex items-center mt-2">
                    <i class="fas fa-clipboard-check mr-2"></i>Absen
                </a>
            </div>
        </div>
    </nav>
    