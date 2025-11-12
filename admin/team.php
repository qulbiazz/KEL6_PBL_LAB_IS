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
    $stmt = $conn->prepare("DELETE FROM team_members WHERE id = :id");
    if ($stmt->execute(['id' => $_GET['delete']])) {
        $success = 'Team member deleted successfully';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $position = $_POST['position'];
    $bio = $_POST['bio'];
    $email = $_POST['email'];
    $linkedin = $_POST['linkedin'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $upload_dir = '../uploads/team/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $photo = uniqid() . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo);
    }
    
    if ($id) {
        if ($photo) {
            $stmt = $conn->prepare("UPDATE team_members SET name = :name, position = :position, photo = :photo, bio = :bio, email = :email, linkedin = :linkedin, display_order = :display_order, is_active = :is_active WHERE id = :id");
            $stmt->execute(['name' => $name, 'position' => $position, 'photo' => $photo, 'bio' => $bio, 'email' => $email, 'linkedin' => $linkedin, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("UPDATE team_members SET name = :name, position = :position, bio = :bio, email = :email, linkedin = :linkedin, display_order = :display_order, is_active = :is_active WHERE id = :id");
            $stmt->execute(['name' => $name, 'position' => $position, 'bio' => $bio, 'email' => $email, 'linkedin' => $linkedin, 'display_order' => $display_order, 'is_active' => $is_active, 'id' => $id]);
        }
        $success = 'Team member updated successfully';
    } else {
        $stmt = $conn->prepare("INSERT INTO team_members (name, position, photo, bio, email, linkedin, display_order, is_active) VALUES (:name, :position, :photo, :bio, :email, :linkedin, :display_order, :is_active)");
        $stmt->execute(['name' => $name, 'position' => $position, 'photo' => $photo, 'bio' => $bio, 'email' => $email, 'linkedin' => $linkedin, 'display_order' => $display_order, 'is_active' => $is_active]);
        $success = 'Team member added successfully';
    }
}

$team = $conn->query("SELECT * FROM team_members ORDER BY display_order")->fetchAll();
$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM team_members WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $edit_item = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8">Manage Team Members</h1>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4"><?php echo $edit_item ? 'Edit' : 'Add'; ?> Team Member</h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Name *</label>
                            <input type="text" name="name" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['name']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Position *</label>
                            <input type="text" name="position" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['position']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Email</label>
                            <input type="email" name="email" value="<?php echo $edit_item ? htmlspecialchars($edit_item['email']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">LinkedIn</label>
                            <input type="url" name="linkedin" value="<?php echo $edit_item ? htmlspecialchars($edit_item['linkedin']) : ''; ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Bio</label>
                        <textarea name="bio" rows="3"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"><?php echo $edit_item ? htmlspecialchars($edit_item['bio']) : ''; ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Photo <?php echo !$edit_item ? '*' : ''; ?></label>
                            <input type="file" name="photo" accept="image/*" <?php echo !$edit_item ? 'required' : ''; ?> class="w-full px-4 py-2 border rounded-lg">
                            <?php if ($edit_item && $edit_item['photo']): ?>
                            <img src="../uploads/team/<?php echo htmlspecialchars($edit_item['photo']); ?>" class="mt-2 h-32 object-cover rounded">
                            <?php endif; ?>
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
                        <a href="team.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg inline-block">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Team Members</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($team as $member): ?>
                    <div class="border rounded-lg p-4 text-center">
                        <img src="../uploads/team/<?php echo htmlspecialchars($member['photo']); ?>" class="w-32 h-32 object-cover rounded-full mx-auto mb-3">
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($member['name']); ?></h3>
                        <p class="text-blue-600 text-sm mb-2"><?php echo htmlspecialchars($member['position']); ?></p>
                        <div class="flex justify-center items-center space-x-2 mb-2">
                            <span class="px-2 py-1 rounded text-xs <?php echo $member['is_active'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <div>
                            <a href="?edit=<?php echo $member['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $member['id']; ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
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
