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
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    if ($stmt->execute(['id' => $_GET['delete']])) {
        $success = 'Product deleted successfully';
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $full_description = $_POST['full_description'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $image = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
    }
    
    if ($id) {
        if ($image) {
            $stmt = $conn->prepare("UPDATE products SET name = :name, description = :description, image = :image, full_description = :full_description, is_active = :is_active WHERE id = :id");
            $stmt->execute(['name' => $name, 'description' => $description, 'image' => $image, 'full_description' => $full_description, 'is_active' => $is_active, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name = :name, description = :description, full_description = :full_description, is_active = :is_active WHERE id = :id");
            $stmt->execute(['name' => $name, 'description' => $description, 'full_description' => $full_description, 'is_active' => $is_active, 'id' => $id]);
        }
        $success = 'Product updated successfully';
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, image, full_description, is_active) VALUES (:name, :description, :image, :full_description, :is_active)");
        $stmt->execute(['name' => $name, 'description' => $description, 'image' => $image, 'full_description' => $full_description, 'is_active' => $is_active]);
        $success = 'Product added successfully';
    }
}

$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $edit_item = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8">Manage Products</h1>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4"><?php echo $edit_item ? 'Edit' : 'Add'; ?> Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Product Name *</label>
                        <input type="text" name="name" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['name']) : ''; ?>"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Short Description *</label>
                        <textarea name="description" rows="2" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"><?php echo $edit_item ? htmlspecialchars($edit_item['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Full Description</label>
                        <textarea name="full_description" rows="5"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"><?php echo $edit_item ? htmlspecialchars($edit_item['full_description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Image <?php echo !$edit_item ? '*' : ''; ?></label>
                        <input type="file" name="image" accept="image/*" <?php echo !$edit_item ? 'required' : ''; ?> class="w-full px-4 py-2 border rounded-lg">
                        <?php if ($edit_item && $edit_item['image']): ?>
                        <img src="../uploads/products/<?php echo htmlspecialchars($edit_item['image']); ?>" class="mt-2 h-32 object-cover">
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" <?php echo (!$edit_item || $edit_item['is_active']) ? 'checked' : ''; ?> class="mr-2">
                            <span class="text-gray-700 font-semibold">Active</span>
                        </label>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i><?php echo $edit_item ? 'Update' : 'Add'; ?>
                        </button>
                        <?php if ($edit_item): ?>
                        <a href="products.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg inline-block">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Product List</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($products as $item): ?>
                    <div class="border rounded-lg p-4">
                        <img src="../uploads/products/<?php echo htmlspecialchars($item['image']); ?>" class="w-full h-40 object-cover rounded mb-2">
                        <h3 class="font-bold text-lg mb-1"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>...</p>
                        <div class="flex justify-between items-center">
                            <span class="px-2 py-1 rounded text-xs <?php echo $item['is_active'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <div>
                                <a href="?edit=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </a>
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
