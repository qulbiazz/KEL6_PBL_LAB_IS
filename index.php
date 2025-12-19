<?php
// Load configuration
require_once 'config/config.php';

// Start session
ini_set('session.gc_maxlifetime', Config::sessionLifetime());
session_start();

// Ensure upload directories exist
Config::ensureUploadDirectories();

// Initialize database connection
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Get page parameter from URL
$page = $_GET['page'] ?? 'home';

// Handle AJAX requests BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Route AJAX requests to appropriate handlers
    if ($page === 'attendance') {
        require_once 'models/AttendanceModel.php';

        $student_id = (int)($_POST['student_id'] ?? 0);
        $attendance_type = $_POST['attendance_type'] ?? '';

        $response = ['success' => false, 'message' => ''];

        // Validasi input
        if (empty($student_id) || empty($attendance_type)) {
            $response['message'] = 'Semua field harus diisi!';
        } elseif (!in_array($attendance_type, ['datang', 'pulang'])) {
            $response['message'] = 'Tipe absensi tidak valid!';
        } else {
            $attendanceModel = new AttendanceModel($conn);

            // Ambil IP address dan user agent
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Catat absensi
            $response = $attendanceModel->recordAttendance(
                $student_id,
                $attendance_type,
                $ip_address,
                $user_agent
            );
        }

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Track visitor (after AJAX check)
require_once 'models/VisitorModel.php';
$visitorModel = new VisitorModel($conn);
$visitorStats = $visitorModel->trackVisitor();

// Initialize variables
$pageTitle = 'InLet - Information and Learning Engineering Technology';
$bodyClass = 'bg-gray-50';

// Router - Handle different pages
switch ($page) {
    case 'home':
        // Load HomeModel
        require_once 'models/HomeModel.php';
        $homeModel = new HomeModel($conn);

        // Fetch data for homepage
        $banners = $homeModel->getBanners();
        $cards = $homeModel->getCards();
        $products = $homeModel->getProducts();
        $partners = $homeModel->getPartners();
        $team = $homeModel->getTeamMembers();
        $news = $homeModel->getNews();
        $gallery = $homeModel->getGallery();
        $research = $homeModel->getResearchActivities();

        $bodyClass = 'bg-gray-50 @container mx-auto';
        $view = 'pages/home';
        break;

    case 'products':
        require_once 'models/ProductModel.php';
        $productModel = new ProductModel($conn);
        $products = $productModel->getAllProducts();
        $pageTitle = 'All Products - InLet';
        $view = 'pages/products';
        break;

    case 'news':
        require_once 'models/NewsModel.php';
        $newsModel = new NewsModel($conn);
        $allNews = $newsModel->getAllNews();
        $pageTitle = 'All News - InLet';
        $view = 'pages/news';
        break;

    case 'research':
        require_once 'models/ResearchModel.php';
        $researchModel = new ResearchModel($conn);
        $allResearch = $researchModel->getAllResearch();
        $pageTitle = 'Research Activities - InLet';
        $view = 'pages/research';
        break;

    case 'about':
        // Load about page settings from database
        $stmt = $conn->prepare("SELECT * FROM about_settings WHERE is_active = true LIMIT 1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        // Decode JSON fields
        if ($settings) {
            $mission_items = json_decode($settings['mission_items'], true) ?? [];
            $focus_areas = json_decode($settings['focus_areas'], true) ?? [];
        } else {
            // Default values if no active settings found
            $settings = null;
            $mission_items = [];
            $focus_areas = [];
        }

        $pageTitle = 'About Us - InLet';
        $view = 'pages/about';
        break;
    case 'borrowing':
        $pageTitle = 'Peminjaman - InLET';
        $view = 'pages/borrowing';
        break;

    case 'attendance':
        $pageTitle = 'Absensi Mahasiswa - InLET';
        $view = 'pages/attendance';
        break;

    case 'product-detail':
        // Get product ID
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$product_id) {
            header('Location: index.php');
            exit();
        }

        // Load ProductModel
        require_once 'models/ProductModel.php';
        $productModel = new ProductModel($conn);
        $product = $productModel->getProductById($product_id);

        if (!$product) {
            header('Location: index.php');
            exit();
        }

        $pageTitle = htmlspecialchars($product['name']) . ' - InLet';
        $view = 'pages/product-detail';
        break;

    case 'news-detail':
        // Get news ID
        $news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$news_id) {
            header('Location: index.php');
            exit();
        }

        // Load NewsModel
        require_once 'models/NewsModel.php';
        $newsModel = new NewsModel($conn);
        $news = $newsModel->getNewsById($news_id);

        if (!$news) {
            header('Location: index.php');
            exit();
        }

        // Get other news for "Another News" section
        $otherNews = $newsModel->getOtherNews($news_id, 3);

        $pageTitle = htmlspecialchars($news['title']) . ' - InLet';
        $view = 'pages/news-detail';
        break;

    case 'research-detail':
        // Get research ID
        $research_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$research_id) {
            header('Location: index.php');
            exit();
        }

        // Load ResearchModel
        require_once 'models/ResearchModel.php';
        $researchModel = new ResearchModel($conn);
        $research = $researchModel->getResearchById($research_id);

        if (!$research) {
            header('Location: index.php');
            exit();
        }

        // Get other research for "Another Research" section
        $otherResearch = $researchModel->getOtherResearch($research_id, 3);

        $pageTitle = htmlspecialchars($research['title']) . ' - InLet';
        $view = 'pages/research-detail';
        break;

    case 'lecturer-detail':
        // Get lecturer ID
        $lecturer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$lecturer_id) {
            header('Location: index.php#team');
            exit();
        }

        // Load LecturerDetailMod
        require_once 'models/LecturerDetailModel.php';
        $lecturerModel = new LecturerDetailModel($conn);
        $lecturer = $lecturerModel->getLecturerProfile($lecturer_id);

        if (!$lecturer) {
            header('Location: index.php#team');
            exit();
        }

        $pageTitle = htmlspecialchars($lecturer['name']) . ' - InLet';
        $view = 'pages/lecturer-detail';
        break;

    default:
        // 404 - Page not found
        header('Location: index.php');
        exit();
}

// Load layout
include 'views/layouts/header.php';
include 'views/' . $view . '.php';
include 'views/layouts/footer.php';
