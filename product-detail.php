<?php
session_start();
require_once 'config/database.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: index.php');
    exit();
}

// Fetch product data
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id AND is_active = true");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - InLet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-gray-50">
    <!-- Abstract Background Pattern -->
    <div class="abstract-pattern"></div>

    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

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
                    <!-- Product Image -->
                    <div>
                        <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="w-full h-auto rounded-lg shadow-lg object-cover"
                            onerror="handleImageError(this)">
                    </div>

                    <!-- Product Info -->
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($product['name']); ?></h2>

                        <div class="bg-blue-50 p-6 rounded-lg mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-3">About This Product</h3>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($product['full_description']); ?></p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-bold text-gray-800 mb-3">Product Information</h3>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-blue-600 mr-3"></i>
                                    <span class="text-gray-700">Added: <?php echo date('F d, Y', strtotime($product['created_at'])); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-blue-600 mr-3"></i>
                                    <span class="text-gray-700">Category: Product</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="text-center">
                    <a href="index.php#products" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Products
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>

</html>