<!DOCTYPE html>
<html lang="en" xml:lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?php echo $pageTitle ?? 'INLET - Information and Learning Engineering Technology | Politeknik Negeri Malang'; ?></title>
    <meta name="title" content="<?php echo $pageTitle ?? 'INLET - Information and Learning Engineering Technology'; ?>">
    <meta name="description" content="<?php echo $pageDescription ?? 'INLET adalah laboratorium riset dan inovasi Teknik Informatika di Politeknik Negeri Malang. Mengembangkan teknologi IoT, sistem cerdas, dan solusi inovatif.'; ?>">
    
    <!-- Preconnect to external resources for faster loading -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    
    <?php
    // Load SEO Helper
    require_once __DIR__ . '/../../includes/SEOHelper.php';
    
    // Initialize SEO Helper with site URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $siteUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
    SEOHelper::init($siteUrl);
    
    // Get current page
    $currentPage = $_GET['page'] ?? 'home';
    
    // Get page-specific SEO data
    $seoData = SEOHelper::getPageSEO($currentPage, $pageData ?? []);
    
    // Output meta tags
    echo SEOHelper::generateMetaTags($seoData);
    
    // Output preload hints for performance
    echo SEOHelper::getPreloadHints();
    
    // Output structured data
    echo SEOHelper::getOrganizationSchema();
    echo SEOHelper::getWebSiteSchema();
    ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/inlet_logo_only.png">
    <link rel="apple-touch-icon" href="assets/images/inlet_logo_only.png">
    
    <!-- Critical CSS - Inline for faster FCP -->
    <style>
        /* Critical above-the-fold styles */
        body{margin:0;font-family:system-ui,-apple-system,sans-serif;background:#f9fafb}
        .loading-skeleton{background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200% 100%;animation:loading 1.5s infinite}
        @keyframes loading{0%{background-position:200% 0}100%{background-position:-200% 0}}
        /* Only top navigation bar */
        body > nav, header nav{background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
        /* Footer nav should be transparent */
        footer nav{background:transparent!important;box-shadow:none!important}
    </style>
    
    <!-- Main Stylesheets with optimized loading -->
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css"></noscript>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="<?php echo $bodyClass ?? 'bg-gray-50'; ?>">
    <!-- Navigation -->
    <?php
    // Load site settings for dynamic logo
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/SiteSettingsModel.php';

    $database = new Database();
    $db = $database->getConnection();
    $siteSettingsModel = new SiteSettingsModel($db);
    $siteSettings = $siteSettingsModel->getSettings();

    $logoPath = $siteSettings['logo_path'] ?? 'assets/images/inlet_logo_only.png';
    $logoAlt = $siteSettings['logo_alt'] ?? 'INLET Logo';

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
                        <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($logoAlt); ?>" class="h-12 md:h-14 lg:h-16 w-auto object-contain">
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
                    <a href="index.php#guestbook" data-section="guestbook" class="<?php echo isSectionLink(); ?> transition">Guestbook</a>
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
                <a href="index.php#guestbook" data-section="guestbook" class="block py-2 <?php echo isSectionLink(); ?>">GuestBook</a>
                <a href="index.php?page=borrowing" class="block py-2 <?php echo isActive('borrowing'); ?>">Peminjaman</a>
                <a href="index.php?page=attendance" class="<?php echo ($current_page_param == 'attendance') ? 'bg-blue-700 text-white' : 'bg-transparent text-blue-700 hover:bg-blue-700 hover:text-white'; ?> border border-blue-700 px-4 py-2 rounded-lg transition flex items-center mt-2">
                    <i class="fas fa-clipboard-check mr-2"></i>Absen
                </a>
            </div>
        </div>
    </nav>