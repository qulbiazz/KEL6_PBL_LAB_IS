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

$gallery = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8">Manage Gallery</h1>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">Add Image</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Image *</label>
                        <input type="file" name="image" accept="image/*" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Caption</label>
                        <input type="text" name="caption" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" checked class="mr-2">
                            <span class="text-gray-700 font-semibold">Active</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-upload mr-2"></i>Upload Image
                    </button>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Gallery Images</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($gallery as $item): ?>
                    <div class="relative group">
                        <img src="../uploads/gallery/<?php echo htmlspecialchars($item['image']); ?>" class="w-full h-48 object-cover rounded">
                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure?')" class="text-white text-2xl">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                        <?php if ($item['caption']): ?>
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 text-white p-2 text-sm">
                            <?php echo htmlspecialchars($item['caption']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="assets/js/admin.js"></script>
</body>
</html>
