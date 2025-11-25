    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8 mb-8">
                <div class="lg:col-span-2">
                    <h3 class="text-2xl font-bold mb-4">InLet</h3>
                    <p class="text-gray-400">Information and Learning Engineering Technology Laboratory</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="index.php?page=about" class="text-gray-400 hover:text-white">About</a></li>
                        <li><a href="index.php#products" class="text-gray-400 hover:text-white">Products</a></li>
                        <li><a href="index.php#research" class="text-gray-400 hover:text-white">Research</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i> info@inlet.com</li>
                        <li><i class="fas fa-phone mr-2"></i> +62 123 456 7890</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Malang, East Java, Indonesia</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4 mb-4">
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                    <!-- Simple Visitor Counter -->
                    <div class="text-gray-400 text-sm">
                        <i class="fas fa-eye mr-1"></i>
                        Total Page Views: <span class="font-semibold"><?php echo number_format($visitorStats['total_views'] ?? 0); ?></span>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 InLet Laboratory. All rights reserved.</p>
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

    <!-- Gallery Modal -->
    <div id="galleryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-90 transition-opacity duration-300" style="display: none; opacity: 0;">
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <!-- Close Button -->
            <button onclick="closeGalleryModal()"
                class="absolute top-4 right-4 text-white hover:text-gray-300 text-4xl font-bold z-50 transition-colors"
                aria-label="Close gallery">
                <i class="fas fa-times"></i>
            </button>

            <!-- Previous Button -->
            <button onclick="previousGalleryImage()"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-30 hover:bg-opacity-50 text-white p-4 rounded-full z-50 transition-all"
                aria-label="Previous image">
                <i class="fas fa-chevron-left text-2xl"></i>
            </button>

            <!-- Image Container -->
            <div class="max-w-5xl max-h-full flex flex-col items-center">
                <img id="galleryModalImage"
                    src=""
                    alt=""
                    class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl">
                <p id="galleryModalCaption"
                    class="text-white text-xl mt-4 text-center font-medium"></p>
                <p id="galleryCounter"
                    class="text-white text-sm mt-2 bg-black bg-opacity-50 px-3 py-1 rounded-full"></p>
            </div>

            <!-- Next Button -->
            <button onclick="nextGalleryImage()"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-30 hover:bg-opacity-50 text-white p-4 rounded-full z-50 transition-all"
                aria-label="Next image">
                <i class="fas fa-chevron-right text-2xl"></i>
            </button>
        </div>
    </div>

    <script src="assets/js/carousel.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/team-slider.js"></script>
    </body>

    </html>