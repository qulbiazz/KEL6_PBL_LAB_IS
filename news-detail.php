<?php
session_start();
require_once 'config/database.php';

// Get news ID from URL
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$news_id) {
    header('Location: index.php');
    exit();
}

// Fetch news data
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM news WHERE id = :id AND is_active = true");
$stmt->execute(['id' => $news_id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - InLet</title>
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
                <!-- Featured Image -->
                <div class="mb-8">
                    <img src="uploads/news/<?php echo htmlspecialchars($news['image']); ?>" 
                         alt="<?php echo htmlspecialchars($news['title']); ?>" 
                         class="w-full h-auto rounded-lg shadow-lg object-cover max-h-96"
                         onerror="handleImageError(this)">
                </div>
                
                <!-- News Content -->
                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <div class="prose max-w-none">
                        <div class="text-gray-700 leading-relaxed whitespace-pre-line text-lg">
                            <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Meta Information -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Article Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt text-blue-600 mr-3 text-xl"></i>
                            <div>
                                <div class="text-sm text-gray-600">Published Date</div>
                                <div class="font-semibold text-gray-800">
                                    <?php echo date('F d, Y', strtotime($news['published_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-blue-600 mr-3 text-xl"></i>
                            <div>
                                <div class="text-sm text-gray-600">Last Updated</div>
                                <div class="font-semibold text-gray-800">
                                    <?php echo date('F d, Y', strtotime($news['updated_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Back Button -->
                <div class="text-center">
                    <a href="index.php#news" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to News
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
