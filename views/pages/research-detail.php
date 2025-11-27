    <!-- Abstract Background Pattern -->
    <div class="abstract-pattern"></div>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-4"><?php echo htmlspecialchars($research['title']); ?></h1>
            </div>
        </div>
    </section>

    <!-- Research Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <img src="uploads/research/<?php echo htmlspecialchars($research['image']); ?>"
                        alt="<?php echo htmlspecialchars($research['title']); ?>"
                        class="w-full h-auto rounded-lg shadow-lg object-cover max-h-96"
                        onerror="handleImageError(this)">
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-3">About this research</h3>
                    <div class="prose max-w-none">
                        <div class="text-gray-700 leading-relaxed text-lg">
                            <?php
                            $content = trim($research['full_description'] ?? '');
                            if ($content === '') {
                                echo '<p class="text-gray-500 italic">Sorry! There is no content available right now.</p>';
                            } else {
                                echo nl2br(htmlspecialchars($content));
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Another Research Section -->
                <?php if (!empty($otherResearch)): ?>
                <div class="bg-white rounded-xl shadow-md p-8 mb-8 border border-gray-100">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-flask text-blue-600 mr-3"></i>
                        Another Research You Might Like
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($otherResearch as $item): ?>
                            <a href="index.php?page=research-detail&id=<?php echo $item['id']; ?>" 
                               class="group flex flex-col bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-300 h-full">
                                <div class="relative overflow-hidden h-48 flex-shrink-0">
                                    <img src="uploads/research/<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                         onerror="this.src='assets/images/default-research.jpg'">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                                <div class="p-4 flex flex-col flex-grow">
                                    <h4 class="text-lg font-bold text-gray-800 group-hover:text-blue-600 transition-colors mb-2 line-clamp-2 min-h-[3.5rem]">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                        <?php echo htmlspecialchars($item['description'] ?? 'No description available.'); ?>
                                    </p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <span class="text-sm text-gray-500">
                                            <i class="far fa-calendar mr-1"></i>
                                            <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                        </span>
                                        <span class="text-blue-600 font-semibold group-hover:translate-x-1 transition-transform inline-flex items-center whitespace-nowrap">
                                            Learn More
                                            <i class="fas fa-arrow-right ml-1 text-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!--  -->

                <div class="text-center">
                    <a href="index.php#research" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Research
                    </a>
                </div>
            </div>
        </div>
    </section>