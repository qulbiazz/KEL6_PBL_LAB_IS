    <!-- Hero Banner Section with SEO optimized content -->
    <section class="relative h-screen overflow-y-hidden -mt-16 md:-mt-20" aria-label="Banner utama INLET">
        <div class="carousel-container relative w-full h-full" role="region" aria-roledescription="carousel">
            <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?> absolute w-full h-full transition-opacity duration-500" 
                     style="background-image: url('uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>')"
                     role="group" 
                     aria-roledescription="slide"
                     aria-label="Slide <?php echo $index + 1; ?> of <?php echo count($banners); ?>">
                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                    <div class="relative z-10 container mx-auto px-6 md:px-8 lg:px-24 h-full flex items-center pt-16 md:pt-20">
                        <div class="text-white max-w-2xl banner-content">
                            <h1 class="text-3xl md:text-5xl font-bold mb-3 md:mb-4 banner-title opacity-0">
                                <span class="typing-text" data-text="<?php echo htmlspecialchars($banner['title']); ?>"></span>
                                <span class="typing-cursor" aria-hidden="true">|</span>
                            </h1>
                            <p class="text-base md:text-xl mb-6 md:mb-8 banner-description opacity-0"><?php echo htmlspecialchars($banner['description']); ?></p>
                            <?php if ($banner['button_text'] && $banner['button_link']): ?>
                                <a href="<?php echo htmlspecialchars($banner['button_link']); ?>" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg inline-block transition banner-button opacity-0 transform translate-y-4"
                                   role="button">
                                    <?php echo htmlspecialchars($banner['button_text']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Carousel Controls with accessibility -->
            <button class="carousel-prev absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 text-gray-800 p-4 min-w-[48px] min-h-[48px] rounded-full z-20 flex items-center justify-center" aria-label="Slide sebelumnya">
                <i class="fas fa-chevron-left text-lg" aria-hidden="true"></i>
            </button>
            <button class="carousel-next absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 text-gray-800 p-4 min-w-[48px] min-h-[48px] rounded-full z-20 flex items-center justify-center" aria-label="Slide selanjutnya">
                <i class="fas fa-chevron-right text-lg" aria-hidden="true"></i>
            </button>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-20 bg-white" aria-label="Statistik INLET">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($cards as $card): ?>
                    <article class="text-center p-6 rounded-lg hover:shadow-lg transition">
                        <div class="text-5xl mb-4" aria-hidden="true">
                            <i class="<?php echo htmlspecialchars($card['icon']); ?> text-blue-600"></i>
                        </div>
                        <h3 class="text-3xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($card['value']); ?></h3>
                        <p class="text-gray-600"><?php echo htmlspecialchars($card['label']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-20 bg-gray-50" aria-labelledby="products-heading">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <header class="text-center mb-12">
                <h2 id="products-heading" class="text-4xl font-bold text-gray-800 mb-4">Our Products</h2>
                <p class="text-gray-600">Innovative solutions for modern challenges</p>
            </header>
            <?php if (count($products) > 3): ?>
                <!-- Splide Slider for more than 3 items -->
                <div class="splide products-slider" role="region" aria-label="Products carousel">
                    <div class="splide__track">
                        <ul class="splide__list" role="list">
                            <?php foreach ($products as $product): ?>
                                <li class="splide__slide" role="listitem">
                                    <article class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 flex flex-col h-full border border-gray-100">
                                        <div class="relative overflow-hidden group">
                                            <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                                                alt="Produk <?php echo htmlspecialchars($product['name']); ?> - INLET Polinema"
                                                loading="lazy"
                                                decoding="async"
                                                width="400"
                                                height="192"
                                                class="w-full h-48 object-cover transform group-hover:scale-110 transition-transform duration-500">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        </div>
                                        <div class="p-6 flex flex-col flex-grow">
                                            <h3 class="text-xl font-bold mb-3 text-gray-800 line-clamp-2 min-h-[3.5rem]"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="text-gray-600 mb-4 flex-grow line-clamp-3"><?php echo htmlspecialchars($product['description']); ?></p>
                                            <a href="index.php?page=product-detail&id=<?php echo $product['id']; ?>"
                                                class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold mt-auto group/link"
                                                aria-label="Pelajari lebih lanjut tentang <?php echo htmlspecialchars($product['name']); ?>">
                                                Learn More
                                                <i class="fas fa-arrow-right ml-2 transform group-hover/link:translate-x-1 transition-transform" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </article>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grid for 3 or fewer items -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($products as $product): ?>
                        <article class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition flex flex-col h-full">
                            <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="Produk <?php echo htmlspecialchars($product['name']); ?> - INLET Polinema" 
                                 loading="lazy"
                                 decoding="async"
                                 width="400"
                                 height="192"
                                 class="w-full h-48 object-cover">
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-gray-600 mb-4 flex-grow"><?php echo htmlspecialchars($product['description']); ?></p>
                                <a href="index.php?page=product-detail&id=<?php echo $product['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-700 font-semibold mt-auto"
                                   aria-label="Pelajari lebih lanjut tentang <?php echo htmlspecialchars($product['name']); ?>">
                                    Learn More <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Partners Section -->
    <section id="partners" class="py-20 bg-white" aria-labelledby="partners-heading" itemscope itemtype="https://schema.org/Organization">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <header class="text-center mb-12">
                <h2 id="partners-heading" class="text-4xl font-bold text-gray-800 mb-4">Our Partners</h2>
                <p class="text-gray-600">Trusted by leading organizations</p>
            </header>
            <div class="flex flex-wrap justify-center items-center gap-8" role="list" aria-label="Partner organizations">
                <?php foreach ($partners as $partner): ?>
                    <figure class="flex items-center justify-center p-4" role="listitem" itemprop="sponsor" itemscope itemtype="https://schema.org/Organization">
                        <img src="uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>"
                            alt="Logo partner: <?php echo htmlspecialchars($partner['name']); ?> - Mitra kerja sama INLET Politeknik Negeri Malang"
                            class="max-h-20 w-auto grayscale hover:grayscale-0 transition"
                            loading="lazy"
                            width="200"
                            height="80"
                            itemprop="logo"
                            onerror="handleImageError(this)">
                        <meta itemprop="name" content="<?php echo htmlspecialchars($partner['name']); ?>">
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="py-20 bg-gray-50" aria-labelledby="team-heading">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <header class="text-center mb-12">
                <h2 id="team-heading" class="text-4xl font-bold text-gray-800 mb-4">Our Team</h2>
                <p class="text-gray-600">Meet the experts behind InLet</p>
            </header>

            <!-- Team Slider -->
            <div class="relative">
                <div class="splide team-slider" role="region" aria-label="Team members carousel">
                    <div class="splide__track">
                        <ul class="splide__list" role="list">
                            <?php foreach ($team as $member): ?>
                                <li class="splide__slide" role="listitem">
                                    <article class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition h-full flex flex-col" itemscope itemtype="https://schema.org/Person">
                                        <!-- Profile Image -->
                                        <div class="relative overflow-hidden">
                                            <img src="uploads/team/<?php echo htmlspecialchars($member['photo']); ?>"
                                                alt="Foto profil <?php echo htmlspecialchars($member['name']); ?> - <?php echo htmlspecialchars($member['position']); ?> di INLET Politeknik Negeri Malang"
                                                class="w-full h-64 object-cover"
                                                loading="lazy"
                                                width="400"
                                                height="256"
                                                itemprop="image">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-300">
                                                <div class="absolute bottom-4 left-0 right-0 text-center">
                                                    <a href="index.php?page=lecturer-detail&id=<?php echo $member['id']; ?>"
                                                        class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                                                        aria-label="Lihat profil lengkap <?php echo htmlspecialchars($member['name']); ?>">
                                                        View Profile
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Member Info -->
                                        <div class="p-6 text-center flex-1 flex flex-col justify-between">
                                            <div>
                                                <h3 class="text-xl font-bold mb-1 text-gray-800" itemprop="name">
                                                    <?php echo htmlspecialchars($member['name']); ?>
                                                </h3>
                                                <p class="text-blue-600 mb-3" itemprop="jobTitle">
                                                    <?php echo htmlspecialchars($member['position']); ?>
                                                </p>
                                                <meta itemprop="worksFor" content="INLET - Politeknik Negeri Malang">
                                            </div>

                                            <nav class="flex justify-center space-x-2 mt-4" aria-label="Social links for <?php echo htmlspecialchars($member['name']); ?>">
                                                <a href="index.php?page=lecturer-detail&id=<?php echo $member['id']; ?>"
                                                    class="text-gray-600 hover:text-blue-600 transition p-2 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                                    aria-label="Profil <?php echo htmlspecialchars($member['name']); ?>">
                                                    <i class="fas fa-user text-xl" aria-hidden="true"></i>
                                                </a>
                                                <?php if ($member['linkedin']): ?>
                                                    <a href="<?php echo htmlspecialchars($member['linkedin']); ?>"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="text-gray-600 hover:text-blue-600 transition p-2 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                                        itemprop="sameAs"
                                                        aria-label="LinkedIn <?php echo htmlspecialchars($member['name']); ?>">
                                                        <i class="fab fa-linkedin text-xl" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($member['email']): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>"
                                                        class="text-gray-600 hover:text-red-600 transition p-2 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                                        itemprop="email"
                                                        aria-label="Email <?php echo htmlspecialchars($member['name']); ?>">
                                                        <i class="fas fa-envelope text-xl" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </nav>
                                        </div>
                                    </article>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="py-20 bg-white" aria-labelledby="news-heading">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <header class="text-center mb-12">
                <h2 id="news-heading" class="text-4xl font-bold text-gray-800 mb-4">Latest News</h2>
                <p class="text-gray-600">Stay updated with our latest activities</p>
            </header>
            <?php if (count($news) > 3): ?>
                <!-- Splide Slider for more than 3 items -->
                <div class="splide news-slider" role="region" aria-label="Latest news carousel">
                    <div class="splide__track">
                        <ul class="splide__list" role="list">
                            <?php foreach ($news as $item): ?>
                                <li class="splide__slide" role="listitem">
                                    <article class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 flex flex-col h-full border border-gray-100" itemscope itemtype="https://schema.org/NewsArticle">
                                        <div class="relative overflow-hidden group">
                                            <img src="uploads/news/<?php echo htmlspecialchars($item['image']); ?>"
                                                alt="Berita: <?php echo htmlspecialchars($item['title']); ?>"
                                                class="w-full h-48 object-cover transform group-hover:scale-110 transition-transform duration-500"
                                                loading="lazy"
                                                width="400"
                                                height="192"
                                                itemprop="image">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        </div>
                                        <div class="p-6 flex flex-col flex-grow">
                                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                                <i class="far fa-calendar mr-2" aria-hidden="true"></i>
                                                <time datetime="<?php echo date('Y-m-d', strtotime($item['published_at'])); ?>" itemprop="datePublished"><?php echo date('F d, Y', strtotime($item['published_at'])); ?></time>
                                            </div>
                                            <h3 class="text-xl font-bold mb-3 text-gray-800 line-clamp-2 min-h-[3.5rem]" itemprop="headline"><?php echo htmlspecialchars($item['title']); ?></h3>
                                            <p class="text-gray-600 mb-4 flex-grow line-clamp-3" itemprop="description"><?php echo substr(htmlspecialchars($item['content']), 0, 120); ?>...</p>
                                            <meta itemprop="author" content="INLET Politeknik Negeri Malang">
                                            <a href="index.php?page=news-detail&id=<?php echo $item['id']; ?>"
                                                class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold mt-auto group/link"
                                                itemprop="url"
                                                aria-label="Baca selengkapnya: <?php echo htmlspecialchars($item['title']); ?>">
                                                Read More
                                                <i class="fas fa-arrow-right ml-2 transform group-hover/link:translate-x-1 transition-transform" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </article>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grid for 3 or fewer items -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" role="list" aria-label="News articles">
                    <?php foreach ($news as $item): ?>
                        <article class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition flex flex-col h-full" role="listitem" itemscope itemtype="https://schema.org/NewsArticle">
                            <img src="uploads/news/<?php echo htmlspecialchars($item['image']); ?>" 
                                alt="Berita: <?php echo htmlspecialchars($item['title']); ?>" 
                                class="w-full h-48 object-cover"
                                loading="lazy"
                                width="400"
                                height="192"
                                itemprop="image">
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="text-sm text-gray-500 mb-2">
                                    <i class="far fa-calendar" aria-hidden="true"></i> 
                                    <time datetime="<?php echo date('Y-m-d', strtotime($item['published_at'])); ?>" itemprop="datePublished"><?php echo date('F d, Y', strtotime($item['published_at'])); ?></time>
                                </div>
                                <h3 class="text-xl font-bold mb-2 text-gray-800" itemprop="headline"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="text-gray-600 mb-4 flex-grow" itemprop="description"><?php echo substr(htmlspecialchars($item['content']), 0, 100); ?>...</p>
                                <meta itemprop="author" content="INLET Politeknik Negeri Malang">
                                <a href="index.php?page=news-detail&id=<?php echo $item['id']; ?>" 
                                    class="text-blue-600 hover:text-blue-700 font-semibold mt-auto"
                                    itemprop="url"
                                    aria-label="Baca selengkapnya: <?php echo htmlspecialchars($item['title']); ?>">
                                    Read More <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-20 bg-gray-50" aria-labelledby="gallery-heading" itemscope itemtype="https://schema.org/ImageGallery">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <header class="text-center mb-12">
                <h2 id="gallery-heading" class="text-4xl font-bold text-gray-800 mb-4" itemprop="name">Gallery</h2>
                <p class="text-gray-600" itemprop="description">Moments captured at InLet</p>
            </header>
            
            <!-- Uniform Grid Layout -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" role="list" aria-label="Photo gallery">
                <?php foreach ($gallery as $index => $item): ?>
                    <figure class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 cursor-pointer aspect-square" role="listitem" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                        <!-- Image -->
                        <img src="uploads/gallery/<?php echo htmlspecialchars($item['image']); ?>"
                            alt="Galeri INLET: <?php echo htmlspecialchars($item['caption']); ?> - Dokumentasi kegiatan Lab INLET Politeknik Negeri Malang"
                            class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-out"
                            loading="lazy"
                            width="400"
                            height="400"
                            itemprop="contentUrl">
                        
                        <!-- Gradient Overlay on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                        
                        <!-- Caption - Show on Hover -->
                        <figcaption class="absolute inset-x-0 bottom-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-[2px] bg-blue-400"></div>
                                <span class="text-blue-400 text-xs font-semibold uppercase tracking-wider">InLet Lab</span>
                            </div>
                            <h3 class="text-white font-bold text-lg leading-tight line-clamp-2" itemprop="caption"><?php echo htmlspecialchars($item['caption']); ?></h3>
                        </figcaption>
                        
                        <!-- Corner Accent -->
                        <div class="absolute top-3 right-3 w-8 h-8 border-t-2 border-r-2 border-white/0 group-hover:border-white/50 transition-all duration-500 rounded-tr-lg"></div>
                        <div class="absolute bottom-3 left-3 w-8 h-8 border-b-2 border-l-2 border-white/0 group-hover:border-white/50 transition-all duration-500 rounded-bl-lg"></div>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Divider -->
    <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>

    <!-- Research Activity Section -->
    <section id="research" class="py-20 bg-white" aria-labelledby="research-heading">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <header class="text-center mb-12">
                <h2 id="research-heading" class="text-4xl font-bold text-gray-800 mb-4">Research Activities</h2>
                <p class="text-gray-600">Exploring new frontiers in technology</p>
            </header>
            <?php if (count($research) > 3): ?>
                <!-- Splide Slider for more than 3 items -->
                <div class="splide research-slider" role="region" aria-label="Research activities carousel">
                    <div class="splide__track">
                        <ul class="splide__list" role="list">
                            <?php foreach ($research as $activity): ?>
                                <li class="splide__slide" role="listitem">
                                    <article class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 flex flex-col h-full border border-gray-100" itemscope itemtype="https://schema.org/ScholarlyArticle">
                                        <div class="relative overflow-hidden group">
                                            <img src="uploads/research/<?php echo htmlspecialchars($activity['image']); ?>"
                                                alt="Riset: <?php echo htmlspecialchars($activity['title']); ?>"
                                                class="w-full h-48 object-cover transform group-hover:scale-110 transition-transform duration-500"
                                                loading="lazy"
                                                width="400"
                                                height="192"
                                                itemprop="image">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        </div>
                                        <div class="p-6 flex flex-col flex-grow">
                                            <h3 class="text-xl font-bold mb-3 text-gray-800 line-clamp-2 min-h-[3.5rem]" itemprop="headline"><?php echo htmlspecialchars($activity['title']); ?></h3>
                                            <p class="text-gray-600 mb-4 flex-grow line-clamp-3" itemprop="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                                <i class="far fa-calendar mr-2" aria-hidden="true"></i>
                                                <time datetime="<?php echo date('Y-m', strtotime($activity['created_at'])); ?>" itemprop="datePublished"><?php echo date('F Y', strtotime($activity['created_at'])); ?></time>
                                            </div>
                                            <meta itemprop="author" content="INLET Politeknik Negeri Malang">
                                            <a href="index.php?page=research-detail&id=<?php echo $activity['id']; ?>"
                                                class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold mt-auto group/link"
                                                itemprop="url"
                                                aria-label="Baca selengkapnya: <?php echo htmlspecialchars($activity['title']); ?>">
                                                Read More
                                                <i class="fas fa-arrow-right ml-2 transform group-hover/link:translate-x-1 transition-transform" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </article>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grid for 3 or fewer items -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8" role="list" aria-label="Research publications">
                    <?php foreach ($research as $activity): ?>
                        <article class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition flex flex-col h-full" role="listitem" itemscope itemtype="https://schema.org/ScholarlyArticle">
                            <img src="uploads/research/<?php echo htmlspecialchars($activity['image']); ?>" 
                                alt="Riset: <?php echo htmlspecialchars($activity['title']); ?>" 
                                class="w-full h-48 object-cover"
                                loading="lazy"
                                width="400"
                                height="192"
                                itemprop="image">
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold mb-2 text-gray-800" itemprop="headline"><?php echo htmlspecialchars($activity['title']); ?></h3>
                                <p class="text-gray-600 mb-4 flex-grow" itemprop="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                                <div class="text-sm text-gray-500 mb-4">
                                    <i class="far fa-calendar" aria-hidden="true"></i> 
                                    <time datetime="<?php echo date('Y-m', strtotime($activity['created_at'])); ?>" itemprop="datePublished"><?php echo date('F Y', strtotime($activity['created_at'])); ?></time>
                                </div>
                                <meta itemprop="author" content="INLET Politeknik Negeri Malang">
                                <a href="index.php?page=research-detail&id=<?php echo $activity['id']; ?>" 
                                    class="text-blue-600 hover:text-blue-700 font-semibold mt-auto"
                                    itemprop="url"
                                    aria-label="Baca selengkapnya: <?php echo htmlspecialchars($activity['title']); ?>">
                                    Read More <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Guestbook Section -->
    <section id="guestbook" class="py-20 bg-gray-50" aria-labelledby="guestbook-heading" itemscope itemtype="https://schema.org/ContactPage">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <header class="text-center mb-12">
                    <h2 id="guestbook-heading" class="text-4xl font-bold text-gray-800 mb-4" itemprop="name">Guestbook</h2>
                    <p class="text-gray-600 text-lg" itemprop="description">Share your thoughts and leave a message for us</p>
                </header>

                <!-- Success Message (Hidden by default) -->
                <div id="guestbookSuccess" class="hidden mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg flex items-start" role="alert" aria-live="polite">
                    <i class="fas fa-check-circle text-2xl mr-3 mt-1" aria-hidden="true"></i>
                    <div>
                        <p class="font-semibold">Thank you for your message!</p>
                        <p class="text-sm">Your entry has been successfully recorded.</p>
                    </div>
                </div>

                <!-- Error Message (Hidden by default) -->
                <div id="guestbookError" class="hidden mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg flex items-start" role="alert" aria-live="assertive">
                    <i class="fas fa-exclamation-circle text-2xl mr-3 mt-1" aria-hidden="true"></i>
                    <div>
                        <p class="font-semibold">Oops! Something went wrong</p>
                        <p class="text-sm" id="errorMessage">Please try again.</p>
                    </div>
                </div>

                <!-- Guestbook Form -->
                <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10 border border-gray-100">
                    <form id="guestbookForm" class="space-y-6" aria-label="Guestbook submission form" novalidate>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name Field -->
                            <div class="form-group">
                                <label for="guestName" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name <span class="text-red-500" aria-hidden="true">*</span>
                                    <span class="sr-only">(required)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400" aria-hidden="true"></i>
                                    </div>
                                    <input type="text"
                                        id="guestName"
                                        name="name"
                                        required
                                        autocomplete="name"
                                        aria-required="true"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter your name">
                                </div>
                            </div>

                            <!-- Institution Field -->
                            <div class="form-group">
                                <label for="guestInstitution" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Institution <span class="text-red-500" aria-hidden="true">*</span>
                                    <span class="sr-only">(required)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-building text-gray-400" aria-hidden="true"></i>
                                    </div>
                                    <input type="text"
                                        id="guestInstitution"
                                        name="institution"
                                        required
                                        autocomplete="organization"
                                        aria-required="true"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Your institution/organization">
                                </div>
                            </div>

                            <!-- Email Field -->
                            <div class="form-group">
                                <label for="guestEmail" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500" aria-hidden="true">*</span>
                                    <span class="sr-only">(required)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400" aria-hidden="true"></i>
                                    </div>
                                    <input type="email"
                                        id="guestEmail"
                                        name="email"
                                        required
                                        autocomplete="email"
                                        aria-required="true"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="your.email@example.com">
                                </div>
                            </div>

                            <!-- Phone Field -->
                            <div class="form-group">
                                <label for="guestPhone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500" aria-hidden="true">*</span>
                                    <span class="sr-only">(required)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400" aria-hidden="true"></i>
                                    </div>
                                    <input type="tel"
                                        id="guestPhone"
                                        name="phone"
                                        required
                                        autocomplete="tel"
                                        aria-required="true"
                                        pattern="[0-9+\-\(\)\s]+"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="081234567890">
                                </div>
                            </div>
                        </div>

                        <!-- Message Field -->
                        <div class="form-group">
                            <label for="guestMessage" class="block text-sm font-semibold text-gray-700 mb-2">
                                Your Message <span class="text-red-500" aria-hidden="true">*</span>
                                <span class="sr-only">(required)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-comment-dots text-gray-400" aria-hidden="true"></i>
                                </div>
                                <textarea id="guestMessage"
                                    name="message"
                                    rows="5"
                                    required
                                    maxlength="1000"
                                    aria-required="true"
                                    aria-describedby="charCount"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                                    placeholder="Share your thoughts, feedback, or questions..."></textarea>
                                <div class="text-right mt-1">
                                    <span id="charCount" class="text-xs text-gray-500" aria-live="polite">0 / 1000</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between pt-4">
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
                                All fields are required
                            </p>
                            <button type="submit"
                                id="submitGuestbook"
                                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                                <i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Guestbook Form Handler
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('guestbookForm');
            const submitBtn = document.getElementById('submitGuestbook');
            const successMsg = document.getElementById('guestbookSuccess');
            const errorMsg = document.getElementById('guestbookError');
            const errorText = document.getElementById('errorMessage');
            const messageInput = document.getElementById('guestMessage');
            const charCount = document.getElementById('charCount');

            // Character counter
            if (messageInput && charCount) {
                messageInput.addEventListener('input', function() {
                    const count = this.value.length;
                    charCount.textContent = count + ' / 1000';
                    if (count > 950) {
                        charCount.classList.add('text-red-500');
                        charCount.classList.remove('text-gray-500');
                    } else {
                        charCount.classList.add('text-gray-500');
                        charCount.classList.remove('text-red-500');
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Hide messages
                    if (successMsg) successMsg.classList.add('hidden');
                    if (errorMsg) errorMsg.classList.add('hidden');

                    // Disable button
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

                    // Get form data
                    const formData = new FormData(form);
                    formData.append('ajax', '1');

                    // Send AJAX request
                    fetch('views/pages/ajax_submit_guestbook.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                if (successMsg) {
                                    successMsg.classList.remove('hidden');
                                    successMsg.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'center'
                                    });
                                }

                                // Reset form
                                form.reset();
                                if (charCount) charCount.textContent = '0 / 1000';

                                // Hide success message after 5 seconds
                                setTimeout(() => {
                                    if (successMsg) successMsg.classList.add('hidden');
                                }, 5000);
                            } else {
                                // Show error message
                                if (errorText) errorText.textContent = data.message || 'Please try again.';
                                if (errorMsg) {
                                    errorMsg.classList.remove('hidden');
                                    errorMsg.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'center'
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (errorText) errorText.textContent = 'Network error. Please check your connection.';
                            if (errorMsg) {
                                errorMsg.classList.remove('hidden');
                                errorMsg.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                        })
                        .finally(() => {
                            // Re-enable button
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send Message';
                        });
                });
            }

            // Input validation and styling
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '' && this.hasAttribute('required')) {
                        this.classList.add('border-red-300');
                        this.classList.remove('border-gray-300');
                    } else {
                        this.classList.remove('border-red-300');
                        this.classList.add('border-gray-300');
                    }
                });

                input.addEventListener('focus', function() {
                    this.classList.remove('border-red-300');
                });
            });
        });
    </script>