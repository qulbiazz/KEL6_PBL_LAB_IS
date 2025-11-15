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
<nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-40">
    <div class="container mx-auto px-6 md:px-8 lg:px-12">
        <div class="flex justify-between items-center py-3">
            <div class="flex items-center">
                <a href="../../index.php" class="flex items-center">
                    <img src="../../assets/images/inlet_logo.png" alt="InLet Logo" class="h-12 md:h-14 lg:h-16 w-auto">
                </a>
            </div>
            <div class="hidden md:flex space-x-8 items-center">
                <a href="../../index.php" class="nav-home-link text-gray-700 hover:text-blue-600 transition">Home</a>
                <a href="../../index.php?page=about" class="text-gray-700 hover:text-blue-600 transition">About</a>
                <a href="../../index.php#products" data-section="products" class="text-gray-700 hover:text-blue-600 nav-section-link transition">Products</a>
                <a href="../../index.php#team" data-section="team" class="text-gray-700 hover:text-blue-600 nav-section-link transition">Team</a>
                <a href="../../index.php#news" data-section="news" class="text-gray-700 hover:text-blue-600 nav-section-link transition">News</a>
                <a href="../../index.php#gallery" data-section="gallery" class="text-gray-700 hover:text-blue-600 nav-section-link transition">Gallery</a>
                <a href="../../index.php#research" data-section="research" class="text-gray-700 hover:text-blue-600 nav-section-link transition">Research</a>
                <a href="../../index.php?page=borrowing" class="text-gray-700 hover:text-blue-600 transition">Peminjaman</a>
                <a href="attendance.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center">
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
            <a href="../../index.php" class="nav-home-link block py-2 text-gray-700 hover:text-blue-600">Home</a>
            <a href="../../index.php?page=about" class="block py-2 text-gray-700 hover:text-blue-600">About</a>
            <a href="../../index.php#products" data-section="products" class="block py-2 text-gray-700 hover:text-blue-600 nav-section-link">Products</a>
            <a href="../../index.php#team" data-section="team" class="block py-2 text-gray-700 hover:text-blue-600 nav-section-link">Team</a>
            <a href="../../index.php#news" data-section="news" class="block py-2 text-gray-700 hover:text-blue-600 nav-section-link">News</a>
            <a href="../../index.php#gallery" data-section="gallery" class="block py-2 text-gray-700 hover:text-blue-600 nav-section-link">Gallery</a>
            <a href="../../index.php#research" data-section="research" class="block py-2 text-gray-700 hover:text-blue-600 nav-section-link">Research</a>
            <a href="../../index.php?page=borrowing" class="block py-2 text-gray-700 hover:text-blue-600">Peminjaman</a>
            <a href="attendance.php" class="block py-2 mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-lg text-center">
                <i class="fas fa-clipboard-check mr-2"></i>Absen
            </a>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>
