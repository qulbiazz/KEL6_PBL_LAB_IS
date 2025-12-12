<!-- Products Page -->
<main class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 py-16">
        <div class="container mx-auto px-6 md:px-8 lg:px-12 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Our Products</h1>
            <p class="text-blue-100 text-lg max-w-2xl mx-auto">Innovative solutions developed by INLET Lab for modern challenges</p>
            <nav class="mt-6 text-blue-200" aria-label="Breadcrumb">
                <a href="index.php" class="hover:text-white transition">Home</a>
                <span class="mx-2">/</span>
                <span class="text-white">Products</span>
            </nav>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="py-16">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <?php if (empty($products)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-600 mb-2">No Products Available</h2>
                    <p class="text-gray-500">Check back later for our latest innovations.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($products as $product): ?>
                        <article class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 flex flex-col h-full border border-gray-100">
                            <div class="relative overflow-hidden group">
                                <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                                    alt="Produk <?php echo htmlspecialchars($product['name']); ?> - INLET Polinema"
                                    loading="lazy"
                                    class="w-full h-56 object-cover transform group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <?php if (!empty($product['year'])): ?>
                                    <div class="absolute top-3 right-3">
                                        <span class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full shadow-lg">
                                            <?php echo htmlspecialchars($product['year']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <h2 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2"><?php echo htmlspecialchars($product['name']); ?></h2>
                                <p class="text-gray-600 mb-4 flex-grow line-clamp-3"><?php echo htmlspecialchars($product['description']); ?></p>
                                <a href="index.php?page=product-detail&id=<?php echo $product['id']; ?>"
                                    class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold mt-auto group/link">
                                    Learn More
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