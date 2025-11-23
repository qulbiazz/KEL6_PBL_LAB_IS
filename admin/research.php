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
    $stmt = $conn->prepare("DELETE FROM research_activities WHERE id = :id");
    if ($stmt->execute(['id' => $_GET['delete']])) {
        $success = 'Research activity deleted successfully';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $full_description = $_POST['full_description'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/research/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $image = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
    }
    
    if ($id) {
        if ($image) {
            $stmt = $conn->prepare("UPDATE research_activities SET title = :title, description = :description, image = :image, full_description = :full_description, is_active = :is_active WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image, 'full_description' => $full_description, 'is_active' => $is_active, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("UPDATE research_activities SET title = :title, description = :description, full_description = :full_description, is_active = :is_active WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'full_description' => $full_description, 'is_active' => $is_active, 'id' => $id]);
        }
        $success = 'Research activity updated successfully';
    } else {
        $stmt = $conn->prepare("INSERT INTO research_activities (title, description, image, full_description, is_active) VALUES (:title, :description, :image, :full_description, :is_active)");
        $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image, 'full_description' => $full_description, 'is_active' => $is_active]);
        $success = 'Research activity added successfully';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

$total_query = $conn->query("SELECT COUNT(*) FROM research_activities");
$total_items = $total_query->fetchColumn();
$total_pages = ceil($total_items / $per_page);

$research = $conn->query("SELECT * FROM research_activities ORDER BY created_at DESC LIMIT $per_page OFFSET $offset")->fetchAll();

$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM research_activities WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $edit_item = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Research - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .research-card {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .research-card:hover {
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
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Research Activities</h1>
                    <p class="text-gray-500 text-sm mt-1">Add and manage research activities</p>
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
                    <i class="fas fa-flask text-blue-600 mr-2"></i>
                    <?php echo $edit_item ? 'Edit' : 'Add'; ?> Research Activity
                </h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" name="title" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['title']) : ''; ?>"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Short Description *</label>
                        <textarea name="description" rows="3" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo $edit_item ? htmlspecialchars($edit_item['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                        <textarea name="full_description" rows="6" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo $edit_item ? htmlspecialchars($edit_item['full_description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image <?php echo !$edit_item ? '*' : ''; ?></label>
                        <input type="file" name="image" accept="image/*" <?php echo !$edit_item ? 'required' : ''; ?> class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <?php if ($edit_item && $edit_item['image']): ?>
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-2">Current image:</p>
                            <img src="../uploads/research/<?php echo htmlspecialchars($edit_item['image']); ?>" class="h-32 object-cover rounded-lg">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" <?php echo (!$edit_item || $edit_item['is_active']) ? 'checked' : ''; ?> class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Active (visible on website)</span>
                        </label>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                            <i class="fas fa-save mr-2"></i><?php echo $edit_item ? 'Update Research' : 'Add Research'; ?>
                        </button>
                        <?php if ($edit_item): ?>
                        <a href="research.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm inline-flex items-center">
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
                        Research Activities (<?php echo $total_items; ?>)
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <?php foreach ($research as $item): ?>
                    <div class="research-card bg-white rounded-xl overflow-hidden">
                        <img src="../uploads/research/<?php echo htmlspecialchars($item['image']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="font-bold text-lg text-gray-900 mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($item['description']); ?></p>
                            
                            <div class="flex justify-center mb-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $item['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                    <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            
                            <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                                <a href="?edit=<?php echo $item['id']; ?>" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </a>
                                <a href="?delete=<?php echo $item['id']; ?>&page=<?php echo $page; ?>" onclick="return confirm('Are you sure you want to delete this research activity?')" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                    <i class="fas fa-trash mr-2"></i> Delete
                                </a>
                            </div>
                        </div>
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
