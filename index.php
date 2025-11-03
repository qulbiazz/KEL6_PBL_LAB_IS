<?php
session_start();
require_once 'config/database.php';

// Fetch data for homepage
$db = new Database();
$conn = $db->getConnection();

// Get carousel banners
$banners = $conn->query("SELECT * FROM carousel_banners WHERE is_active = true ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);

// Get card data
$cards = $conn->query("SELECT * FROM card_data WHERE is_active = true ORDER BY display_order LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

// Get products
$products = $conn->query("SELECT * FROM products WHERE is_active = true ORDER BY created_at DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

// Get partners
$partners = $conn->query("SELECT * FROM partners WHERE is_active = true ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);

// Get team members
$team = $conn->query("SELECT * FROM team_members WHERE is_active = true ORDER BY display_order LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

// Get news
$news = $conn->query("SELECT * FROM news WHERE is_active = true ORDER BY published_at DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

// Get gallery
$gallery = $conn->query("SELECT * FROM gallery WHERE is_active = true ORDER BY created_at DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

// Get research activities
$research = $conn->query("SELECT * FROM research_activities WHERE is_active = true ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InLet - Information and Learning Engineering Technology</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 @container mx-auto">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Carousel Banner Section -->
    <section class="relative h-screen">
        <div class="carousel-container relative w-full h-full overflow-hidden">
            <?php foreach ($banners as $index => $banner): ?>
            <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?> absolute w-full h-full transition-opacity duration-500" style="background-image: url('uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>')">
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                <div class="relative z-10 container mx-auto px-6 md:px-8 lg:px-24 h-full flex items-center">
                    <div class="text-white max-w-2xl">
                        <h1 class="text-5xl font-bold mb-4"><?php echo htmlspecialchars($banner['title']); ?></h1>
                        <p class="text-xl mb-8"><?php echo htmlspecialchars($banner['description']); ?></p>
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
                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition">
                    <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-700 font-semibold">
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
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 items-center">
                <?php foreach ($partners as $partner): ?>
                <div class="flex items-center justify-center p-4">
                    <img src="uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>" alt="<?php echo htmlspecialchars($partner['name']); ?>" class="max-h-20 w-auto grayscale hover:grayscale-0 transition">
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
                            <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition text-center h-full">
                                <img src="uploads/team/<?php echo htmlspecialchars($member['photo']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-full h-64 object-cover">
                                <div class="p-6">
                                    <h3 class="text-xl font-bold mb-1 text-gray-800"><?php echo htmlspecialchars($member['name']); ?></h3>
                                    <p class="text-blue-600 mb-3"><?php echo htmlspecialchars($member['position']); ?></p>
                                    <div class="flex justify-center space-x-3">
                                        <?php if ($member['linkedin']): ?>
                                        <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" target="_blank" class="text-gray-600 hover:text-blue-600 transition">
                                            <i class="fab fa-linkedin text-xl"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($member['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="text-gray-600 hover:text-blue-600 transition">
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
                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition">
                    <img src="uploads/news/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">
                            <i class="far fa-calendar"></i> <?php echo date('F d, Y', strtotime($item['published_at'])); ?>
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo substr(htmlspecialchars($item['content']), 0, 100); ?>...</p>
                        <a href="news-detail.php?id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-700 font-semibold">
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
                <?php foreach ($gallery as $item): ?>
                <div class="relative overflow-hidden rounded-lg group cursor-pointer" onclick="openGalleryModal('<?php echo htmlspecialchars($item['image']); ?>', '<?php echo htmlspecialchars($item['caption']); ?>')">
                    <img src="uploads/gallery/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['caption']); ?>" class="w-full h-64 object-cover group-hover:scale-110 transition duration-300">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition flex items-center justify-center">
                        <i class="fas fa-search-plus text-white text-3xl opacity-0 group-hover:opacity-100 transition"></i>
                    </div>
                </div>
                <?php endforeach; ?>
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
                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition">
                    <img src="uploads/research/<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($activity['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($activity['description']); ?></p>
                        <div class="text-sm text-gray-500">
                            <i class="far fa-calendar"></i> <?php echo date('F Y', strtotime($activity['created_at'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Gallery Modal -->
    <div id="galleryModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div class="relative max-w-4xl w-full">
            <button onclick="closeGalleryModal()" class="absolute top-4 right-4 text-white text-3xl z-10">
                <i class="fas fa-times"></i>
            </button>
            <img id="galleryModalImage" src="" alt="" class="w-full h-auto rounded-lg">
            <p id="galleryModalCaption" class="text-white text-center mt-4"></p>
        </div>
    </div>

    <script src="assets/js/carousel.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/team-slider.js"></script>
</body>
</html>
