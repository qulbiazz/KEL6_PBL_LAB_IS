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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .partner-card {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .partner-card:hover {
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
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Partners</h1>
                    <p class="text-gray-500 text-sm mt-1">Add and manage your organization partners</p>
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
                    <i class="fas fa-handshake text-blue-600 mr-2"></i>
                    <?php echo $edit_item ? 'Edit' : 'Add'; ?> Partner
                </h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Partner Name *</label>
                            <input type="text" name="name" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['name']) : ''; ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                            <input type="url" name="website" value="<?php echo $edit_item ? htmlspecialchars($edit_item['website']) : ''; ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="https://example.com">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo <?php echo !$edit_item ? '*' : ''; ?></label>
                            <input type="file" name="logo" accept="image/*" <?php echo !$edit_item ? 'required' : ''; ?> class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <?php if ($edit_item && $edit_item['logo']): ?>
                            <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 mb-2">Current logo:</p>
                                <img src="../uploads/partners/<?php echo htmlspecialchars($edit_item['logo']); ?>" class="h-20 object-contain">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                            <select name="display_order" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                    
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" <?php echo (!$edit_item || $edit_item['is_active']) ? 'checked' : ''; ?> class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Active (visible on website)</span>
                        </label>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                            <i class="fas fa-save mr-2"></i><?php echo $edit_item ? 'Update Partner' : 'Add Partner'; ?>
                        </button>
                        <?php if ($edit_item): ?>
                        <a href="partners.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm inline-flex items-center">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-building text-blue-600 mr-2"></i>
                        Partner List (<?php echo count($partners); ?>)
                    </h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                    <?php foreach ($partners as $partner): ?>
                    <div class="partner-card bg-white rounded-xl p-6 text-center">
                        <div class="relative inline-block mb-4">
                            <img src="../uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>" class="h-20 w-full object-contain mx-auto">
                            <div class="absolute -top-2 -right-2 bg-white rounded-full p-1 shadow-md">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold <?php echo $partner['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                    <i class="fas <?php echo $partner['is_active'] ? 'fa-check' : 'fa-times'; ?>"></i>
                                </span>
                            </div>
                        </div>
                        
                        <h3 class="font-semibold text-sm text-gray-900 mb-2 line-clamp-2"><?php echo htmlspecialchars($partner['name']); ?></h3>
                        
                        <div class="flex justify-center items-center gap-1 mb-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $partner['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo $partner['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                #<?php echo $partner['display_order']; ?>
                            </span>
                        </div>
                        
                        <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                            <a href="?edit=<?php echo $partner['id']; ?>" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <?php if (count($partners) > 1): ?>
                            <a href="?delete=<?php echo $partner['id']; ?>" onclick="return confirm('Are you sure you want to delete this partner?')" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </a>
                            <?php else: ?>
                            <button disabled class="w-full bg-gray-100 text-gray-400 px-3 py-2 rounded-lg text-xs font-medium cursor-not-allowed flex items-center justify-center" title="Cannot delete the last partner">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
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
