<aside class="bg-gray-800 text-white w-64 min-h-screen">
    <div class="p-6">
        <h3 class="text-lg font-semibold mb-6">Content Management</h3>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="banners.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-images mr-2"></i> Carousel Banners
            </a>
            <a href="cards.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'cards.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-th-large mr-2"></i> Card Data
            </a>
            <a href="products.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-box mr-2"></i> Products
            </a>
            <a href="partners.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'partners.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-handshake mr-2"></i> Partners
            </a>
            <a href="team.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'team.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-users mr-2"></i> Team Members
            </a>
            <a href="lecturer_details.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'lecturer_details.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-user-tie mr-2"></i> Lecturer Details
            </a>
            <a href="news.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'news.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-newspaper mr-2"></i> News
            </a>
            <a href="gallery.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-photo-video mr-2"></i> Gallery
            </a>
            <a href="research.php" class="block px-4 py-2 rounded hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'research.php' ? 'bg-gray-700' : ''; ?>">
                <i class="fas fa-flask mr-2"></i> Research Activities
            </a>
        </nav>
    </div>
</aside>
