<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? null;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    try {
        if ($_POST['ajax'] === 'activate') {
            $id = $_POST['id'];
            // Deactivate all
            $db->exec("UPDATE about_settings SET is_active = false");
            // Activate selected
            $stmt = $db->prepare("UPDATE about_settings SET is_active = true WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Version activated successfully!']);
        } elseif ($_POST['ajax'] === 'delete') {
            $id = $_POST['id'];
            // Check if it's active
            $stmt = $db->prepare("SELECT is_active FROM about_settings WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $version = $stmt->fetch();

            if ($version['is_active']) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete active version!']);
            } else {
                $stmt = $db->prepare("DELETE FROM about_settings WHERE id = :id");
                $stmt->execute(['id' => $id]);
                echo json_encode(['success' => true, 'message' => 'Version deleted successfully!']);
            }
        } elseif ($_POST['ajax'] === 'duplicate') {
            $id = $_POST['id'];
            $stmt = $db->prepare("SELECT * FROM about_settings WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $original = $stmt->fetch();

            $new_name = 'Copy of ' . $original['version_name'];
            $sql = "INSERT INTO about_settings (version_name, hero_title, hero_subtitle, vision_text, mission_items, who_we_are_text, quote_text, focus_areas, is_active) 
                    VALUES (:version_name, :hero_title, :hero_subtitle, :vision_text, :mission_items, :who_we_are_text, :quote_text, :focus_areas, false)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'version_name' => $new_name,
                'hero_title' => $original['hero_title'],
                'hero_subtitle' => $original['hero_subtitle'],
                'vision_text' => $original['vision_text'],
                'mission_items' => $original['mission_items'],
                'who_we_are_text' => $original['who_we_are_text'],
                'quote_text' => $original['quote_text'],
                'focus_areas' => $original['focus_areas']
            ]);
            echo json_encode(['success' => true, 'message' => 'Version duplicated successfully!']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Handle form submission (Create/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    try {
        $version_name = $_POST['version_name'] ?? 'Unnamed Version';
        $hero_title = $_POST['hero_title'] ?? '';
        $hero_subtitle = $_POST['hero_subtitle'] ?? '';
        $vision_text = $_POST['vision_text'] ?? '';
        $who_we_are_text = $_POST['who_we_are_text'] ?? '';
        $quote_text = $_POST['quote_text'] ?? '';

        // Process mission items
        $mission_items = [];
        if (isset($_POST['mission_items']) && is_array($_POST['mission_items'])) {
            foreach ($_POST['mission_items'] as $item) {
                if (!empty(trim($item))) {
                    $mission_items[] = trim($item);
                }
            }
        }

        // Process focus areas
        $focus_areas = [];
        if (isset($_POST['focus_title']) && is_array($_POST['focus_title'])) {
            for ($i = 0; $i < count($_POST['focus_title']); $i++) {
                if (!empty(trim($_POST['focus_title'][$i]))) {
                    $focus_areas[] = [
                        'title' => trim($_POST['focus_title'][$i]),
                        'description' => trim($_POST['focus_description'][$i] ?? ''),
                        'icon' => trim($_POST['focus_icon'][$i] ?? 'fas fa-circle'),
                        'color' => trim($_POST['focus_color'][$i] ?? 'blue')
                    ];
                }
            }
        }

        if ($edit_id) {
            // Update existing
            $sql = "UPDATE about_settings SET 
                    version_name = :version_name,
                    hero_title = :hero_title,
                    hero_subtitle = :hero_subtitle,
                    vision_text = :vision_text,
                    mission_items = :mission_items,
                    who_we_are_text = :who_we_are_text,
                    quote_text = :quote_text,
                    focus_areas = :focus_areas,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'version_name' => $version_name,
                'hero_title' => $hero_title,
                'hero_subtitle' => $hero_subtitle,
                'vision_text' => $vision_text,
                'mission_items' => json_encode($mission_items),
                'who_we_are_text' => $who_we_are_text,
                'quote_text' => $quote_text,
                'focus_areas' => json_encode($focus_areas),
                'id' => $edit_id
            ]);
            $success = 'Version updated successfully!';
        } else {
            // Create new
            $sql = "INSERT INTO about_settings (version_name, hero_title, hero_subtitle, vision_text, mission_items, who_we_are_text, quote_text, focus_areas, is_active) 
                    VALUES (:version_name, :hero_title, :hero_subtitle, :vision_text, :mission_items, :who_we_are_text, :quote_text, :focus_areas, false)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'version_name' => $version_name,
                'hero_title' => $hero_title,
                'hero_subtitle' => $hero_subtitle,
                'vision_text' => $vision_text,
                'mission_items' => json_encode($mission_items),
                'who_we_are_text' => $who_we_are_text,
                'quote_text' => $quote_text,
                'focus_areas' => json_encode($focus_areas)
            ]);
            $success = 'New version created successfully!';
        }

        // Redirect to list
        header('Location: manage_about.php?success=' . urlencode($success));
        exit();
    } catch (Exception $e) {
        $error = 'Error saving version: ' . $e->getMessage();
    }
}

// Fetch all versions for list view - ordered by id only to maintain consistent position
$stmt = $db->query("SELECT * FROM about_settings ORDER BY id ASC");
$versions = $stmt->fetchAll();

// Fetch specific version for editing
$edit_version = null;
if ($action === 'edit' && $edit_id) {
    $stmt = $db->prepare("SELECT * FROM about_settings WHERE id = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_version = $stmt->fetch();

    if ($edit_version) {
        $mission_items = json_decode($edit_version['mission_items'], true) ?: [];
        $focus_areas = json_decode($edit_version['focus_areas'], true) ?: [];
    }
}

// For new version
if ($action === 'create') {
    $edit_version = [
        'version_name' => 'New Version',
        'hero_title' => '',
        'hero_subtitle' => '',
        'vision_text' => '',
        'who_we_are_text' => '',
        'quote_text' => ''
    ];
    $mission_items = [];
    $focus_areas = [];
}

// Success message from redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Page - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage About Page</h1>
                <p class="text-gray-500 text-sm mt-1">Manage multiple versions of the About page content</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div id="success-message" class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-xl mr-3"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Version List View -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">All Versions</h2>
                        <a href="?action=create" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Create New Version
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($versions as $version): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($version['version_name']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($version['is_active']): ?>
                                                <span class="px-3 py-1 inline-flex justify-center items-center text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 inline-flex justify-center items-center text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($version['updated_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="?action=edit&id=<?php echo $version['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <?php if (!$version['is_active']): ?>
                                                <button onclick="activateVersion(<?php echo $version['id']; ?>)" class="text-green-600 hover:text-green-900 mr-3">
                                                    <i class="fas fa-check-circle"></i> Activate
                                                </button>
                                            <?php endif; ?>
                                            <button onclick="duplicateVersion(<?php echo $version['id']; ?>)" class="text-purple-600 hover:text-purple-900 mr-3">
                                                <i class="fas fa-copy"></i> Duplicate
                                            </button>
                                            <?php if (!$version['is_active']): ?>
                                                <button onclick="deleteVersion(<?php echo $version['id']; ?>, '<?php echo htmlspecialchars($version['version_name']); ?>')" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif (($action === 'edit' || $action === 'create') && $edit_version): ?>
                <!-- Edit/Create Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold"><?php echo $action === 'create' ? 'Create New Version' : 'Edit Version'; ?></h2>
                        <a href="manage_about.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to List
                        </a>
                    </div>

                    <form method="POST" action="?action=<?php echo $action; ?>&id=<?php echo $edit_id; ?>" class="space-y-6">
                        <!-- Version Name -->
                        <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center">
                                <i class="fas fa-tag mr-2 text-blue-600"></i>Version Information
                            </h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Version Name *</label>
                                <input type="text" name="version_name" value="<?php echo htmlspecialchars($edit_version['version_name']); ?>"
                                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                        </div>

                        <!-- Hero Section -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center">
                                <i class="fas fa-heading mr-2 text-blue-600"></i>Hero Section
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                                    <input type="text" name="hero_title" value="<?php echo htmlspecialchars($edit_version['hero_title']); ?>"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                                    <input type="text" name="hero_subtitle" value="<?php echo htmlspecialchars($edit_version['hero_subtitle']); ?>"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Vision Section -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center">
                                <i class="fas fa-eye mr-2 text-blue-600"></i>Vision
                            </h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Vision Text</label>
                                <textarea name="vision_text" rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($edit_version['vision_text']); ?></textarea>
                            </div>
                        </div>

                        <!-- Mission Section -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center">
                                <i class="fas fa-bullseye mr-2 text-green-600"></i>Mission
                            </h3>
                            <div id="mission-items" class="space-y-3">
                                <?php foreach ($mission_items as $index => $item): ?>
                                    <div class="flex gap-2 mission-item">
                                        <input type="text" name="mission_items[]" value="<?php echo htmlspecialchars($item); ?>"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <button type="button" onclick="this.parentElement.remove()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" onclick="addMissionItem()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i>Add Mission Item
                            </button>
                        </div>

                        <!-- Who We Are Section -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center">
                                <i class="fas fa-users mr-2 text-blue-600"></i>Who We Are
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Main Content</label>
                                    <textarea name="who_we_are_text" rows="6"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($edit_version['who_we_are_text']); ?></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Use \n\n for new paragraphs</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quote Text</label>
                                    <textarea name="quote_text" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($edit_version['quote_text']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Focus Areas Section -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center">
                                <i class="fas fa-th-large mr-2 text-purple-600"></i>Focus Areas
                            </h3>
                            <div id="focus-areas" class="space-y-4">
                                <?php foreach ($focus_areas as $index => $area): ?>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg focus-area-item bg-gray-50">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                                                <input type="text" name="focus_title[]" value="<?php echo htmlspecialchars($area['title']); ?>"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Icon (FontAwesome class)</label>
                                                <input type="text" name="focus_icon[]" value="<?php echo htmlspecialchars($area['icon']); ?>"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                    placeholder="fas fa-graduation-cap">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                                <select name="focus_color[]" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                                    <option value="blue" <?php echo $area['color'] == 'blue' ? 'selected' : ''; ?>>Blue</option>
                                                    <option value="green" <?php echo $area['color'] == 'green' ? 'selected' : ''; ?>>Green</option>
                                                    <option value="purple" <?php echo $area['color'] == 'purple' ? 'selected' : ''; ?>>Purple</option>
                                                    <option value="orange" <?php echo $area['color'] == 'orange' ? 'selected' : ''; ?>>Orange</option>
                                                </select>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                                <input type="text" name="focus_description[]" value="<?php echo htmlspecialchars($area['description']); ?>"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                        <button type="button" onclick="this.parentElement.remove()" class="mt-2 px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600">
                                            <i class="fas fa-trash mr-1"></i>Remove
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" onclick="addFocusArea()" class="mt-3 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                <i class="fas fa-plus mr-2"></i>Add Focus Area
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-3">
                            <a href="manage_about.php" class="px-6 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-save mr-2"></i><?php echo $action === 'create' ? 'Create Version' : 'Save Changes'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        function addMissionItem() {
            const container = document.getElementById('mission-items');
            const div = document.createElement('div');
            div.className = 'flex gap-2 mission-item';
            div.innerHTML = `
                <input type="text" name="mission_items[]" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter mission item">
                <button type="button" onclick="this.parentElement.remove()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(div);
        }

        function addFocusArea() {
            const container = document.getElementById('focus-areas');
            const div = document.createElement('div');
            div.className = 'p-4 border-2 border-gray-200 rounded-lg focus-area-item bg-gray-50';
            div.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                        <input type="text" name="focus_title[]" 
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Icon (FontAwesome class)</label>
                        <input type="text" name="focus_icon[]" 
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="fas fa-graduation-cap">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                        <select name="focus_color[]" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="blue">Blue</option>
                            <option value="green">Green</option>
                            <option value="purple">Purple</option>
                            <option value="orange">Orange</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                        <input type="text" name="focus_description[]" 
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="mt-2 px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            `;
            container.appendChild(div);
        }

        function activateVersion(id) {
            if (confirm('Are you sure you want to activate this version? The current active version will be deactivated.')) {
                fetch('manage_about.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'ajax=activate&id=' + id
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }

        function deleteVersion(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                fetch('manage_about.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'ajax=delete&id=' + id
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }

        function duplicateVersion(id) {
            if (confirm('Create a copy of this version?')) {
                fetch('manage_about.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'ajax=duplicate&id=' + id
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
            const msg = document.getElementById('success-message');
            if (msg) msg.remove();
        }, 5000);
    </script>
</body>

</html>