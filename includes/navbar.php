<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Get section from hash (for active state)
$current_section = '';

function isActive($page, $section = '') {
    global $current_page;
    
    // For Home link - only active if on index.php AND no hash in URL
    if ($page == 'index.php' && $section == '') {
        // Check if we're on index and likely at top (no specific section)
        return ($current_page == 'index.php') ? 'text-blue-600 font-semibold' : 'text-gray-700 hover:text-blue-600';
    }
    
    // For About page
    if ($page == 'about.php') {
        return ($current_page == 'about.php') ? 'text-blue-600 font-semibold' : 'text-gray-700 hover:text-blue-600';
    }
    
    // For sections - will be handled by JavaScript
    if ($section) {
        return 'text-gray-700 hover:text-blue-600 nav-section-link';
    }
    
    return 'text-gray-700 hover:text-blue-600';
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
            <div class="hidden md:flex space-x-8">
                <a href="index.php" id="nav-home" class="<?php echo isActive('index.php'); ?> transition">Home</a>
                <a href="about.php" class="<?php echo isActive('about.php'); ?> transition">About</a>
                <a href="index.php#products" data-section="products" class="<?php echo isActive('index.php', 'products'); ?> transition">Products</a>
                <a href="index.php#team" data-section="team" class="<?php echo isActive('index.php', 'team'); ?> transition">Team</a>
                <a href="index.php#news" data-section="news" class="<?php echo isActive('index.php', 'news'); ?> transition">News</a>
                <a href="index.php#gallery" data-section="gallery" class="<?php echo isActive('index.php', 'gallery'); ?> transition">Gallery</a>
                <a href="index.php#research" data-section="research" class="<?php echo isActive('index.php', 'research'); ?> transition">Research</a>
            </div>
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-700">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-4">
            <a href="index.php" id="nav-home-mobile" class="block py-2 <?php echo isActive('index.php'); ?>">Home</a>
            <a href="about.php" class="block py-2 <?php echo isActive('about.php'); ?>">About</a>
            <a href="index.php#products" data-section="products" class="block py-2 <?php echo isActive('index.php', 'products'); ?>">Products</a>
            <a href="index.php#team" data-section="team" class="block py-2 <?php echo isActive('index.php', 'team'); ?>">Team</a>
            <a href="index.php#news" data-section="news" class="block py-2 <?php echo isActive('index.php', 'news'); ?>">News</a>
            <a href="index.php#gallery" data-section="gallery" class="block py-2 <?php echo isActive('index.php', 'gallery'); ?>">Gallery</a>
            <a href="index.php#research" data-section="research" class="block py-2 <?php echo isActive('index.php', 'research'); ?>">Research</a>
        </div>
    </div>
</nav>
