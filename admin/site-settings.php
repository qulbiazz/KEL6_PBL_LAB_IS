<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SiteSettingsModel.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$siteSettingsModel = new SiteSettingsModel($db);

// Get current settings
$settings = $siteSettingsModel->getSettings();

$pageTitle = "Site Settings - INLET Admin";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1f2937;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        /* Ensure hidden class works */
        .hidden {
            display: none !important;
        }
        /* Ensure rotation works */
        .rotate-180 {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="flex-1 p-4 md:p-8 ml-0 lg:ml-64 min-h-screen transition-all duration-300">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Site Settings</h1>
                    <p class="text-gray-600 mt-2">Kelola logo, konten footer, dan informasi kontak website</p>
                </div>

                <!-- Alert Messages -->
                <div id="alert-container" class="mb-6"></div>

                <!-- Settings Form -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form Section -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Logo Settings Card -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-image mr-3 text-blue-600"></i>
                                    Logo Settings
                                </h2>
                            </div>

                            <form id="logoForm" enctype="multipart/form-data">
                                <div class="space-y-4">
                                    <!-- Current Logo -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                                            <img id="currentLogo" src="../<?php echo htmlspecialchars($settings['logo_path']); ?>" 
                                                 alt="Current Logo" class="h-20 object-contain mx-auto">
                                        </div>
                                    </div>

                                    <!-- Logo Alt Text -->
                                    <div>
                                        <label for="logo_alt" class="block text-sm font-medium text-gray-700 mb-2">
                                            Logo Alt Text
                                        </label>
                                        <input type="text" id="logo_alt" name="logo_alt" 
                                               value="<?php echo htmlspecialchars($settings['logo_alt']); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="INLET Logo">
                                    </div>

                                    <!-- Upload New Logo -->
                                    <div>
                                        <label for="logo_file" class="block text-sm font-medium text-gray-700 mb-2">
                                            Upload New Logo
                                        </label>
                                        <input type="file" id="logo_file" name="logo_file" accept="image/*"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <p class="text-sm text-gray-500 mt-2">Supported: JPG, PNG, SVG, WEBP (Max 2MB)</p>
                                    </div>

                                    <!-- Submit Button -->
                                    <button type="submit" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>
                                        Update Logo
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Footer About Card -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-info-circle mr-3 text-blue-600"></i>
                                    Footer About Section
                                </h2>
                            </div>

                            <form id="footerAboutForm">
                                <div class="space-y-4">
                                    <div>
                                        <label for="footer_about_title" class="block text-sm font-medium text-gray-700 mb-2">
                                            Title
                                        </label>
                                        <input type="text" id="footer_about_title" name="footer_about_title" 
                                               value="<?php echo htmlspecialchars($settings['footer_about_title']); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="INLET">
                                    </div>

                                    <div>
                                        <label for="footer_about_text" class="block text-sm font-medium text-gray-700 mb-2">
                                            Description
                                        </label>
                                        <textarea id="footer_about_text" name="footer_about_text" rows="4"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                  placeholder="Innovation and Learning Engineering Technology Laboratory"><?php echo htmlspecialchars($settings['footer_about_text']); ?></textarea>
                                    </div>

                                    <div>
                                        <label for="footer_copyright" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-copyright mr-2 text-gray-500"></i>Copyright Text
                                        </label>
                                        <input type="text" id="footer_copyright" name="footer_copyright" 
                                               value="<?php echo htmlspecialchars($settings['footer_copyright'] ?? '© 2025 Information and Learning Engineering Technology Laboratory. All rights reserved.'); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="© 2025 Information and Learning Engineering Technology Laboratory. All rights reserved.">
                                        <p class="text-xs text-gray-500 mt-1">Text yang akan ditampilkan di bagian bawah footer</p>
                                    </div>

                                    <button type="submit" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>
                                        Update Footer About
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Contact Information Card -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-address-book mr-3 text-blue-600"></i>
                                    Contact Information
                                </h2>
                            </div>

                            <form id="contactForm">
                                <div class="space-y-4">
                                    <div>
                                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-envelope mr-2 text-gray-500"></i>Email
                                        </label>
                                        <input type="email" id="contact_email" name="contact_email" 
                                               value="<?php echo htmlspecialchars($settings['contact_email']); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="inlet@polinema.ac.id">
                                    </div>

                                    <div>
                                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-phone mr-2 text-gray-500"></i>Phone
                                        </label>
                                        <input type="text" id="contact_phone" name="contact_phone" 
                                               value="<?php echo htmlspecialchars($settings['contact_phone']); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="+62 341 404424">
                                    </div>

                                    <div>
                                        <label for="contact_address" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-map-marker-alt mr-2 text-gray-500"></i>Address
                                        </label>
                                        <textarea id="contact_address" name="contact_address" rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                  placeholder="Jl. Soekarno Hatta No.9, Malang"><?php echo htmlspecialchars($settings['contact_address']); ?></textarea>
                                    </div>

                                    <button type="submit" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>
                                        Update Contact Info
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Social Media Card -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-share-alt mr-3 text-blue-600"></i>
                                    Social Media Links
                                </h2>
                            </div>

                            <form id="socialForm">
                                <div class="space-y-4">
                                    <div>
                                        <label for="social_facebook" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fab fa-facebook mr-2 text-blue-600"></i>Facebook
                                        </label>
                                        <input type="url" id="social_facebook" name="social_facebook" 
                                               value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="https://facebook.com/inlet.polinema">
                                    </div>

                                    <div>
                                        <label for="social_instagram" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fab fa-instagram mr-2 text-pink-600"></i>Instagram
                                        </label>
                                        <input type="url" id="social_instagram" name="social_instagram" 
                                               value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="https://instagram.com/inlet.polinema">
                                    </div>

                                    <div>
                                        <label for="social_youtube" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fab fa-youtube mr-2 text-red-600"></i>YouTube
                                        </label>
                                        <input type="url" id="social_youtube" name="social_youtube" 
                                               value="<?php echo htmlspecialchars($settings['social_youtube'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="https://youtube.com/@inlet.polinema">
                                    </div>

                                    <div>
                                        <label for="social_linkedin" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fab fa-linkedin mr-2 text-blue-700"></i>LinkedIn
                                        </label>
                                        <input type="url" id="social_linkedin" name="social_linkedin" 
                                               value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="https://linkedin.com/company/inlet">
                                    </div>

                                    <div>
                                        <label for="social_twitter" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fab fa-twitter mr-2 text-blue-400"></i>Twitter/X
                                        </label>
                                        <input type="url" id="social_twitter" name="social_twitter" 
                                               value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="https://twitter.com/inlet_polinema">
                                    </div>

                                    <button type="submit" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>
                                        Update Social Links
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Quick Links Management Card -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-link mr-3 text-blue-600"></i>
                                    Footer Quick Links
                                </h2>
                                <span class="text-sm text-gray-500">Max 8 links</span>
                            </div>

                            <form id="quickLinksForm">
                                <div id="quickLinksContainer" class="space-y-3 mb-4">
                                    <!-- Quick link items will be added here dynamically -->
                                </div>
                                
                                <button type="button" id="addQuickLinkBtn" 
                                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center mb-4">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Link
                                </button>

                                <button type="submit" 
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-save mr-2"></i>
                                    Save Quick Links
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-6 space-y-6">
                            <!-- Logo Preview Card -->
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-eye mr-2 text-blue-600"></i>
                                    Logo Preview
                                </h3>
                                <div class="border-2 border-gray-200 rounded-lg p-6 bg-gray-50">
                                    <img id="logoPreview" src="../<?php echo htmlspecialchars($settings['logo_path']); ?>" 
                                         alt="Logo Preview" class="h-24 object-contain mx-auto">
                                </div>
                            </div>

                            <!-- Footer Preview Card -->
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-eye mr-2 text-blue-600"></i>
                                    Footer Preview
                                </h3>
                                <div class="bg-gray-800 text-white p-4 rounded-lg text-sm">
                                    <h4 id="previewTitle" class="font-bold mb-2"><?php echo htmlspecialchars($settings['footer_about_title']); ?></h4>
                                    <p id="previewText" class="text-gray-400 text-xs mb-4"><?php echo htmlspecialchars($settings['footer_about_text']); ?></p>
                                    
                                    <div class="space-y-1 text-xs text-gray-400 mb-3">
                                        <div id="previewEmail"><i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($settings['contact_email']); ?></div>
                                        <div id="previewPhone"><i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($settings['contact_phone']); ?></div>
                                    </div>

                                    <div id="previewSocial" class="flex space-x-3 mb-4">
                                        <?php if (!empty($settings['social_facebook'])): ?>
                                        <i class="fab fa-facebook text-lg"></i>
                                        <?php endif; ?>
                                        <?php if (!empty($settings['social_instagram'])): ?>
                                        <i class="fab fa-instagram text-lg"></i>
                                        <?php endif; ?>
                                        <?php if (!empty($settings['social_youtube'])): ?>
                                        <i class="fab fa-youtube text-lg"></i>
                                        <?php endif; ?>
                                        <?php if (!empty($settings['social_linkedin'])): ?>
                                        <i class="fab fa-linkedin text-lg"></i>
                                        <?php endif; ?>
                                        <?php if (!empty($settings['social_twitter'])): ?>
                                        <i class="fab fa-twitter text-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="border-t border-gray-700 pt-3 mt-3">
                                        <p id="previewCopyright" class="text-xs text-gray-500 text-center"><?php echo htmlspecialchars($settings['footer_copyright'] ?? '© 2025 Information and Learning Engineering Technology Laboratory. All rights reserved.'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                                <div class="space-y-2">
                                    <a href="../index.php" target="_blank" 
                                       class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 text-center">
                                        <i class="fas fa-external-link-alt mr-2"></i>
                                        View Website
                                    </a>
                                    <button onclick="location.reload()" 
                                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200">
                                        <i class="fas fa-sync-alt mr-2"></i>
                                        Refresh Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="../assets/js/admin-site-settings.js"></script>
</body>

</html>
