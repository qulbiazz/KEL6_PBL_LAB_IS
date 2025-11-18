<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../models/LecturerDetailModel.php';

$db = new Database();
$conn = $db->getConnection();
$lecturerModel = new LecturerDetailModel($conn);

// Get all team members for dropdown
$query = "SELECT id, name, position FROM team_members ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->execute();
$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle selected lecturer
$selectedLecturerId = isset($_GET['lecturer_id']) ? (int)$_GET['lecturer_id'] : null;
$lecturer = null;
$projects = [];
$education = [];
$skills = [];
$interests = [];
$publications = [];

if ($selectedLecturerId) {
    $lecturer = $lecturerModel->getLecturerProfile($selectedLecturerId);
    if ($lecturer) {
        $projects = $lecturer['projects'];
        $education = $lecturer['education'];
        $skills = [];
        foreach ($lecturer['skills'] as $category => $skillList) {
            foreach ($skillList as $skill) {
                $skills[] = $skill;
            }
        }
        $interests = $lecturer['interests'];
        $publications = $lecturer['publications'];
    }
}

$pageTitle = 'Lecturer Details';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Prevent horizontal overflow */
        * {
            box-sizing: border-box;
        }

        body {
            overflow-x: hidden;
        }

        /* Skeleton loader styles */
        .skeleton {
            animation: skeleton-loading 1s linear infinite alternate;
        }

        @keyframes skeleton-loading {
            0% {
                background-color: hsl(200, 20%, 80%);
            }

            100% {
                background-color: hsl(200, 20%, 95%);
            }
        }

        .skeleton-text {
            height: 1rem;
            border-radius: 0.25rem;
        }

        .skeleton-button {
            height: 2.5rem;
            border-radius: 0.375rem;
        }

        /* Custom scrollbar for tab navigation */
        .tab-scroll::-webkit-scrollbar {
            height: 4px;
        }

        .tab-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .tab-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 2px;
        }

        .tab-scroll::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .no-underline,
        .no-underline:active,
        .no-underline:focus {
            text-decoration: none !important;
            border-bottom: none !important;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen overflow-x-hidden">
            <div class="max-w-7xl mx-auto w-full">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['success'];
                                                                unset($_SESSION['success']); ?>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $_SESSION['error'];
                                                                        unset($_SESSION['error']); ?>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                    <div class="w-full md:w-auto">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 break-words">Manage Team Details</h1>
                    </div>
                </div>

                <!-- Select Lecturer -->
                <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                    <div class="bg-gray-50 px-4 md:px-6 py-4 border-b-2 border-gray-200 font-semibold">
                        <i class="fas fa-user-tie mr-2"></i>
                        <span class="break-words">Select Team</span>
                    </div>
                    <div class="p-4 md:p-6">
                        <form method="GET" action="" class="flex flex-col md:flex-row gap-4 items-stretch md:items-end">
                            <div class="flex-1 w-full min-w-0">
                                <label class="block font-bold mb-2 text-sm md:text-base">Choose Team</label>
                                <select name="lecturer_id"
                                    id="lecturerSelect"
                                    class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Select Team Members --</option>
                                    <?php foreach ($teamMembers as $member): ?>
                                        <option value="<?php echo $member['id']; ?>" <?php echo ($selectedLecturerId == $member['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($member['name']); ?> - <?php echo htmlspecialchars($member['position']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="w-full md:w-auto flex-shrink-0">
                                <button type="submit"
                                    id="loadProfileBtn"
                                    class="w-full md:w-auto bg-blue-500 text-white px-4 md:px-6 py-2 md:py-3 rounded text-sm md:text-base hover:bg-blue-600 transition-colors whitespace-nowrap">
                                    <i class="fas fa-search mr-2"></i> Load Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($lecturer): ?>

                    <!-- Tabs Navigation -->
                    <div class="border-b-2 border-gray-200 mb-6 -mx-4 md:mx-0 overflow-hidden">
                        <div class="overflow-x-auto tab-scroll px-4 md:px-0">
                            <nav class="flex space-x-1 min-w-max " id="lecturerTabs">
                                <button class="px-3 md:px-4 py-2 md:py-3 font-medium text-xs md:text-sm text-blue-600 border-b-2 border-blue-600 whitespace-nowrap transition-colors flex-shrink-0" onclick="showTab('basic')">
                                    <i class="fas fa-info-circle mr-1"></i> <span>Info</span>
                                </button>
                                <button class="px-3 md:px-4 py-2 md:py-3 font-medium text-xs md:text-sm text-gray-600 hover:text-blue-600 whitespace-nowrap transition-colors flex-shrink-0" onclick="showTab('projects')">
                                    <i class="fas fa-project-diagram mr-1"></i> <span>Projects</span> <span class="bg-gray-200 px-1.5 py-0.5 rounded text-xs ml-1"><?php echo count($projects); ?></span>
                                </button>
                                <button class="px-3 md:px-4 py-2 md:py-3 font-medium text-xs md:text-sm text-gray-600 hover:text-blue-600 whitespace-nowrap transition-colors flex-shrink-0" onclick="showTab('education')">
                                    <i class="fas fa-graduation-cap mr-1"></i> <span>Education</span> <span class="bg-gray-200 px-1.5 py-0.5 rounded text-xs ml-1"><?php echo count($education); ?></span>
                                </button>
                                <button class="px-3 md:px-4 py-2 md:py-3 font-medium text-xs md:text-sm text-gray-600 hover:text-blue-600 whitespace-nowrap transition-colors flex-shrink-0" onclick="showTab('skills')">
                                    <i class="fas fa-code mr-1"></i> <span>Skills</span> <span class="bg-gray-200 px-1.5 py-0.5 rounded text-xs ml-1"><?php echo count($skills); ?></span>
                                </button>
                                <button class="px-3 md:px-4 py-2 md:py-3 font-medium text-xs md:text-sm text-gray-600 hover:text-blue-600 whitespace-nowrap transition-colors flex-shrink-0" onclick="showTab('interests')">
                                    <i class="fas fa-heart mr-1"></i> <span>Interests</span> <span class="bg-gray-200 px-1.5 py-0.5 rounded text-xs ml-1"><?php echo count($interests); ?></span>
                                </button>
                                <button class="px-3 md:px-4 py-2 md:py-3 font-medium text-xs md:text-sm text-gray-600 hover:text-blue-600 whitespace-nowrap transition-colors flex-shrink-0" onclick="showTab('publications')">
                                    <i class="fas fa-file-alt mr-1"></i> <span>Publications</span> <span class="bg-gray-200 px-1.5 py-0.5 rounded text-xs ml-1"><?php echo count($publications); ?></span>
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Tabs Content -->
                    <div id="lecturerTabsContent">

                        <!-- Basic Info Tab -->
                        <div id="basic" class="tab-content">
                            <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                                <div class="bg-gray-50 px-4 md:px-6 py-4 border-b-2 border-gray-200 font-semibold text-sm md:text-base">
                                    <i class="fas fa-edit mr-2"></i>
                                    <span class="break-words">Update Basic Information</span>
                                </div>
                                <div class="p-4 md:p-6">
                                    <!-- Info Alert -->
                                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-3 md:p-4 mb-4 md:mb-6 flex text-sm md:text-base" role="alert">
                                        <i class="fas fa-info-circle mr-2 md:mr-3 mt-1 flex-shrink-0"></i>
                                        <div class="break-words min-w-0">
                                            <strong>Note:</strong> To edit <strong>Name</strong>, <strong>Position</strong>, and <strong>Profile Photo</strong>, please go to
                                            <a href="team.php" class="font-bold underline">Team Members</a> page.
                                        </div>
                                    </div>

                                    <form action="actions/update_lecturer_basic.php" method="POST">
                                        <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">

                                        <!-- Display Current Name & Position (Read-only) -->
                                        <div class="mb-4 md:mb-6">
                                            <div class="bg-gray-50 rounded-lg p-3 md:p-4">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                                                    <div class="min-w-0">
                                                        <label class="block font-bold text-gray-600 text-xs md:text-sm mb-1">Current Name</label>
                                                        <p class="text-base md:text-lg break-words"><?php echo htmlspecialchars($lecturer['name']); ?></p>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <label class="block font-bold text-gray-600 text-xs md:text-sm mb-1">Current Position</label>
                                                        <p class="text-base md:text-lg break-words"><?php echo htmlspecialchars($lecturer['position']); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-3 md:mb-4">
                                            <div class="min-w-0">
                                                <label class="block mb-2 text-sm md:text-base">Address</label>
                                                <input type="text" name="address" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($lecturer['address'] ?? ''); ?>">
                                            </div>
                                            <div class="min-w-0">
                                                <label class="block mb-2 text-sm md:text-base">Phone</label>
                                                <input type="text" name="phone" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($lecturer['phone'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <div class="mb-3 md:mb-4 min-w-0">
                                            <label class="block mb-2 text-sm md:text-base">Email</label>
                                            <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($lecturer['email'] ?? ''); ?>">
                                        </div>

                                        <div class="mb-3 md:mb-4 min-w-0">
                                            <label class="block mb-2 text-sm md:text-base">Biography</label>
                                            <textarea name="bio" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"><?php echo htmlspecialchars($lecturer['bio'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-3 md:mb-4">
                                            <div class="min-w-0">
                                                <label class="block mb-2 text-sm md:text-base">LinkedIn URL</label>
                                                <input type="url" name="linkedin_url" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($lecturer['linkedin_url'] ?? ''); ?>">
                                            </div>
                                            <div class="min-w-0">
                                                <label class="block mb-2 text-sm md:text-base">Google Scholar URL</label>
                                                <input type="url" name="google_scholar_url" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($lecturer['google_scholar_url'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-4 md:mb-6">
                                            <div class="min-w-0">
                                                <label class="block mb-2 text-sm md:text-base">ResearchGate URL</label>
                                                <input type="url" name="researchgate_url" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($lecturer['researchgate_url'] ?? ''); ?>">
                                            </div>
                                            <div class="min-w-0">
                                                <label class="block mb-2 text-sm md:text-base">ORCID URL</label>
                                                <input type="url" name="orcid_url" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($lecturer['orcid_url'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full md:w-auto bg-blue-500 text-white px-4 md:px-6 py-2 rounded text-sm md:text-base hover:bg-blue-600 transition-colors">
                                            <i class="fas fa-save mr-1"></i> Save Changes
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Projects Tab -->
                        <div id="projects" class="tab-content hidden">
                            <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                                <div class="bg-gray-50 px-4 md:px-6 py-4 border-b-2 border-gray-200 font-semibold flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-sm md:text-base">
                                    <span class="break-words"><i class="fas fa-list mr-1"></i> Work in Progress / Projects</span>
                                    <button class="w-full sm:w-auto bg-green-500 text-white px-3 md:px-4 py-2 rounded text-xs md:text-sm hover:bg-green-600 transition-colors whitespace-nowrap flex-shrink-0" onclick="openModal('addProjectModal')">
                                        <i class="fas fa-plus mr-1"></i> Add Project
                                    </button>
                                </div>
                                <div class="p-4 md:p-6 overflow-x-auto">
                                    <table class="min-w-full border border-gray-300">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Title</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Category</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Period</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($projects)): ?>
                                                <tr>
                                                    <td colspan="4" class="border border-gray-300 px-2 md:px-4 py-3 text-center text-xs md:text-sm">No projects added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($projects as $project): ?>
                                                    <tr>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words max-w-xs"><?php echo htmlspecialchars($project['title']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words"><?php echo htmlspecialchars($project['category']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm whitespace-nowrap">
                                                            <?php
                                                            echo date('M Y', strtotime($project['start_date']));
                                                            echo ' - ';
                                                            echo $project['end_date'] ? date('M Y', strtotime($project['end_date'])) : 'Present';
                                                            ?>
                                                        </td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2">
                                                            <a href="actions/delete_lecturer_item.php?type=project&id=<?php echo $project['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="inline-block bg-red-500 text-white px-2 md:px-3 py-1 rounded text-xs md:text-sm hover:bg-red-600 transition-colors"
                                                                onclick="return confirm('Delete this project?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Education Tab -->
                        <div id="education" class="tab-content hidden">
                            <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                                <div class="bg-gray-50 px-4 md:px-6 py-4 border-b-2 border-gray-200 font-semibold flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-sm md:text-base">
                                    <span class="break-words"><i class="fas fa-list mr-1"></i> Education History</span>
                                    <button class="w-full sm:w-auto bg-green-500 text-white px-3 md:px-4 py-2 rounded text-xs md:text-sm hover:bg-green-600 transition-colors whitespace-nowrap flex-shrink-0" onclick="openModal('addEducationModal')">
                                        <i class="fas fa-plus mr-1"></i> Add Education
                                    </button>
                                </div>
                                <div class="p-4 md:p-6 overflow-x-auto">
                                    <table class="min-w-full border border-gray-300">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Institution</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Degree</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Field</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Period</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($education)): ?>
                                                <tr>
                                                    <td colspan="5" class="border border-gray-300 px-2 md:px-4 py-3 text-center text-xs md:text-sm">No education records added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($education as $edu): ?>
                                                    <tr>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words max-w-xs"><?php echo htmlspecialchars($edu['institution']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words"><?php echo htmlspecialchars($edu['degree']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words"><?php echo htmlspecialchars($edu['field_of_study']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm whitespace-nowrap">
                                                            <?php
                                                            echo date('M Y', strtotime($edu['start_date']));
                                                            echo ' - ';
                                                            echo date('M Y', strtotime($edu['end_date']));
                                                            ?>
                                                        </td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2">
                                                            <a href="actions/delete_lecturer_item.php?type=education&id=<?php echo $edu['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="inline-block bg-red-500 text-white px-2 md:px-3 py-1 rounded text-xs md:text-sm hover:bg-red-600 transition-colors"
                                                                onclick="return confirm('Delete this education record?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Skills Tab -->
                        <div id="skills" class="tab-content hidden">
                            <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                                <div class="bg-gray-50 px-4 md:px-6 py-4 border-b-2 border-gray-200 font-semibold flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-sm md:text-base">
                                    <span class="break-words"><i class="fas fa-list mr-1"></i> Skills</span>
                                    <button class="w-full sm:w-auto bg-green-500 text-white px-3 md:px-4 py-2 rounded text-xs md:text-sm hover:bg-green-600 transition-colors whitespace-nowrap flex-shrink-0" onclick="openModal('addSkillModal')">
                                        <i class="fas fa-plus mr-1"></i> Add Skill
                                    </button>
                                </div>
                                <div class="p-4 md:p-6 overflow-x-auto">
                                    <table class="min-w-full border border-gray-300">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Skill Name</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Category</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Icon</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($skills)): ?>
                                                <tr>
                                                    <td colspan="4" class="border border-gray-300 px-2 md:px-4 py-3 text-center text-xs md:text-sm">No skills added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($skills as $skill): ?>
                                                    <tr>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words max-w-xs"><?php echo htmlspecialchars($skill['skill_name']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words"><?php echo htmlspecialchars($skill['category']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm">
                                                            <?php if (!empty($skill['skill_icon'])): ?>
                                                                <i class="<?php echo htmlspecialchars($skill['skill_icon']); ?>"></i>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2">
                                                            <a href="actions/delete_lecturer_item.php?type=skill&id=<?php echo $skill['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="inline-block bg-red-500 text-white px-2 md:px-3 py-1 rounded text-xs md:text-sm hover:bg-red-600 transition-colors"
                                                                onclick="return confirm('Delete this skill?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Interests Tab -->
                        <div id="interests" class="tab-content hidden">
                            <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                                <div class="bg-gray-50 px-4 md:px-6 py-4 border-b-2 border-gray-200 font-semibold flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-sm md:text-base">
                                    <span class="break-words"><i class="fas fa-list mr-1"></i> Research Interests</span>
                                    <button class="w-full sm:w-auto bg-green-500 text-white px-3 md:px-4 py-2 rounded text-xs md:text-sm hover:bg-green-600 transition-colors whitespace-nowrap flex-shrink-0" onclick="openModal('addInterestModal')">
                                        <i class="fas fa-plus mr-1"></i> Add Interest
                                    </button>
                                </div>
                                <div class="p-4 md:p-6 overflow-x-auto">
                                    <table class="min-w-full border border-gray-300">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Interest</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($interests)): ?>
                                                <tr>
                                                    <td colspan="2" class="border border-gray-300 px-2 md:px-4 py-3 text-center text-xs md:text-sm">No interests added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($interests as $interest): ?>
                                                    <tr>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words"><?php echo nl2br(htmlspecialchars($interest['interest_text'])); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2">
                                                            <a href="actions/delete_lecturer_item.php?type=interest&id=<?php echo $interest['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="inline-block bg-red-500 text-white px-2 md:px-3 py-1 rounded text-xs md:text-sm hover:bg-red-600 transition-colors"
                                                                onclick="return confirm('Delete this interest?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Publications Tab -->
                        <div id="publications" class="tab-content hidden">
                            <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                                <div class="bg-gray-50 px-4 md:px-6 py-4 border-b-2 border-gray-200 font-semibold flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-sm md:text-base">
                                    <span class="break-words"><i class="fas fa-list mr-1"></i> Publications</span>
                                    <button class="w-full sm:w-auto bg-green-500 text-white px-3 md:px-4 py-2 rounded text-xs md:text-sm hover:bg-green-600 transition-colors whitespace-nowrap flex-shrink-0" onclick="openModal('addPublicationModal')">
                                        <i class="fas fa-plus mr-1"></i> Add Publication
                                    </button>
                                </div>
                                <div class="p-4 md:p-6 overflow-x-auto">
                                    <table class="min-w-full border border-gray-300">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Title</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Year</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">URL</th>
                                                <th class="border border-gray-300 px-2 md:px-4 py-2 text-left text-xs md:text-sm">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($publications)): ?>
                                                <tr>
                                                    <td colspan="4" class="border border-gray-300 px-2 md:px-4 py-3 text-center text-xs md:text-sm">No publications added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($publications as $pub): ?>
                                                    <tr>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm break-words max-w-xs"><?php echo htmlspecialchars($pub['title']); ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm"><?php echo $pub['year']; ?></td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2 text-xs md:text-sm">
                                                            <?php if (!empty($pub['url'])): ?>
                                                                <a href="<?php echo htmlspecialchars($pub['url']); ?>" target="_blank" class="text-blue-600 hover:underline break-all">
                                                                    <i class="fas fa-external-link-alt"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="border border-gray-300 px-2 md:px-4 py-2">
                                                            <a href="actions/delete_lecturer_item.php?type=publication&id=<?php echo $pub['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="inline-block bg-red-500 text-white px-2 md:px-3 py-1 rounded text-xs md:text-sm hover:bg-red-600 transition-colors"
                                                                onclick="return confirm('Delete this publication?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Preview Button -->
                    <div class="text-right mb-6">
                        <a href="../index.php?page=lecturer-detail&id=<?php echo $selectedLecturerId; ?>"
                            target="_blank"
                            class="inline-block w-full sm:w-auto bg-cyan-500 text-white px-4 md:px-6 py-2 rounded hover:bg-cyan-600 text-sm md:text-base transition-colors text-center">
                            <i class="fas fa-eye mr-1"></i> Preview Profile
                        </a>
                    </div>

                <?php endif; ?>

            </div><!-- End container -->
        </main>
    </div>

    <!-- Add Project Modal -->
    <div id="addProjectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 md:top-20 mx-auto p-4 md:p-5 border w-11/12 md:w-full max-w-3xl shadow-lg rounded-md bg-white my-4">
            <div class="flex justify-between items-center pb-3 border-b">
                <h5 class="text-lg md:text-xl font-semibold">Add New Project</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl md:text-3xl" onclick="closeModal('addProjectModal')">&times;</button>
            </div>
            <form action="actions/add_lecturer_project.php" method="POST">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="p-3 md:p-4 max-h-[60vh] md:max-h-none overflow-y-auto">
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Project Title *</label>
                        <input type="text" name="title" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Category</label>
                        <input type="text" name="category" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Research, Development, Consulting">
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Description *</label>
                        <textarea name="description" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4" required></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                        <div class="mb-3 md:mb-4">
                            <label class="block mb-2 text-sm md:text-base">Start Date *</label>
                            <input type="date" name="start_date" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="mb-3 md:mb-4">
                            <label class="block mb-2 text-sm md:text-base">End Date (Leave empty if ongoing)</label>
                            <input type="date" name="end_date" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end p-3 md:p-4 border-t gap-2">
                    <button type="button" class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-gray-600 transition-colors" onclick="closeModal('addProjectModal')">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto bg-blue-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-blue-600 transition-colors">Add Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Education Modal -->
    <div id="addEducationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 md:top-20 mx-auto p-4 md:p-5 border w-11/12 md:w-full max-w-3xl shadow-lg rounded-md bg-white my-4">
            <div class="flex justify-between items-center pb-3 border-b">
                <h5 class="text-lg md:text-xl font-semibold">Add Education</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl md:text-3xl" onclick="closeModal('addEducationModal')">&times;</button>
            </div>
            <form action="actions/add_lecturer_education.php" method="POST">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="p-3 md:p-4 max-h-[60vh] md:max-h-none overflow-y-auto">
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Institution *</label>
                        <input type="text" name="institution" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Degree *</label>
                        <input type="text" name="degree" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., PhD, Master, Bachelor" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Field of Study *</label>
                        <input type="text" name="field_of_study" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Description</label>
                        <textarea name="description" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                        <div class="mb-3 md:mb-4">
                            <label class="block mb-2 text-sm md:text-base">Start Date *</label>
                            <input type="date" name="start_date" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="mb-3 md:mb-4">
                            <label class="block mb-2 text-sm md:text-base">End Date *</label>
                            <input type="date" name="end_date" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end p-3 md:p-4 border-t gap-2">
                    <button type="button" class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-gray-600 transition-colors" onclick="closeModal('addEducationModal')">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto bg-blue-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-blue-600 transition-colors">Add Education</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Skill Modal -->
    <div id="addSkillModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 md:top-20 mx-auto p-4 md:p-5 border w-11/12 md:w-full max-w-lg shadow-lg rounded-md bg-white my-4">
            <div class="flex justify-between items-center pb-3 border-b">
                <h5 class="text-lg md:text-xl font-semibold">Add Skill</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl md:text-3xl" onclick="closeModal('addSkillModal')">&times;</button>
            </div>
            <form action="actions/add_lecturer_skill.php" method="POST">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="p-3 md:p-4 max-h-[60vh] md:max-h-none overflow-y-auto">
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Skill Name *</label>
                        <input type="text" name="skill_name" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Category *</label>
                        <input type="text" name="category" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Programming Languages, Tools, Frameworks" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Icon (Font Awesome class)</label>
                        <input type="text" name="skill_icon" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., fab fa-python">
                        <small class="text-gray-600 text-xs md:text-sm">Visit <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-600 underline">Font Awesome</a> for icons</small>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end p-3 md:p-4 border-t gap-2">
                    <button type="button" class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-gray-600 transition-colors" onclick="closeModal('addSkillModal')">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto bg-blue-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-blue-600 transition-colors">Add Skill</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Interest Modal -->
    <div id="addInterestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 md:top-20 mx-auto p-4 md:p-5 border w-11/12 md:w-full max-w-lg shadow-lg rounded-md bg-white my-4">
            <div class="flex justify-between items-center pb-3 border-b">
                <h5 class="text-lg md:text-xl font-semibold">Add Research Interest</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl md:text-3xl" onclick="closeModal('addInterestModal')">&times;</button>
            </div>
            <form action="actions/add_lecturer_interest.php" method="POST">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="p-3 md:p-4 max-h-[60vh] md:max-h-none overflow-y-auto">
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Interest *</label>
                        <textarea name="interest_text" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" required></textarea>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end p-3 md:p-4 border-t gap-2">
                    <button type="button" class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-gray-600 transition-colors" onclick="closeModal('addInterestModal')">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto bg-blue-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-blue-600 transition-colors">Add Interest</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Publication Modal -->
    <div id="addPublicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 md:top-20 mx-auto p-4 md:p-5 border w-11/12 md:w-full max-w-lg shadow-lg rounded-md bg-white my-4">
            <div class="flex justify-between items-center pb-3 border-b">
                <h5 class="text-lg md:text-xl font-semibold">Add Publication</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl md:text-3xl" onclick="closeModal('addPublicationModal')">&times;</button>
            </div>
            <form action="actions/add_lecturer_publication.php" method="POST">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="p-3 md:p-4 max-h-[60vh] md:max-h-none overflow-y-auto">
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Publication Title *</label>
                        <input type="text" name="title" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">Year *</label>
                        <input type="number" name="year" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" min="1900" max="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="mb-3 md:mb-4">
                        <label class="block mb-2 text-sm md:text-base">URL</label>
                        <input type="url" name="url" class="w-full border border-gray-300 rounded px-3 md:px-4 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://">
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end p-3 md:p-4 border-t gap-2">
                    <button type="button" class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-gray-600 transition-colors" onclick="closeModal('addPublicationModal')">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto bg-blue-500 text-white px-4 py-2 rounded text-sm md:text-base hover:bg-blue-600 transition-colors">Add Publication</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>

</html>