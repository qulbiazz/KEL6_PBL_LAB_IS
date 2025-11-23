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
$total_cards = count($cards);
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
    <title>Manage Card Data - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .card-preview {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .card-preview:hover {
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
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Card Data</h1>
                    <p class="text-gray-500 text-sm mt-1">Statistics cards displayed on homepage</p>
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
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    <?php echo $edit_item ? 'Edit' : 'Add'; ?> Card
                </h2>
                <p class="text-sm text-gray-600 mb-6 bg-blue-50 p-3 rounded-lg">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    For icons, use FontAwesome class names (e.g., "fas fa-users", "fas fa-project-diagram"). 
                    <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-600 hover:underline font-medium">Browse icons</a>
                </p>
                <form method="POST">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icon Class *</label>
                            <input type="text" name="icon" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['icon']) : ''; ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="fas fa-users">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Value *</label>
                            <input type="text" name="value" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['value']) : ''; ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="50+">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Label *</label>
                            <input type="text" name="label" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['label']) : ''; ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Active Projects">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                            <select name="display_order" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <?php 
                                $max_order = $edit_item ? $total_cards : $total_cards + 1;
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
                            <i class="fas fa-save mr-2"></i><?php echo $edit_item ? 'Update Card' : 'Add Card'; ?>
                        </button>
                        <?php if ($edit_item): ?>
                        <a href="cards.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm inline-flex items-center">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-th text-blue-600 mr-2"></i>
                        Card List (<?php echo count($cards); ?>)
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($cards as $card): ?>
                    <div class="card-preview bg-white rounded-xl p-6 text-center">
                        <div class="text-5xl text-blue-600 mb-4">
                            <i class="<?php echo htmlspecialchars($card['icon']); ?>"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($card['value']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($card['label']); ?></p>
                        
                        <div class="flex justify-center items-center gap-2 mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $card['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo $card['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                #<?php echo $card['display_order']; ?>
                            </span>
                        </div>
                        
                        <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                            <a href="?edit=<?php echo $card['id']; ?>" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </a>
                            <a href="?delete=<?php echo $card['id']; ?>" onclick="return confirm('Are you sure you want to delete this card?')" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-trash mr-2"></i> Delete
                            </a>
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
