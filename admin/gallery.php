<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$success = '';

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = :id");
    if ($stmt->execute(['id' => $_GET['delete']])) {
        $success = 'Gallery item deleted successfully';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = $_POST['caption'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/gallery/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $image = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        
        $stmt = $conn->prepare("INSERT INTO gallery (image, caption, is_active) VALUES (:image, :caption, :is_active)");
        $stmt->execute(['image' => $image, 'caption' => $caption, 'is_active' => $is_active]);
        $success = 'Gallery item added successfully';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$total_query = $conn->query("SELECT COUNT(*) FROM gallery");
$total_items = $total_query->fetchColumn();
$total_pages = ceil($total_items / $per_page);

$gallery = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT $per_page OFFSET $offset")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .gallery-item {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .gallery-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>
        <main class="flex-1 p-4 md:p-8 ml-0 lg:ml-64 min-h-screen">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Gallery</h1>
                    <p class="text-gray-500 text-sm mt-1">Upload and manage gallery images</p>
                </div>
            </div>
            
            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm mb-6 flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fas fa-upload text-blue-600 mr-2"></i>
                    Add Image
                </h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image *</label>
                            <input type="file" name="image" accept="image/*" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Caption</label>
                            <input type="text" name="caption" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Optional image caption">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Active (visible on website)</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                        <i class="fas fa-upload mr-2"></i>Upload Image
                    </button>
                </form>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-images text-blue-600 mr-2"></i>
                        Gallery Images (<?php echo $total_items; ?>)
                    </h2>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-6">
                    <?php foreach ($gallery as $item): ?>
                    <div class="gallery-item bg-white rounded-xl overflow-hidden">
                        <div class="relative group">
                            <img src="../uploads/gallery/<?php echo htmlspecialchars($item['image']); ?>" class="w-full h-48 object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all flex items-center justify-center">
                                <a href="?delete=<?php echo $item['id']; ?>&page=<?php echo $page; ?>" onclick="return confirm('Are you sure you want to delete this image?')" class="opacity-0 group-hover:opacity-100 transition-opacity bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                                    <i class="fas fa-trash mr-2"></i> Delete
                                </a>
                            </div>
                            <?php if (!$item['is_active']): ?>
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    Inactive
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($item['caption']): ?>
                        <div class="p-3 bg-gray-50">
                            <p class="text-sm text-gray-700 line-clamp-2"><?php echo htmlspecialchars($item['caption']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="flex justify-center items-center gap-2 pt-6 border-t border-gray-200">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <?php endif; ?>
                    
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
