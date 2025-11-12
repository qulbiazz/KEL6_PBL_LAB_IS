<nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
    <div class="px-4 md:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <!-- Mobile Menu Toggle -->
            <button id="mobile-menu-toggle" class="lg:hidden text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
            
            <h2 class="text-lg md:text-xl font-bold text-gray-800">InLet Admin Panel</h2>
        </div>
        
        <div class="flex items-center space-x-2 md:space-x-4">
            <a href="../index.php" target="_blank" class="text-gray-600 hover:text-blue-600 text-sm md:text-base">
                <i class="fas fa-external-link-alt mr-1"></i> 
                <span class="hidden sm:inline">View Site</span>
            </a>
            <div class="flex items-center">
                <span class="text-gray-700 mr-2 md:mr-3 text-sm md:text-base hidden md:inline">
                    <i class="fas fa-user-circle mr-1"></i>
                    <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 md:px-4 py-2 rounded text-sm md:text-base">
                    <i class="fas fa-sign-out-alt mr-1"></i> 
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>
