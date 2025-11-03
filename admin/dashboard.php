<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

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

// Page visits (last 7 days)
$visits_query = "SELECT visit_date, SUM(visit_count) as total 
                 FROM page_visits 
                 WHERE visit_date >= CURRENT_DATE - INTERVAL '7 days' 
                 GROUP BY visit_date 
                 ORDER BY visit_date";
$visits = $conn->query($visits_query)->fetchAll();

// Recent news
$recent_news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - InLet Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
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
                <!-- Visits Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Page Visits (Last 7 Days)</h2>
                    <canvas id="visitsChart"></canvas>
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
    
    <script>
        // Visits Chart
        const ctx = document.getElementById('visitsChart').getContext('2d');
        const visitsData = <?php echo json_encode($visits); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: visitsData.map(v => v.visit_date),
                datasets: [{
                    label: 'Page Visits',
                    data: visitsData.map(v => v.total),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
