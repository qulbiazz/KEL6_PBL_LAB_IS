    <!-- Footer -->
    <?php
    // Load site settings for dynamic footer content
    if (!isset($siteSettingsModel)) {
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../../models/SiteSettingsModel.php';

        $database = new Database();
        $db = $database->getConnection();
        $siteSettingsModel = new SiteSettingsModel($db);
    }

    $siteSettings = $siteSettingsModel->getSettings();
    $footerAbout = $siteSettingsModel->getFooterAbout();
    $contactInfo = $siteSettingsModel->getContactInfo();
    $socialLinks = $siteSettingsModel->getSocialLinks();
    ?>
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8 mb-8">
                <div class="lg:col-span-2">
                    <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($footerAbout['title']); ?></h3>
                    <p class="text-gray-400"><?php echo htmlspecialchars($footerAbout['text']); ?></p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <?php
                        $quickLinks = $siteSettingsModel->getSetting('quick_links');
                        
                        // Default links if none configured
                        if (empty($quickLinks)) {
                            $quickLinks = [
                                ['label' => 'Home', 'url' => 'index.php'],
                                ['label' => 'About', 'url' => 'index.php?page=about'],
                                ['label' => 'Products', 'url' => 'index.php#products'],
                                ['label' => 'Research', 'url' => 'index.php#research']
                            ];
                        }
                        
                        foreach ($quickLinks as $link):
                            if (!empty($link['label']) && !empty($link['url'])):
                        ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" 
                                   class="text-gray-400 hover:text-white transition duration-200">
                                    <?php echo htmlspecialchars($link['label']); ?>
                                </a>
                            </li>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <?php if (!empty($contactInfo['email'])): ?>
                            <li><i class="fas fa-envelope mr-2"></i> <?php echo htmlspecialchars($contactInfo['email']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['phone'])): ?>
                            <li><i class="fas fa-phone mr-2"></i> <?php echo htmlspecialchars($contactInfo['phone']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['address'])): ?>
                            <li><i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($contactInfo['address']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4 mb-4">
                        <?php if (!empty($socialLinks['facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-facebook"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['instagram']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['youtube'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-linkedin"></i></a>
                        <?php endif; ?>
                    </div>
                    <!-- Simple Visitor Counter -->
                    <div class="text-gray-400 text-sm">
                        <i class="fas fa-eye mr-1"></i>
                        Total Page Views: <span class="font-semibold"><?php echo number_format($visitorStats['total_views'] ?? 0); ?></span>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($footerAbout['title']); ?> Laboratory. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Custom Styles for Shimmer Animation -->
    <style>
        @keyframes shimmer {
            0% {
                transform: translateX(-150%) skewX(-15deg);
            }

            100% {
                transform: translateX(150%) skewX(-15deg);
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }
    </style>

    <!-- Scroll to Top Button (Liquid Glass Style - Transparent) -->
    <button id="scrollToTop"
        class="fixed bottom-8 right-8 w-14 h-14 flex items-center justify-center rounded-full z-50 transition-all duration-500 opacity-0 invisible
                   bg-white/10 backdrop-blur-xl border border-white/20
                   shadow-[0_8px_32px_0_rgba(31,38,135,0.15)]
                   text-blue-600
                   hover:scale-110 hover:-translate-y-1 hover:bg-white/20 hover:shadow-[0_8px_32px_0_rgba(31,38,135,0.25)]
                   hover:border-white/40
                   group overflow-hidden"
        aria-label="Scroll to top">
        <!-- Shine effect -->
        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full group-hover:animate-shimmer z-0"></div>

        <i class="fas fa-arrow-up text-xl relative z-10 group-hover:animate-bounce"></i>
    </button>

    <script src="assets/js/carousel.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/team-slider.js"></script>
    </body>

    </html>