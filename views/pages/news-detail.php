    <!-- Abstract Background Pattern -->
    <div class="abstract-pattern"></div>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-4"><?php echo htmlspecialchars($news['title']); ?></h1>
                <div class="flex items-center justify-center text-lg">
                    <i class="fas fa-newspaper mr-2"></i>
                    <span>News Details</span>
                    <span class="mx-3">•</span>
                    <i class="far fa-calendar mr-2"></i>
                    <span><?php echo date('F Y', strtotime($news['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- News Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <img src="uploads/news/<?php echo htmlspecialchars($news['image']); ?>"
                        alt="<?php echo htmlspecialchars($news['title']); ?>"
                        class="w-full h-auto rounded-lg shadow-lg object-cover max-h-96"
                        onerror="handleImageError(this)">
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <div class="prose max-w-none">
                        <div class="text-gray-700 leading-relaxed whitespace-pre-line text-lg">
                            <?php
                            $content = trim($news['content'] ?? '');
                            if ($content === '') {
                                echo '<p class="text-gray-500 italic">Sorry! There is no content available right now.</p>';
                            } else {
                                echo nl2br(htmlspecialchars($content));
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">News Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-blue-600 mr-3"></i>
                            <span class="text-gray-700">Published: <?php echo date('F d, Y', strtotime($news['published_at'])); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-blue-600 mr-3"></i>
                            <span class="text-gray-700">Updated: <?php echo date('F d, Y', strtotime($news['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="index.php#news" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to News
                    </a>
                </div>
            </div>
        </div>
    </section>