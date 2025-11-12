<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get visitor statistics
require_once '../models/VisitorModel.php';
$visitorModel = new VisitorModel($conn);
$visitorStats = $visitorModel->getVisitorStats();

// Get statistics
$stats = [];

// Total content counts
$stats['banners'] = $conn->query("SELECT COUNT(*) FROM carousel_banners")->fetchColumn();
$stats['products'] = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$stats['partners'] = $conn->query("SELECT COUNT(*) FROM partners")->fetchColumn();
$stats['team'] = $conn->query("SELECT COUNT(*) FROM team_members")->fetchColumn();
$stats['news'] = $conn->query("SELECT COUNT(*) FROM news")->fetchColumn();
$stats['gallery'] = $conn->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
$stats['research'] = $conn->query("SELECT COUNT(*) FROM research_activities")->fetchColumn();

// Recent news
$recent_news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="flex-1 p-4 md:p-8 pt-24 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8 mt-16">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600 text-sm md:text-base">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Carousel Banners</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['banners']; ?></h3>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <i class="fas fa-images text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Products</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['products']; ?></h3>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg">
                            <i class="fas fa-box text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Team Members</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['team']; ?></h3>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">News Articles</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['news']; ?></h3>
                        </div>
                        <div class="bg-orange-100 p-4 rounded-lg">
                            <i class="fas fa-newspaper text-orange-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Visitor Statistics -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                        Visitor Statistics
                    </h2>
                    
                    <!-- Main Stats Grid -->
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-3xl font-bold text-blue-600">
                                <?php echo number_format($visitorStats['total'] ?? 0); ?>
                            </div>
                            <div class="text-xs text-gray-600 mt-1">Total Visitors</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-3xl font-bold text-green-600">
                                <?php echo number_format($visitorStats['today'] ?? 0); ?>
                            </div>
                            <div class="text-xs text-gray-600 mt-1">Today</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-3xl font-bold text-yellow-600">
                                <?php echo number_format($visitorStats['online'] ?? 0); ?>
                            </div>
                            <div class="text-xs text-gray-600 mt-1">Online Now</div>
                        </div>
                    </div>
                    
                    <!-- Page Views -->
                    <div class="text-center mb-6 pb-4 border-b">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-eye mr-1"></i>
                            Total Page Views: <span class="font-semibold text-gray-800"><?php echo number_format($visitorStats['total_views'] ?? 0); ?></span>
                        </div>
                    </div>
                    
                    <!-- Top Countries -->
                    <?php if (!empty($visitorStats['countries'])): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Top Countries</h3>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <?php foreach ($visitorStats['countries'] as $country): ?>
                            <div class="flex items-center justify-between bg-gray-50 rounded px-3 py-2 hover:bg-gray-100 transition">
                                <div class="flex items-center space-x-2 flex-1">
                                    <span class="text-xl">
                                        <?php echo VisitorModel::getCountryFlag($country['country_code']); ?>
                                    </span>
                                    <span class="text-sm text-gray-700 truncate">
                                        <?php echo htmlspecialchars($country['country']); ?>
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="text-xs font-semibold text-blue-600">
                                        <?php echo number_format($country['count']); ?>
                                    </div>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <?php 
                                        $maxCount = $visitorStats['countries'][0]['count'] ?? 1;
                                        $percentage = ($country['count'] / $maxCount) * 100;
                                        ?>
                                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" 
                                             style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent News -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Recent News</h2>
                    <div class="space-y-4">
                        <?php foreach ($recent_news as $news): ?>
                        <div class="border-b pb-3">
                            <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($news['title']); ?></h3>
                            <p class="text-sm text-gray-600">
                                <i class="far fa-calendar mr-1"></i>
                                <?php echo date('M d, Y', strtotime($news['created_at'])); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
