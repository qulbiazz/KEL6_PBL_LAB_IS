    <!-- Abstract Background Pattern -->
    <div class="abstract-pattern"></div>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="flex items-center justify-center text-lg">
                    <i class="fas fa-box mr-2"></i>
                    <span>Product Details</span>
                    <span class="mx-3">•</span>
                    <i class="far fa-calendar mr-2"></i>
                    <span><?php echo date('F Y', strtotime($product['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Detail Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
                    <div>
                        <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="w-full h-auto rounded-lg shadow-lg object-cover"
                            onerror="handleImageError(this)">
                    </div>

                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($product['name']); ?></h2>

                        <div class="bg-blue-50 p-6 rounded-lg mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-3">About This Product</h3>
                            <p class="text-gray-700 leading-relaxed">
                                <?php
                                $content = trim($product['full_description'] ?? '');
                                if ($content === '') {
                                    echo '<p class="text-gray-500 italic">Sorry! There is no content available right now.</p>';
                                } else {
                                    echo nl2br(htmlspecialchars($content));
                                }
                                ?>
                            </p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-3">Product Information</h3>
                            <div class="space-y-2">
                                <?php if (!empty($product['year'])): ?>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>
                                    <span class="text-gray-700">Year: <strong><?php echo htmlspecialchars($product['year']); ?></strong></span>
                                </div>
                                <?php endif; ?>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-blue-600 mr-3"></i>
                                    <span class="text-gray-700">Added: <?php echo date('F d, Y', strtotime($product['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($product['demo_link'])): ?>
                        <div class="mb-6">
                            <a href="<?php echo htmlspecialchars($product['demo_link']); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="inline-flex items-center justify-center w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                View Live Demo
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center">
                    <a href="index.php#products" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-8 py-3 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Products
                    </a>
                </div>
            </div>
        </div>
    </section>