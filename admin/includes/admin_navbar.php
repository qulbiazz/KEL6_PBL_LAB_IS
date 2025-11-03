<nav class="bg-white shadow-md">
    <div class="px-8 py-4 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">InLet Admin Panel</h2>
        <div class="flex items-center space-x-4">
            <a href="../index.php" target="_blank" class="text-gray-600 hover:text-blue-600">
                <i class="fas fa-external-link-alt mr-1"></i> View Site
            </a>
            <div class="flex items-center">
                <span class="text-gray-700 mr-3">
                    <i class="fas fa-user-circle mr-1"></i>
                    <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>
