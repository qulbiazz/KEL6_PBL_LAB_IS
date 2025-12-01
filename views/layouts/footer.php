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
    <footer class="bg-gray-800 text-white py-12" itemscope itemtype="https://schema.org/WPFooter" role="contentinfo">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8 mb-8">
                <div class="lg:col-span-2" itemscope itemtype="https://schema.org/Organization">
                    <h3 class="text-2xl font-bold mb-4" itemprop="name"><?php echo htmlspecialchars($footerAbout['title']); ?></h3>
                    <p class="text-gray-400" itemprop="description"><?php echo htmlspecialchars($footerAbout['text']); ?></p>
                    <meta itemprop="url" content="https://pbl-inlet.alwaysdata.net/inlet-web/">
                </div>
                <nav aria-label="Footer quick links" class="bg-transparent">
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 list-none p-0 m-0 bg-transparent">
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
                </nav>
                <address class="not-italic" itemscope itemtype="https://schema.org/PostalAddress">
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <?php if (!empty($contactInfo['email'])): ?>
                            <li>
                                <i class="fas fa-envelope mr-2" aria-hidden="true"></i>
                                <a href="mailto:<?php echo htmlspecialchars($contactInfo['email']); ?>" class="hover:text-white transition" itemprop="email">
                                    <?php echo htmlspecialchars($contactInfo['email']); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['phone'])): ?>
                            <li>
                                <i class="fas fa-phone mr-2" aria-hidden="true"></i>
                                <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $contactInfo['phone'])); ?>" class="hover:text-white transition" itemprop="telephone">
                                    <?php echo htmlspecialchars($contactInfo['phone']); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['address'])): ?>
                            <li itemprop="streetAddress"><i class="fas fa-map-marker-alt mr-2" aria-hidden="true"></i> <?php echo htmlspecialchars($contactInfo['address']); ?></li>
                        <?php endif; ?>
                    </ul>
                </address>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <nav class="flex space-x-2 mb-4" aria-label="Social media links">
                        <?php if (!empty($socialLinks['facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl p-2 min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Kunjungi Facebook INLET">
                                <i class="fab fa-facebook" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl p-2 min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Kunjungi Twitter INLET">
                                <i class="fab fa-twitter" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['instagram']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl p-2 min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Kunjungi Instagram INLET">
                                <i class="fab fa-instagram" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['youtube'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl p-2 min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Kunjungi YouTube INLET">
                                <i class="fab fa-youtube" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinks['linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white text-2xl p-2 min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Kunjungi LinkedIn INLET">
                                <i class="fab fa-linkedin" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                    <!-- Simple Visitor Counter -->
                    <div class="text-gray-400 text-sm">
                        <i class="fas fa-eye mr-1" aria-hidden="true"></i>
                        Total Page Views: <span class="font-semibold"><?php echo number_format($visitorStats['total_views'] ?? 0); ?></span>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <?php 
                $copyrightText = '';
                if (!empty($siteSettings['footer_copyright'])) {
                    $copyrightText = $siteSettings['footer_copyright'];
                } else {
                    $copyrightText = '© ' . date('Y') . ' ' . $footerAbout['title'] . ' Laboratory. All rights reserved.';
                }
                ?>
                <p><?php echo htmlspecialchars($copyrightText); ?></p>
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