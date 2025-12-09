<!-- Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="bg-gray-900 text-white w-64 fixed top-16 bottom-0 left-0 z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto border-r border-gray-800 custom-scrollbar">
    <div class="p-4">
        <!-- Close button for mobile -->
        <button id="sidebar-close" class="lg:hidden absolute top-4 right-4 text-gray-400 hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>

        <nav class="space-y-1 mt-2">
            <!-- Dashboard -->
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-tachometer-alt w-6 text-center mr-2"></i>
                Dashboard
            </a>

            <!-- Absensi Dropdown -->
            <?php
            $absensi_pages = ['students.php', 'attendance.php'];
            $is_absensi_active = in_array(basename($_SERVER['PHP_SELF']), $absensi_pages);
            ?>
            <div>
                <button type="button" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors focus:outline-none <?php echo $is_absensi_active ? 'text-white bg-gray-800' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>" onclick="toggleDropdown('absensi-dropdown', this)">
                    <i class="fas fa-user-check w-6 text-center mr-2"></i>
                    <span class="flex-1 text-left">Absensi</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $is_absensi_active ? 'transform rotate-180' : ''; ?>"></i>
                </button>
                <div id="absensi-dropdown" class="space-y-1 mt-1 pl-10 <?php echo $is_absensi_active ? '' : 'hidden'; ?>">
                    <a href="students.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Kelola Mahasiswa</a>
                    <a href="attendance.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Log Absensi</a>
                </div>
            </div>

            <!-- Peminjaman Dropdown -->
            <?php
            $peminjaman_pages = ['borrowing.php', 'equipment.php'];
            $is_peminjaman_active = in_array(basename($_SERVER['PHP_SELF']), $peminjaman_pages);
            ?>
            <div>
                <button type="button" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors focus:outline-none <?php echo $is_peminjaman_active ? 'text-white bg-gray-800' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>" onclick="toggleDropdown('peminjaman-dropdown', this)">
                    <i class="fas fa-clipboard-list w-6 text-center mr-2"></i>
                    <span class="flex-1 text-left">Peminjaman</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $is_peminjaman_active ? 'transform rotate-180' : ''; ?>"></i>
                </button>
                <div id="peminjaman-dropdown" class="space-y-1 mt-1 pl-10 <?php echo $is_peminjaman_active ? '' : 'hidden'; ?>">
                    <a href="borrowing.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'borrowing.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Kelola Peminjaman</a>
                    <a href="equipment.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'equipment.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Kelola Alat</a>
                </div>
            </div>

            <!-- Content Dropdown -->
            <?php
            $content_pages = ['banners.php', 'news.php', 'gallery.php', 'research.php', 'partners.php', 'cards.php', 'products.php', 'manage_about.php'];
            $is_content_active = in_array(basename($_SERVER['PHP_SELF']), $content_pages);
            ?>
            <div>
                <button type="button" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors focus:outline-none <?php echo $is_content_active ? 'text-white bg-gray-800' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>" onclick="toggleDropdown('content-dropdown', this)">
                    <i class="fas fa-photo-video w-6 text-center mr-2"></i>
                    <span class="flex-1 text-left">Content</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $is_content_active ? 'transform rotate-180' : ''; ?>"></i>
                </button>
                <div id="content-dropdown" class="space-y-1 mt-1 pl-10 <?php echo $is_content_active ? '' : 'hidden'; ?>">
                    <a href="banners.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Carousel Banners</a>
                    <a href="manage_about.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'manage_about.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Manage About</a>
                    <a href="news.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'news.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">News</a>
                    <a href="gallery.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Gallery</a>
                    <a href="research.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'research.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Research Activities</a>
                    <a href="partners.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'partners.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Partners</a>
                    <a href="cards.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'cards.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Card Data</a>
                    <a href="products.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Products</a>
                </div>
            </div>

            <!-- Team Dropdown -->
            <?php
            $team_pages = ['team.php', 'lecturer_details.php'];
            $is_team_active = in_array(basename($_SERVER['PHP_SELF']), $team_pages);
            ?>
            <div>
                <button type="button" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors focus:outline-none <?php echo $is_team_active ? 'text-white bg-gray-800' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>" onclick="toggleDropdown('team-dropdown', this)">
                    <i class="fas fa-users w-6 text-center mr-2"></i>
                    <span class="flex-1 text-left">Team</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $is_team_active ? 'transform rotate-180' : ''; ?>"></i>
                </button>
                <div id="team-dropdown" class="space-y-1 mt-1 pl-10 <?php echo $is_team_active ? '' : 'hidden'; ?>">
                    <a href="team.php" class="block px-4 py-2 text-sm rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'team.php' ? 'text-blue-400 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">Team Members</a>
                </div>
            </div>

            <!-- Buku Tamu -->
            <a href="guestbook.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'guestbook.php' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-book-open w-6 text-center mr-2"></i>
                Buku Tamu
            </a>

        </nav>
        
        <!-- Site Settings - Fixed at bottom -->
        <div class="mt-8 pt-4 border-t border-gray-800">
            <a href="site-settings.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'site-settings.php' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <i class="fas fa-cog w-6 text-center mr-2"></i>
                Site Settings
            </a>
        </div>
    </div>
</aside>