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
$query = "SELECT id, name, position, photo FROM team_members ORDER BY name";
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

$pageTitle = 'Team Details';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        
        .tab-active {
            border-bottom: 2px solid #3b82f6;
            color: #2563eb;
            font-weight: 600;
        }
        
        .tab-inactive {
            color: #6b7280;
            border-bottom: 2px solid transparent;
        }
        
        .tab-inactive:hover {
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }

        /* Custom scrollbar for tab navigation */
        .tab-scroll::-webkit-scrollbar {
            height: 0px;
            background: transparent;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-6 lg:ml-64 min-h-screen">
            <div class="max-w-7xl mx-auto">
                
                <!-- Header Section -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Team Details</h1>
                        <p class="text-gray-500 text-sm mt-1">Manage detailed profiles for team members.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="team.php" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Team
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm mb-6 flex justify-between items-center">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-xl mr-3"></i>
                            <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm mb-6 flex justify-between items-center">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Selection Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <form method="GET" action="" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Team Member</label>
                            <div class="relative">
                                <select name="lecturer_id" onchange="this.form.submit()" class="w-full appearance-none bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 pr-8">
                                    <option value="">-- Choose a member to view details --</option>
                                    <?php foreach ($teamMembers as $member): ?>
                                        <option value="<?php echo $member['id']; ?>" <?php echo ($selectedLecturerId == $member['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($member['name']); ?> - <?php echo htmlspecialchars($member['position']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-auto">
                             <a href="team.php" class="block w-full md:w-auto text-center bg-gray-100 text-gray-700 px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                                Manage Members
                            </a>
                        </div>
                    </form>
                </div>

                <?php if ($lecturer): ?>
                    <!-- Profile Header -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                        <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-700"></div>
                        <div class="px-6 pb-6">
                            <div class="relative flex items-end -mt-12 mb-4">
                                <div class="bg-white p-1 rounded-full shadow-lg">
                                    <?php if (!empty($lecturer['photo'])): ?>
                                        <img src="../uploads/team/<?php echo htmlspecialchars($lecturer['photo']); ?>" alt="<?php echo htmlspecialchars($lecturer['name']); ?>" class="w-24 h-24 rounded-full object-cover border-4 border-white">
                                    <?php else: ?>
                                        <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center border-4 border-white text-gray-400">
                                            <i class="fas fa-user text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4 mb-1">
                                    <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($lecturer['name']); ?></h2>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($lecturer['position']); ?></p>
                                </div>
                                <div class="ml-auto mb-2 hidden md:block">
                                    <a href="../index.php?page=lecturer-detail&id=<?php echo $selectedLecturerId; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                        <i class="fas fa-external-link-alt mr-1"></i> View Public Profile
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Social Links -->
                            <div class="flex flex-wrap gap-3 mt-4">
                                <?php if (!empty($lecturer['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($lecturer['email']); ?>" class="text-gray-500 hover:text-blue-600 transition-colors"><i class="fas fa-envelope text-lg"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($lecturer['linkedin_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($lecturer['linkedin_url']); ?>" target="_blank" class="text-gray-500 hover:text-blue-700 transition-colors"><i class="fab fa-linkedin text-lg"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($lecturer['google_scholar_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($lecturer['google_scholar_url']); ?>" target="_blank" class="text-gray-500 hover:text-blue-600 transition-colors"><i class="fas fa-graduation-cap text-lg"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
                        <div class="overflow-x-auto tab-scroll">
                            <nav class="flex border-b border-gray-200" id="lecturerTabs">
                                <button class="flex-1 min-w-[100px] py-4 px-6 text-sm font-medium text-center transition-colors tab-active focus:outline-none" onclick="showTab('basic', this)">
                                    Basic Info
                                </button>
                                <button class="flex-1 min-w-[100px] py-4 px-6 text-sm font-medium text-center transition-colors tab-inactive focus:outline-none" onclick="showTab('projects', this)">
                                    Projects <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs"><?php echo count($projects); ?></span>
                                </button>
                                <button class="flex-1 min-w-[100px] py-4 px-6 text-sm font-medium text-center transition-colors tab-inactive focus:outline-none" onclick="showTab('education', this)">
                                    Education <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs"><?php echo count($education); ?></span>
                                </button>
                                <button class="flex-1 min-w-[100px] py-4 px-6 text-sm font-medium text-center transition-colors tab-inactive focus:outline-none" onclick="showTab('skills', this)">
                                    Skills <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs"><?php echo count($skills); ?></span>
                                </button>
                                <button class="flex-1 min-w-[100px] py-4 px-6 text-sm font-medium text-center transition-colors tab-inactive focus:outline-none" onclick="showTab('interests', this)">
                                    Interests <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs"><?php echo count($interests); ?></span>
                                </button>
                                <button class="flex-1 min-w-[100px] py-4 px-6 text-sm font-medium text-center transition-colors tab-inactive focus:outline-none" onclick="showTab('publications', this)">
                                    Publications <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs"><?php echo count($publications); ?></span>
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Tab Contents -->
                    <div id="lecturerTabsContent">
                        
                        <!-- Basic Info -->
                        <div id="basic" class="tab-content block">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-bold text-gray-900">Basic Information</h3>
                                </div>
                                
                                <form action="actions/update_lecturer_basic.php" method="POST">
                                    <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                            <input type="text" name="address" value="<?php echo htmlspecialchars($lecturer['address'] ?? ''); ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                            <input type="text" name="phone" value="<?php echo htmlspecialchars($lecturer['phone'] ?? ''); ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($lecturer['email'] ?? ''); ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Biography</label>
                                            <textarea name="bio" rows="4" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"><?php echo htmlspecialchars($lecturer['bio'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Social Profiles</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">LinkedIn URL</label>
                                            <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($lecturer['linkedin_url'] ?? ''); ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Google Scholar URL</label>
                                            <input type="url" name="google_scholar_url" value="<?php echo htmlspecialchars($lecturer['google_scholar_url'] ?? ''); ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">ResearchGate URL</label>
                                            <input type="url" name="researchgate_url" value="<?php echo htmlspecialchars($lecturer['researchgate_url'] ?? ''); ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">ORCID URL</label>
                                            <input type="url" name="orcid_url" value="<?php echo htmlspecialchars($lecturer['orcid_url'] ?? ''); ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Projects -->
                        <div id="projects" class="tab-content hidden">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-bold text-gray-900">Projects</h3>
                                    <button onclick="openModal('addProjectModal')" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                                        <i class="fas fa-plus mr-2"></i> Add Project
                                    </button>
                                </div>
                                
                                <?php if (empty($projects)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                        <p>No projects added yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left text-gray-500">
                                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3">Title</th>
                                                    <th scope="col" class="px-6 py-3">Category</th>
                                                    <th scope="col" class="px-6 py-3">Period</th>
                                                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($projects as $project): ?>
                                                    <tr class="bg-white border-b hover:bg-gray-50">
                                                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($project['title']); ?></td>
                                                        <td class="px-6 py-4"><?php echo htmlspecialchars($project['category']); ?></td>
                                                        <td class="px-6 py-4">
                                                            <?php echo date('M Y', strtotime($project['start_date'])); ?> - 
                                                            <?php echo $project['end_date'] ? date('M Y', strtotime($project['end_date'])) : 'Present'; ?>
                                                        </td>
                                                        <td class="px-6 py-4 text-right">
                                                            <a href="actions/delete_lecturer_item.php?type=project&id=<?php echo $project['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>" 
                                                               onclick="return confirm('Delete this project?')" 
                                                               class="font-medium text-red-600 hover:underline">Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Education -->
                        <div id="education" class="tab-content hidden">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-bold text-gray-900">Education</h3>
                                    <button onclick="openModal('addEducationModal')" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                                        <i class="fas fa-plus mr-2"></i> Add Education
                                    </button>
                                </div>

                                <?php if (empty($education)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-graduation-cap text-4xl mb-3 text-gray-300"></i>
                                        <p>No education history added yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($education as $edu): ?>
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                                <div>
                                                    <h4 class="text-base font-semibold text-gray-900"><?php echo htmlspecialchars($edu['institution']); ?></h4>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($edu['degree']); ?> in <?php echo htmlspecialchars($edu['field_of_study']); ?></p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        <?php echo date('M Y', strtotime($edu['start_date'])); ?> - 
                                                        <?php echo date('M Y', strtotime($edu['end_date'])); ?>
                                                    </p>
                                                </div>
                                                <div class="mt-3 sm:mt-0">
                                                    <a href="actions/delete_lecturer_item.php?type=education&id=<?php echo $edu['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>" 
                                                       onclick="return confirm('Delete this record?')" 
                                                       class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                        <i class="fas fa-trash mr-1"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div id="skills" class="tab-content hidden">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-bold text-gray-900">Skills</h3>
                                    <button onclick="openModal('addSkillModal')" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                                        <i class="fas fa-plus mr-2"></i> Add Skill
                                    </button>
                                </div>

                                <?php if (empty($skills)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-code text-4xl mb-3 text-gray-300"></i>
                                        <p>No skills added yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                        <?php foreach ($skills as $skill): ?>
                                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow">
                                                <div class="flex items-center">
                                                    <?php if (!empty($skill['skill_icon'])): ?>
                                                        <i class="<?php echo htmlspecialchars($skill['skill_icon']); ?> text-blue-500 text-xl mr-3 w-6 text-center"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-check-circle text-blue-500 text-xl mr-3 w-6 text-center"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($skill['skill_name']); ?></p>
                                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($skill['category']); ?></p>
                                                    </div>
                                                </div>
                                                <a href="actions/delete_lecturer_item.php?type=skill&id=<?php echo $skill['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>" 
                                                   onclick="return confirm('Delete this skill?')" 
                                                   class="text-gray-400 hover:text-red-500">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Interests -->
                        <div id="interests" class="tab-content hidden">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-bold text-gray-900">Research Interests</h3>
                                    <button onclick="openModal('addInterestModal')" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                                        <i class="fas fa-plus mr-2"></i> Add Interest
                                    </button>
                                </div>

                                <?php if (empty($interests)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-lightbulb text-4xl mb-3 text-gray-300"></i>
                                        <p>No interests added yet.</p>
                                    </div>
                                <?php else: ?>
                                    <ul class="space-y-2">
                                        <?php foreach ($interests as $interest): ?>
                                            <li class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-start">
                                                    <i class="fas fa-dot-circle text-blue-500 mt-1 mr-3 text-xs"></i>
                                                    <span class="text-gray-700"><?php echo nl2br(htmlspecialchars($interest['interest_text'])); ?></span>
                                                </div>
                                                <a href="actions/delete_lecturer_item.php?type=interest&id=<?php echo $interest['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>" 
                                                   onclick="return confirm('Delete this interest?')" 
                                                   class="text-gray-400 hover:text-red-500 ml-2">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Publications -->
                        <div id="publications" class="tab-content hidden">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-bold text-gray-900">Publications</h3>
                                    <button onclick="openModal('addPublicationModal')" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                                        <i class="fas fa-plus mr-2"></i> Add Publication
                                    </button>
                                </div>

                                <?php if (empty($publications)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-book text-4xl mb-3 text-gray-300"></i>
                                        <p>No publications added yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($publications as $pub): ?>
                                            <div class="flex flex-col sm:flex-row sm:items-start justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                                <div class="flex-1 pr-4">
                                                    <h4 class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($pub['title']); ?></h4>
                                                    <div class="flex items-center mt-1 text-sm text-gray-500">
                                                        <span class="bg-gray-100 px-2 py-0.5 rounded text-xs font-semibold text-gray-600 mr-2"><?php echo $pub['year']; ?></span>
                                                        <?php if (!empty($pub['url'])): ?>
                                                            <a href="<?php echo htmlspecialchars($pub['url']); ?>" target="_blank" class="text-blue-600 hover:underline flex items-center">
                                                                View <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="mt-2 sm:mt-0 flex-shrink-0">
                                                    <a href="actions/delete_lecturer_item.php?type=publication&id=<?php echo $pub['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>" 
                                                       onclick="return confirm('Delete this publication?')" 
                                                       class="text-red-600 hover:text-red-800 text-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <!-- Modals (Keep existing structure but style them) -->
    <!-- Add Project Modal -->
    <div id="addProjectModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-xl font-bold text-gray-900">Add New Project</h3>
                <button onclick="closeModal('addProjectModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="actions/add_lecturer_project.php" method="POST" class="mt-4">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project Title *</label>
                        <input type="text" name="title" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input type="text" name="category" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" rows="3" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                            <input type="date" name="start_date" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal('addProjectModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Add Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Education Modal -->
    <div id="addEducationModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-xl font-bold text-gray-900">Add Education</h3>
                <button onclick="closeModal('addEducationModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="actions/add_lecturer_education.php" method="POST" class="mt-4">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Institution *</label>
                        <input type="text" name="institution" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Degree *</label>
                            <input type="text" name="degree" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Field of Study *</label>
                            <input type="text" name="field_of_study" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                            <input type="date" name="start_date" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                            <input type="date" name="end_date" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal('addEducationModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Add Education</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Skill Modal -->
    <div id="addSkillModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-xl font-bold text-gray-900">Add Skill</h3>
                <button onclick="closeModal('addSkillModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="actions/add_lecturer_skill.php" method="POST" class="mt-4">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Skill Name *</label>
                        <input type="text" name="skill_name" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <input type="text" name="category" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" placeholder="e.g. Programming">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon Class</label>
                        <input type="text" name="skill_icon" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" placeholder="fab fa-python">
                        <p class="text-xs text-gray-500 mt-1">Font Awesome class name</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal('addSkillModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Add Skill</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Interest Modal -->
    <div id="addInterestModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-xl font-bold text-gray-900">Add Interest</h3>
                <button onclick="closeModal('addInterestModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="actions/add_lecturer_interest.php" method="POST" class="mt-4">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interest *</label>
                        <textarea name="interest_text" rows="3" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal('addInterestModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Add Interest</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Publication Modal -->
    <div id="addPublicationModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-5 border w-full max-w-lg shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-xl font-bold text-gray-900">Add Publication</h3>
                <button onclick="closeModal('addPublicationModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="actions/add_lecturer_publication.php" method="POST" class="mt-4">
                <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                            <input type="number" name="year" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                            <input type="url" name="url" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal('addPublicationModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Add Publication</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        function showTab(tabId, btn) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('block');
            
            // Update buttons
            document.querySelectorAll('#lecturerTabs button').forEach(b => {
                b.classList.remove('tab-active');
                b.classList.add('tab-inactive');
            });
            
            btn.classList.remove('tab-inactive');
            btn.classList.add('tab-active');
        }

        // Open modal helper
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>