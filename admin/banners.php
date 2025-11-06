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

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if this is the last banner
    $count_stmt = $conn->query("SELECT COUNT(*) FROM carousel_banners");
    $banner_count = $count_stmt->fetchColumn();
    
    if ($banner_count <= 1) {
        $error = 'Cannot delete the last banner. At least one banner must remain.';
    } else {
        $stmt = $conn->prepare("DELETE FROM carousel_banners WHERE id = :id");
        if ($stmt->execute(['id' => $id])) {
            $success = 'Banner deleted successfully';
        } else {
            $error = 'Error deleting banner';
        }
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/banners/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $file_extension;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
    }
    
    if ($id) {
        // Update
        if ($image) {
            $stmt = $conn->prepare("UPDATE carousel_banners SET title = :title, description = :description, image = :image, button_text = :button_text, button_link = :button_link, display_order = :display_order, is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image, 'button_text' => $button_text, 'button_link' => $button_link, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("UPDATE carousel_banners SET title = :title, description = :description, button_text = :button_text, button_link = :button_link, display_order = :display_order, is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'button_text' => $button_text, 'button_link' => $button_link, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        }
        $success = 'Banner updated successfully';
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO carousel_banners (title, description, image, button_text, button_link, display_order, is_active) VALUES (:title, :description, :image, :button_text, :button_link, :display_order, :is_active)");
        $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image, 'button_text' => $button_text, 'button_link' => $button_link, 'display_order' => $display_order, 'is_active' => $is_active]);
        $success = 'Banner added successfully';
    }
}

// Get all banners
$banners = $conn->query("SELECT * FROM carousel_banners ORDER BY display_order")->fetchAll();

// Get single banner for edit
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Carousel Banners</h1>
            </div>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <!-- Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4"><?php echo $edit_banner ? 'Edit' : 'Add'; ?> Banner</h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_banner): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_banner['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Title *</label>
                            <input type="text" name="title" required
                                value="<?php echo $edit_banner ? htmlspecialchars($edit_banner['title']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Display Order</label>
                            <input type="number" name="display_order"
                                value="<?php echo $edit_banner ? $edit_banner['display_order'] : 0; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"><?php echo $edit_banner ? htmlspecialchars($edit_banner['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Button Text</label>
                            <input type="text" name="button_text"
                                value="<?php echo $edit_banner ? htmlspecialchars($edit_banner['button_text']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Button Link</label>
                            <select name="button_link" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="">-- Select Page --</option>
                                <option value="index.php" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php') ? 'selected' : ''; ?>>Home</option>
                                <option value="about.php" <?php echo ($edit_banner && $edit_banner['button_link'] == 'about.php') ? 'selected' : ''; ?>>About</option>
                                <option value="index.php#products" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#products') ? 'selected' : ''; ?>>Products</option>
                                <option value="index.php#team" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#team') ? 'selected' : ''; ?>>Team</option>
                                <option value="index.php#news" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#news') ? 'selected' : ''; ?>>News</option>
                                <option value="index.php#gallery" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#gallery') ? 'selected' : ''; ?>>Gallery</option>
                                <option value="index.php#research" <?php echo ($edit_banner && $edit_banner['button_link'] == 'index.php#research') ? 'selected' : ''; ?>>Research</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Image <?php echo !$edit_banner ? '*' : ''; ?></label>
                        <input type="file" name="image" accept="image/*" <?php echo !$edit_banner ? 'required' : ''; ?>
                            class="w-full px-4 py-2 border rounded-lg">
                        <?php if ($edit_banner && $edit_banner['image']): ?>
                        <img src="../uploads/banners/<?php echo htmlspecialchars($edit_banner['image']); ?>" class="mt-2 h-32 object-cover">
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" <?php echo (!$edit_banner || $edit_banner['is_active']) ? 'checked' : ''; ?> class="mr-2">
                            <span class="text-gray-700 font-semibold">Active</span>
                        </label>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i><?php echo $edit_banner ? 'Update' : 'Add'; ?> Banner
                        </button>
                        <?php if ($edit_banner): ?>
                        <a href="banners.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg inline-block">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- List -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Banner List</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left">Image</th>
                                <th class="px-4 py-2 text-left">Title</th>
                                <th class="px-4 py-2 text-left">Order</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banners as $banner): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2">
                                    <img src="../uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>" class="h-16 w-24 object-cover rounded">
                                </td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($banner['title']); ?></td>
                                <td class="px-4 py-2"><?php echo $banner['display_order']; ?></td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 rounded text-xs <?php echo $banner['is_active'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                        <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <a href="?edit=<?php echo $banner['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (count($banners) > 1): ?>
                                    <a href="?delete=<?php echo $banner['id']; ?>" onclick="return confirm('Are you sure you want to delete this banner?')" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-gray-400 cursor-not-allowed" title="Cannot delete the last banner">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
