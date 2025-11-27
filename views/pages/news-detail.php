    <!-- Abstract Background Pattern -->
    <div class="abstract-pattern"></div>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16 md:py-20">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-6 leading-tight"><?php echo htmlspecialchars($news['title']); ?></h1>
                <div class="flex flex-wrap items-center justify-center gap-4 text-base md:text-lg">
                    <div class="flex items-center bg-white/10 px-4 py-2 rounded-full backdrop-blur-sm">
                        <i class="fas fa-user-edit mr-2"></i>
                        <span><?php echo htmlspecialchars($news['author'] ?? 'InLet Lab'); ?></span>
                    </div>
                    <span class="hidden md:inline">•</span>
                    <div class="flex items-center bg-white/10 px-4 py-2 rounded-full backdrop-blur-sm">
                        <i class="far fa-calendar mr-2"></i>
                        <span><?php echo date('F d, Y', strtotime($news['published_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Content -->
    <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="max-w-4xl mx-auto">
                <!-- Featured Image -->
                <div class="mb-8 rounded-xl overflow-hidden shadow-2xl">
                    <img src="uploads/news/<?php echo htmlspecialchars($news['image']); ?>"
                        alt="<?php echo htmlspecialchars($news['title']); ?>"
                        class="w-full h-auto object-cover max-h-96 md:max-h-[500px]"
                        onerror="handleImageError(this)">
                </div>

                <!-- Content Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mb-8 border border-gray-100">
                    <div class="prose prose-lg max-w-none">
                        <div class="text-gray-700 leading-relaxed text-base md:text-lg">
                            <h1 class="text-3xl font-bold mb-3">
                                <?php echo htmlspecialchars($news['title']); ?>
                            </h1>
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

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-md p-8 mb-8 border border-blue-100">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                        News Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Published Date</p>
                                <p class="text-gray-800 font-semibold"><?php echo date('F d, Y', strtotime($news['published_at'])); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user-edit text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Author</p>
                                <p class="text-gray-800 font-semibold"><?php echo htmlspecialchars($news['author'] ?? 'InLet Lab'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Another News Section -->
                <?php if (!empty($otherNews)): ?>
                <div class="bg-white rounded-xl shadow-md p-8 mb-8 border border-gray-100">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-newspaper text-blue-600 mr-3"></i>
                        Another News You Might Like
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($otherNews as $item): ?>
                            <a href="index.php?page=news-detail&id=<?php echo $item['id']; ?>" 
                               class="group flex flex-col bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-300 h-full">
                                <div class="relative overflow-hidden h-48 flex-shrink-0">
                                    <img src="uploads/news/<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                         onerror="this.src='assets/images/default-news.jpg'">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                                <div class="p-4 flex flex-col flex-grow">
                                    <h4 class="text-lg font-bold text-gray-800 group-hover:text-blue-600 transition-colors mb-2 line-clamp-2 min-h-[3.5rem]">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h4>
                                    <div class="flex items-center text-sm text-gray-500 mb-3">
                                        <i class="far fa-calendar mr-2"></i>
                                        <span><?php echo date('M d, Y', strtotime($item['published_at'])); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between mt-auto">
                                        <span class="text-sm text-gray-600 truncate mr-2">
                                            <i class="fas fa-user mr-1"></i>
                                            <?php echo htmlspecialchars($item['author'] ?? 'InLet Lab'); ?>
                                        </span>
                                        <span class="text-blue-600 font-semibold group-hover:translate-x-1 transition-transform inline-flex items-center whitespace-nowrap">
                                            Read More
                                            <i class="fas fa-arrow-right ml-1 text-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Back Button -->
                <div class="text-center mt-8">
                    <a href="index.php#news" class="inline-flex items-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 group">
                        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                        <span class="font-semibold">Back to All News</span>
                    </a>
                </div>
            </div>
        </div>
    </section>