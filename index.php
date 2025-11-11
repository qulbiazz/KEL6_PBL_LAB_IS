<?php
// Load configuration
require_once 'config/config.php';

// Start session with custom lifetime
ini_set('session.gc_maxlifetime', Config::sessionLifetime());
session_start();

// Ensure upload directories exist
Config::ensureUploadDirectories();

// Initialize database connection
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Track visitor
require_once 'models/VisitorModel.php';
$visitorModel = new VisitorModel($conn);
$visitorStats = $visitorModel->trackVisitor();

// Get page parameter from URL
$page = $_GET['page'] ?? 'home';

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
        
    case 'about':
        $pageTitle = 'About Us - InLet';
        $view = 'pages/about';
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
        
        // Load LecturerDetailModel
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
?>

