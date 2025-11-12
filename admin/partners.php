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
    // Check partner count before deletion
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM partners");
    $count_stmt->execute();
    $partner_count = $count_stmt->fetchColumn();
    
    if ($partner_count <= 1) {
        $error = 'Cannot delete the last partner. At least 1 partner must remain.';
    } else {
        $stmt = $conn->prepare("DELETE FROM partners WHERE id = :id");
        if ($stmt->execute(['id' => $_GET['delete']])) {
            $success = 'Partner deleted successfully';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $website = $_POST['website'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $logo = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $upload_dir = '../uploads/partners/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $logo = uniqid() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo);
    }
    
    if ($id) {
        if ($logo) {
            $stmt = $conn->prepare("UPDATE partners SET name = :name, logo = :logo, website = :website, display_order = :display_order, is_active = :is_active WHERE id = :id");
            $stmt->execute(['name' => $name, 'logo' => $logo, 'website' => $website, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("UPDATE partners SET name = :name, website = :website, display_order = :display_order, is_active = :is_active WHERE id = :id");
            $stmt->execute(['name' => $name, 'website' => $website, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        }
        $success = 'Partner updated successfully';
    } else {
        $stmt = $conn->prepare("INSERT INTO partners (name, logo, website, display_order, is_active) VALUES (:name, :logo, :website, :display_order, :is_active)");
        $stmt->execute(['name' => $name, 'logo' => $logo, 'website' => $website, 'display_order' => $display_order, 'is_active' => $is_active]);
        $success = 'Partner added successfully';
    }
}

$partners = $conn->query("SELECT * FROM partners ORDER BY display_order")->fetchAll();
$total_partners = count($partners);
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM partners WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $edit_item = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Partners - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8">Manage Partners</h1>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4"><?php echo $edit_item ? 'Edit' : 'Add'; ?> Partner</h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Partner Name *</label>
                            <input type="text" name="name" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['name']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Website</label>
                            <input type="url" name="website" value="<?php echo $edit_item ? htmlspecialchars($edit_item['website']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Logo <?php echo !$edit_item ? '*' : ''; ?></label>
                            <input type="file" name="logo" accept="image/*" <?php echo !$edit_item ? 'required' : ''; ?> class="w-full px-4 py-2 border rounded-lg">
                            <?php if ($edit_item && $edit_item['logo']): ?>
                            <img src="../uploads/partners/<?php echo htmlspecialchars($edit_item['logo']); ?>" class="mt-2 h-20 object-contain">
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Display Order</label>
                            <select name="display_order" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                                <?php 
                                $max_order = $edit_item ? $total_partners : $total_partners + 1;
                                for ($i = 1; $i <= $max_order; $i++): 
                                    $selected = ($edit_item && $edit_item['display_order'] == $i) ? 'selected' : (!$edit_item && $i == $max_order ? 'selected' : '');
                                ?>
                                    <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
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
                        <a href="partners.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg inline-block">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Partner List</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php foreach ($partners as $partner): ?>
                    <div class="border rounded-lg p-4 text-center">
                        <img src="../uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>" class="h-16 mx-auto mb-2 object-contain">
                        <h3 class="font-semibold text-sm mb-2"><?php echo htmlspecialchars($partner['name']); ?></h3>
                        <div class="text-center mb-2">
                            <span class="px-2 py-1 rounded text-xs <?php echo $partner['is_active'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                <?php echo $partner['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <div>
                            <a href="?edit=<?php echo $partner['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if (count($partners) > 1): ?>
                            <a href="?delete=<?php echo $partner['id']; ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400 cursor-not-allowed" title="Cannot delete the last partner">
                                <i class="fas fa-trash"></i>
                            </span>
                            <?php endif; ?>
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
