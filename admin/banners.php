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
$error = '';

if (isset($_GET['delete'])) {
    $count_stmt = $conn->query("SELECT COUNT(*) FROM carousel_banners");
    $banner_count = $count_stmt->fetchColumn();
    
    if ($banner_count <= 1) {
        $error = 'Cannot delete the last banner. At least one banner must remain.';
    } else {
        $stmt = $conn->prepare("DELETE FROM carousel_banners WHERE id = :id");
        if ($stmt->execute(['id' => $_GET['delete']])) {
            $success = 'Banner deleted successfully';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/banners/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $image = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
    }
    
    if ($id) {
        if ($image) {
            $stmt = $conn->prepare("UPDATE carousel_banners SET title = :title, description = :description, image = :image, button_text = :button_text, button_link = :button_link, display_order = :display_order, is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image, 'button_text' => $button_text, 'button_link' => $button_link, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("UPDATE carousel_banners SET title = :title, description = :description, button_text = :button_text, button_link = :button_link, display_order = :display_order, is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'button_text' => $button_text, 'button_link' => $button_link, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        }
        $success = 'Banner updated successfully';
    } else {
        $stmt = $conn->prepare("INSERT INTO carousel_banners (title, description, image, button_text, button_link, display_order, is_active) VALUES (:title, :description, :image, :button_text, :button_link, :display_order, :is_active)");
        $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image, 'button_text' => $button_text, 'button_link' => $button_link, 'display_order' => $display_order, 'is_active' => $is_active]);
        $success = 'Banner added successfully';
    }
}

$banners = $conn->query("SELECT * FROM carousel_banners ORDER BY display_order")->fetchAll();
$total_banners = count($banners);
$edit_banner = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM carousel_banners WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $edit_banner = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Carousel Banners - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .banner-card {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .banner-card:hover {
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
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Carousel Banners</h1>
                    <p class="text-gray-500 text-sm mt-1">Manage homepage carousel banners</p>
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
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm mb-6 flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fas fa-images text-blue-600 mr-2"></i>
                    <?php echo $edit_banner ? 'Edit' : 'Add'; ?> Banner
                </h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_banner): ?><input type="hidden" name="id" value="<?php echo $edit_banner['id']; ?>"><?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" required value="<?php echo $edit_banner ? htmlspecialchars($edit_banner['title']) : ''; ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                            <select name="display_order" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <?php 
                                $max_order = $edit_banner ? $total_banners : $total_banners + 1;
                                for ($i = 1; $i <= $max_order; $i++): 
                                    $selected = ($edit_banner && $edit_banner['display_order'] == $i) ? 'selected' : (!$edit_banner && $i == $max_order ? 'selected' : '');
                                ?>
                                    <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo $edit_banner ? htmlspecialchars($edit_banner['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                            <input type="text" name="button_text" value="<?php echo $edit_banner ? htmlspecialchars($edit_banner['button_text']) : ''; ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Learn More">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                            <select name="button_link" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Select Page --</option>
                                <option value="index.php" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php') ? 'selected' : ''; ?>>Home</option>
                                <option value="index.php?page=about" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php?page=about') ? 'selected' : ''; ?>>About</option>
                                <option value="index.php#products" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#products') ? 'selected' : ''; ?>>Products</option>
                                <option value="index.php#team" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#team') ? 'selected' : ''; ?>>Team</option>
                                <option value="index.php#news" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#news') ? 'selected' : ''; ?>>News</option>
                                <option value="index.php#gallery" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#gallery') ? 'selected' : ''; ?>>Gallery</option>
                                <option value="index.php#research" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#research') ? 'selected' : ''; ?>>Research</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image <?php echo !$edit_banner ? '*' : ''; ?></label>
                        <input type="file" name="image" accept="image/*" <?php echo !$edit_banner ? 'required' : ''; ?> class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <?php if ($edit_banner && $edit_banner['image']): ?>
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-2">Current image:</p>
                            <img src="../uploads/banners/<?php echo htmlspecialchars($edit_banner['image']); ?>" class="h-32 object-cover rounded-lg">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" <?php echo (!$edit_banner || $edit_banner['is_active']) ? 'checked' : ''; ?> class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Active (visible on website)</span>
                        </label>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                            <i class="fas fa-save mr-2"></i><?php echo $edit_banner ? 'Update Banner' : 'Add Banner'; ?>
                        </button>
                        <?php if ($edit_banner): ?>
                        <a href="banners.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm inline-flex items-center">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-list text-blue-600 mr-2"></i>
                        Banner List (<?php echo count($banners); ?>)
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($banners as $banner): ?>
                    <div class="banner-card bg-white rounded-xl overflow-hidden">
                        <div class="relative">
                            <img src="../uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>" class="w-full h-48 object-cover">
                            <div class="absolute top-2 right-2 flex gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $banner['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> backdrop-blur-sm bg-opacity-90">
                                    <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 backdrop-blur-sm bg-opacity-90">
                                    #<?php echo $banner['display_order']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg text-gray-900 mb-2"><?php echo htmlspecialchars($banner['title']); ?></h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($banner['description']); ?></p>
                            
                            <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                                <a href="?edit=<?php echo $banner['id']; ?>" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i> Edit Banner
                                </a>
                                <?php if (count($banners) > 1): ?>
                                <a href="?delete=<?php echo $banner['id']; ?>" onclick="return confirm('Are you sure you want to delete this banner?')" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                    <i class="fas fa-trash mr-2"></i> Delete
                                </a>
                                <?php else: ?>
                                <button disabled class="w-full bg-gray-100 text-gray-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed flex items-center justify-center" title="Cannot delete the last banner">
                                    <i class="fas fa-trash mr-2"></i> Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
