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
    $stmt = $conn->prepare("DELETE FROM card_data WHERE id = :id");
    if ($stmt->execute(['id' => $_GET['delete']])) {
        $success = 'Card deleted successfully';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $icon = $_POST['icon'];
    $value = $_POST['value'];
    $label = $_POST['label'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE card_data SET icon = :icon, value = :value, label = :label, display_order = :display_order, is_active = :is_active WHERE id = :id");
        $stmt->execute(['icon' => $icon, 'value' => $value, 'label' => $label, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        $success = 'Card updated successfully';
    } else {
        $stmt = $conn->prepare("INSERT INTO card_data (icon, value, label, display_order, is_active) VALUES (:icon, :value, :label, :display_order, :is_active)");
        $stmt->execute(['icon' => $icon, 'value' => $value, 'label' => $label, 'display_order' => $display_order, 'is_active' => $is_active]);
        $success = 'Card added successfully';
    }
}

$cards = $conn->query("SELECT * FROM card_data ORDER BY display_order")->fetchAll();
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM card_data WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $edit_item = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cards - InLet Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage Card Data</h1>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4"><?php echo $edit_item ? 'Edit' : 'Add'; ?> Card</h2>
                <p class="text-sm text-gray-600 mb-4">
                    For icons, use FontAwesome class names (e.g., "fas fa-users", "fas fa-project-diagram"). 
                    <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-600">Browse icons</a>
                </p>
                <form method="POST">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Icon Class *</label>
                            <input type="text" name="icon" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['icon']) : 'fas fa-star'; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                placeholder="fas fa-users">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Value *</label>
                            <input type="text" name="value" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['value']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                placeholder="50+">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Label *</label>
                            <input type="text" name="label" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['label']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                placeholder="Projects">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Display Order</label>
                            <input type="number" name="display_order" value="<?php echo $edit_item ? $edit_item['display_order'] : 0; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
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
                        <a href="cards.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg inline-block">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Card List</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php foreach ($cards as $card): ?>
                    <div class="border rounded-lg p-6 text-center">
                        <div class="text-5xl text-blue-600 mb-4">
                            <i class="<?php echo htmlspecialchars($card['icon']); ?>"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($card['value']); ?></h3>
                        <p class="text-gray-600 mb-3"><?php echo htmlspecialchars($card['label']); ?></p>
                        <div class="mb-2">
                            <span class="px-2 py-1 rounded text-xs <?php echo $card['is_active'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                <?php echo $card['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <div>
                            <a href="?edit=<?php echo $card['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $card['id']; ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
