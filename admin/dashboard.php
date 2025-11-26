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
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="flex-1 p-4 md:p-8 ml-0 lg:ml-64 min-h-screen transition-all duration-300">
            <!-- Welcome Section -->
            <div class="mb-8 mt-3 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h1>
                    <p class="text-blue-100 text-lg max-w-2xl">Here's what's happening with InLet website today. You have <span class="font-bold text-white"><?php echo $visitorStats['today'] ?? 0; ?></span> new visitors!.</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Banners Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 card-hover group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-50 p-3 rounded-lg group-hover:bg-blue-100 transition-colors">
                            <i class="fas fa-images text-blue-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Content</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo $stats['banners']; ?></h3>
                    <p class="text-gray-500 text-sm">Active Banners</p>
                </div>
                
                <!-- Products Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 card-hover group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-emerald-50 p-3 rounded-lg group-hover:bg-emerald-100 transition-colors">
                            <i class="fas fa-box text-emerald-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Data</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo $stats['products']; ?></h3>
                    <p class="text-gray-500 text-sm">Total Products</p>
                </div>
                
                <!-- Team Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 card-hover group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-50 p-3 rounded-lg group-hover:bg-purple-100 transition-colors">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Person</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo $stats['team']; ?></h3>
                    <p class="text-gray-500 text-sm">Team Members</p>
                </div>
                
                <!-- News Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 card-hover group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-orange-50 p-3 rounded-lg group-hover:bg-orange-100 transition-colors">
                            <i class="fas fa-newspaper text-orange-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Content</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo $stats['news']; ?></h3>
                    <p class="text-gray-500 text-sm">News Articles</p>
                </div>
            </div>
            
            <!-- Charts and Tables -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Visitor Statistics (2/3 width on large screens) -->
                <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                            Visitor Analytics
                        </h2>
                        <button class="text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <!-- Main Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100">
                                <div class="text-sm text-blue-600 font-medium mb-1">Total Visitors</div>
                                <div class="text-3xl font-bold text-gray-800">
                                    <?php echo number_format($visitorStats['total_views'] ?? 0); ?>
                                </div>
                                <div class="text-xs text-blue-400 mt-2 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> All time
                                </div>
                            </div>
                            
                            <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100">
                                <div class="text-sm text-emerald-600 font-medium mb-1">Today's Visits</div>
                                <div class="text-3xl font-bold text-gray-800">
                                    <?php echo number_format($visitorStats['today'] ?? 0); ?>
                                </div>
                                <div class="text-xs text-emerald-400 mt-2 flex items-center">
                                    <i class="fas fa-clock mr-1"></i> Since midnight
                                </div>
                            </div>
                            
                            <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100">
                                <div class="text-sm text-amber-600 font-medium mb-1">Online Now</div>
                                <div class="text-3xl font-bold text-gray-800">
                                    <?php echo number_format($visitorStats['online'] ?? 0); ?>
                                </div>
                                <div class="text-xs text-amber-400 mt-2 flex items-center">
                                    <i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i> Live
                                </div>
                            </div>
                        </div>
                        
                        <!-- Top Countries -->
                        <?php if (!empty($visitorStats['countries'])): ?>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Top Locations</h3>
                            <div class="space-y-4">
                                <?php foreach ($visitorStats['countries'] as $country): ?>
                                <div class="group">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-2xl shadow-sm rounded-sm overflow-hidden">
                                                <?php echo VisitorModel::getCountryFlag($country['country_code']); ?>
                                            </span>
                                            <span class="text-sm font-medium text-gray-700">
                                                <?php echo htmlspecialchars($country['country']); ?>
                                            </span>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800"><?php echo number_format($country['count']); ?></span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <?php 
                                        $maxCount = $visitorStats['countries'][0]['count'] ?? 1;
                                        $percentage = ($country['count'] / $maxCount) * 100;
                                        ?>
                                        <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500 group-hover:bg-blue-600" 
                                             style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent News (1/3 width) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800">Recent News</h2>
                        <a href="news.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <?php foreach ($recent_news as $news): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-lg overflow-hidden">
                                    <?php if (!empty($news['image'])): ?>
                                        <img src="../uploads/news/<?php echo htmlspecialchars($news['image']); ?>" class="w-full h-full object-cover" alt="">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 line-clamp-2 mb-1">
                                        <a href="#" class="hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($news['title']); ?></a>
                                    </h3>
                                    <p class="text-xs text-gray-500 flex items-center">
                                        <i class="far fa-clock mr-1"></i>
                                        <?php echo date('M d, Y', strtotime($news['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recent_news)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="far fa-newspaper text-3xl mb-2 opacity-50"></i>
                            <p>No news articles yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 bg-gray-50 border-t border-gray-100 text-center">
                        <a href="news.php?action=add" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors w-full">
                            <i class="fas fa-plus mr-2"></i> Create New Article
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
