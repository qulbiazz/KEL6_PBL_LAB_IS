    <!-- Carousel Banner Section -->
    <section class="relative h-screen overflow-y-hidden -mt-16 md:-mt-20">
        <div class="carousel-container relative w-full h-full">
            <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?> absolute w-full h-full transition-opacity duration-500" style="background-image: url('uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>')">
                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                    <div class="relative z-10 container mx-auto px-6 md:px-8 lg:px-24 h-full flex items-center pt-16 md:pt-20">
                        <div class="text-white max-w-2xl">
                            <h1 class="text-3xl md:text-5xl font-bold mb-3 md:mb-4"><?php echo htmlspecialchars($banner['title']); ?></h1>
                            <p class="text-base md:text-xl mb-6 md:mb-8"><?php echo htmlspecialchars($banner['description']); ?></p>
                            <?php if ($banner['button_text'] && $banner['button_link']): ?>
                                <a href="<?php echo htmlspecialchars($banner['button_link']); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg inline-block transition">
                                    <?php echo htmlspecialchars($banner['button_text']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Carousel Controls -->
            <button class="carousel-prev absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 text-gray-800 p-3 rounded-full z-20">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-next absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 text-gray-800 p-3 rounded-full z-20">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </section>

    <!-- Card Data Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($cards as $card): ?>
                    <div class="text-center p-6 rounded-lg hover:shadow-lg transition">
                        <div class="text-5xl mb-4">
                            <i class="<?php echo htmlspecialchars($card['icon']); ?> text-blue-600"></i>
                        </div>
                        <h3 class="text-3xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($card['value']); ?></h3>
                        <p class="text-gray-600"><?php echo htmlspecialchars($card['label']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Products</h2>
                <p class="text-gray-600">Innovative solutions for modern challenges</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition flex flex-col h-full">
                        <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-600 mb-4 flex-grow"><?php echo htmlspecialchars($product['description']); ?></p>
                            <a href="index.php?page=product-detail&id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-700 font-semibold mt-auto">
                                Learn More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Partners</h2>
                <p class="text-gray-600">Trusted by leading organizations</p>
            </div>
            <div class="flex flex-wrap justify-center items-center gap-8">
                <?php foreach ($partners as $partner): ?>
                    <div class="flex items-center justify-center p-4">
                        <img src="uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>"
                            alt="<?php echo htmlspecialchars($partner['name']); ?>"
                            class="max-h-20 w-auto grayscale hover:grayscale-0 transition"
                            onerror="handleImageError(this)">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Team</h2>
                <p class="text-gray-600">Meet the experts behind InLet</p>
            </div>

            <!-- Team Slider -->
            <div class="relative">
                <div class="swiper teamSwiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($team as $member): ?>
                            <div class="swiper-slide">
                                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition h-full flex flex-col">
                                    <!-- Profile Image -->
                                    <div class="relative overflow-hidden">
                                        <img src="uploads/team/<?php echo htmlspecialchars($member['photo']); ?>"
                                            alt="<?php echo htmlspecialchars($member['name']); ?>"
                                            class="w-full h-64 object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-300">
                                            <div class="absolute bottom-4 left-0 right-0 text-center">
                                                <a href="index.php?page=lecturer-detail&id=<?php echo $member['id']; ?>"
                                                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                                                    View Profile
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Member Info -->
                                    <div class="p-6 text-center flex-1 flex flex-col justify-between">
                                        <div>
                                            <h3 class="text-xl font-bold mb-1 text-gray-800">
                                                <?php echo htmlspecialchars($member['name']); ?>
                                            </h3>
                                            <p class="text-blue-600 mb-3">
                                                <?php echo htmlspecialchars($member['position']); ?>
                                            </p>
                                        </div>

                                        <div class="flex justify-center space-x-3 mt-4">
                                            <a href="index.php?page=lecturer-detail&id=<?php echo $member['id']; ?>"
                                                class="text-gray-600 hover:text-blue-600 transition">
                                                <i class="fas fa-user text-xl"></i>
                                            </a>
                                            <?php if ($member['linkedin']): ?>
                                                <a href="<?php echo htmlspecialchars($member['linkedin']); ?>"
                                                    target="_blank"
                                                    class="text-gray-600 hover:text-blue-600 transition">
                                                    <i class="fab fa-linkedin text-xl"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($member['email']): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>"
                                                    class="text-gray-600 hover:text-red-600 transition">
                                                    <i class="fas fa-envelope text-xl"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="swiper-button-next team-button-next"></div>
                    <div class="swiper-button-prev team-button-prev"></div>

                    <!-- Pagination -->
                    <div class="swiper-pagination team-pagination"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Latest News</h2>
                <p class="text-gray-600">Stay updated with our latest activities</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($news as $item): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition flex flex-col h-full">
                        <img src="uploads/news/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-6 flex flex-col flex-grow">
                            <div class="text-sm text-gray-500 mb-2">
                                <i class="far fa-calendar"></i> <?php echo date('F d, Y', strtotime($item['published_at'])); ?>
                            </div>
                            <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="text-gray-600 mb-4 flex-grow"><?php echo substr(htmlspecialchars($item['content']), 0, 100); ?>...</p>
                            <a href="index.php?page=news-detail&id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-700 font-semibold mt-auto">
                                Read More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Gallery</h2>
                <p class="text-gray-600">Moments captured at InLet</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php
                $index = 0;
                foreach ($gallery as $item):
                ?>
                    <div class="relative overflow-hidden rounded-lg group cursor-pointer gallery-item"
                        onclick="openGalleryModal('<?php echo addslashes(htmlspecialchars($item['image'])); ?>', '<?php echo addslashes(htmlspecialchars($item['caption'])); ?>', <?php echo $index; ?>)">
                        <img src="uploads/gallery/<?php echo htmlspecialchars($item['image']); ?>"
                            alt="<?php echo htmlspecialchars($item['caption']); ?>"
                            class="w-full h-64 object-cover group-hover:scale-110 transition duration-300"
                            onerror="handleImageError(this)">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-70 transition flex flex-col items-center justify-center p-4">
                            <h3 class="text-white text-center text-sm opacity-0 group-hover:opacity-100 transition font-semibold">
                                <?php echo htmlspecialchars($item['caption']); ?>
                            </h3>
                        </div>
                    </div>
                <?php
                    $index++;
                endforeach;
                ?>
            </div>
        </div>
    </section>

    <!-- Research Activity Section -->
    <section id="research" class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Research Activities</h2>
                <p class="text-gray-600">Exploring new frontiers in technology</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($research as $activity): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition flex flex-col h-full">
                        <img src="uploads/research/<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($activity['title']); ?></h3>
                            <p class="text-gray-600 mb-4 flex-grow"><?php echo htmlspecialchars($activity['description']); ?></p>
                            <div class="text-sm text-gray-500 mb-4">
                                <i class="far fa-calendar"></i> <?php echo date('F Y', strtotime($activity['created_at'])); ?>
                            </div>
                            <a href="index.php?page=research-detail&id=<?php echo $activity['id']; ?>" class="text-blue-600 hover:text-blue-700 font-semibold mt-auto">
                                Read More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>