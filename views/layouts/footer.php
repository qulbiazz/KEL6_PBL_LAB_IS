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


    <script src="assets/js/carousel.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/team-slider.js"></script>
    </body>

    </html>