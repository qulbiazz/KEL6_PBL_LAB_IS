<nav class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg fixed top-0 left-0 right-0 z-50">
    <div class="px-4 md:px-8 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <!-- Mobile Menu Toggle -->
            <button id="mobile-menu-toggle" class="lg:hidden text-white hover:text-blue-100 focus:outline-none transition-colors">
                <i class="fas fa-bars text-2xl"></i>
            </button>
            
            <!-- Logo & Title -->
            <div class="flex items-center space-x-3">
                <div class="bg-white p-1.5 rounded-lg shadow-sm">
                    <img src="assets/image/inlet_logo_only.png" alt="InLet Logo" class="h-8 w-8 object-contain">
                </div>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-white">InLet Admin</h2>
                    <p class="text-xs text-blue-100 hidden md:block">Management Panel</p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center space-x-2 md:space-x-4">
            <!-- Quick Actions -->
            <div class="hidden lg:flex items-center space-x-2">
                <!-- View Site -->
                <a href="../index.php" target="_blank" class="flex items-center space-x-2 bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-4 py-2 rounded-lg transition-all backdrop-blur-sm" title="View Website">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="text-sm font-medium">View Site</span>
                </a>
            </div>
            
            <!-- Mobile View Site -->
            <a href="../index.php" target="_blank" class="lg:hidden bg-white bg-opacity-10 hover:bg-opacity-20 text-white p-2.5 rounded-lg transition-all backdrop-blur-sm" title="View Website">
                <i class="fas fa-external-link-alt"></i>
            </a>
            
            <!-- User Dropdown -->
            <div class="relative">
                <button id="user-menu-toggle" class="flex items-center space-x-2 bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-3 md:px-4 py-2 rounded-lg transition-all backdrop-blur-sm">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                        <p class="text-xs text-blue-100">Administrator</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs hidden md:block"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
                    <!-- User Info -->
                    <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                        <p class="text-xs text-gray-600">Administrator</p>
                    </div>
                    
                    <!-- Menu Items -->
                    <div class="py-2">
                        <a href="../index.php" target="_blank" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition-colors lg:hidden">
                            <i class="fas fa-external-link-alt w-5 text-blue-600"></i>
                            <span class="ml-3">View Website</span>
                        </a>
                        <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                            <i class="fas fa-user-cog w-5 text-blue-600"></i>
                            <span class="ml-3">Profile Settings</span>
                        </a>

                        <div class="border-t border-gray-200 my-2"></div>
                        <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span class="ml-3 font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
// User dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
    const userMenuToggle = document.getElementById('user-menu-toggle');
    const userDropdown = document.getElementById('user-dropdown');
    
    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }
});
</script>
