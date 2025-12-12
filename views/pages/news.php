<!-- News Page -->
<main class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 py-16">
        <div class="container mx-auto px-6 md:px-8 lg:px-12 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">News & Updates</h1>
            <p class="text-blue-100 text-lg max-w-2xl mx-auto">Stay updated with the latest activities and announcements from INLET Lab</p>
            <nav class="mt-6 text-blue-200" aria-label="Breadcrumb">
                <a href="index.php" class="hover:text-white transition">Home</a>
                <span class="mx-2">/</span>
                <span class="text-white">News</span>
            </nav>
        </div>
    </section>

    <!-- News Grid -->
    <section class="py-16">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <?php if (empty($allNews)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-newspaper text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-600 mb-2">No News Available</h2>
                    <p class="text-gray-500">Check back later for the latest updates.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($allNews as $item): ?>
                        <article class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 flex flex-col h-full border border-gray-100" itemscope itemtype="https://schema.org/NewsArticle">
                            <div class="relative overflow-hidden group">
                                <img src="uploads/news/<?php echo htmlspecialchars($item['image']); ?>"
                                    alt="Berita: <?php echo htmlspecialchars($item['title']); ?>"
                                    loading="lazy"
                                    class="w-full h-56 object-cover transform group-hover:scale-110 transition-transform duration-500"
                                    itemprop="image">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="far fa-calendar mr-2"></i>
                                    <time datetime="<?php echo date('Y-m-d', strtotime($item['published_at'])); ?>" itemprop="datePublished">
                                        <?php echo date('F d, Y', strtotime($item['published_at'])); ?>
                                    </time>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2" itemprop="headline"><?php echo htmlspecialchars($item['title']); ?></h2>
                                <p class="text-gray-600 mb-4 flex-grow line-clamp-3" itemprop="description"><?php echo substr(htmlspecialchars($item['content']), 0, 150); ?>...</p>
                                <meta itemprop="author" content="INLET Politeknik Negeri Malang">
                                <a href="index.php?page=news-detail&id=<?php echo $item['id']; ?>"
                                    class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold mt-auto group/link"
                                    itemprop="url">
                                    Read More
                                    <i class="fas fa-arrow-right ml-2 transform group-hover/link:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>