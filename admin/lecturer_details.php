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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        /* Navbar Styles */
        .admin-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }
        
        /* Sidebar Styles */
        .admin-sidebar {
            background-color: #212529;
            min-height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
        }
        
        .admin-sidebar .nav-link {
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: background-color 0.3s;
        }
        
        .admin-sidebar .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }
        
        .admin-sidebar .nav-link.active {
            background-color: #495057;
        }
        
        .admin-sidebar .sidebar-heading {
            color: #fff;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background: #fff;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            background-color: #fff;
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }

        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }

        .table-actions {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg admin-navbar">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">InLet Admin Panel</span>
            <div class="d-flex align-items-center">
                <a href="../index.php" target="_blank" class="btn btn-link text-decoration-none me-3">
                    <i class="fas fa-external-link-alt me-1"></i> View Site
                </a>
                <span class="me-3">
                    <i class="fas fa-user-circle me-1"></i>
                    Administrator
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="d-flex">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-heading">
                <i class="fas fa-cog me-2"></i>Content Management
            </div>
            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="banners.php" class="nav-link">
                    <i class="fas fa-images me-2"></i> Carousel Banners
                </a>
                <a href="cards.php" class="nav-link">
                    <i class="fas fa-th-large me-2"></i> Card Data
                </a>
                <a href="products.php" class="nav-link">
                    <i class="fas fa-box me-2"></i> Products
                </a>
                <a href="partners.php" class="nav-link">
                    <i class="fas fa-handshake me-2"></i> Partners
                </a>
                <a href="team.php" class="nav-link">
                    <i class="fas fa-users me-2"></i> Team Members
                </a>
                <a href="lecturer_details.php" class="nav-link active">
                    <i class="fas fa-user-tie me-2"></i> Lecturer Details
                </a>
                <a href="news.php" class="nav-link">
                    <i class="fas fa-newspaper me-2"></i> News
                </a>
                <a href="gallery.php" class="nav-link">
                    <i class="fas fa-photo-video me-2"></i> Gallery
                </a>
                <a href="research.php" class="nav-link">
                    <i class="fas fa-flask me-2"></i> Research Activities
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow-1 p-4 main-content" style="overflow-x: auto;">

            <div class="container-fluid">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success'];
                                                                unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error'];
                                                                        unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-2">Manage Lecturer Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Lecturer Details</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <!-- Select Lecturer -->
                <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-tie me-2"></i>
                    Select Lecturer
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-bold">Choose Lecturer</label>
                            <select name="lecturer_id" class="form-select form-select-lg" required>
                                <option value="">-- Select Lecturer --</option>
                                <?php foreach ($teamMembers as $member): ?>
                                    <option value="<?php echo $member['id']; ?>" <?php echo ($selectedLecturerId == $member['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($member['name']); ?> - <?php echo htmlspecialchars($member['position']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-search me-2"></i> Load Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($lecturer): ?>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="lecturerTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i> Basic Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab">
                            <i class="fas fa-project-diagram me-2"></i> Projects (<?php echo count($projects); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab">
                            <i class="fas fa-graduation-cap me-2"></i> Education (<?php echo count($education); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab">
                            <i class="fas fa-code me-2"></i> Skills (<?php echo count($skills); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="interests-tab" data-bs-toggle="tab" data-bs-target="#interests" type="button" role="tab">
                            <i class="fas fa-heart me-2"></i> Interests (<?php echo count($interests); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="publications-tab" data-bs-toggle="tab" data-bs-target="#publications" type="button" role="tab">
                            <i class="fas fa-file-alt me-2"></i> Publications (<?php echo count($publications); ?>)
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="lecturerTabsContent">

                    <!-- Basic Info Tab -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-edit me-2"></i>
                                Update Basic Information
                            </div>
                            <div class="card-body">
                                <!-- Info Alert -->
                                <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                                    <i class="fas fa-info-circle me-3 mt-1"></i>
                                    <div>
                                        <strong>Note:</strong> To edit <strong>Name</strong>, <strong>Position</strong>, and <strong>Profile Photo</strong>, please go to
                                        <a href="team.php" class="alert-link fw-bold">Team Members</a> page.
                                    </div>
                                </div>

                                <form action="actions/update_lecturer_basic.php" method="POST">
                                    <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">

                                    <!-- Display Current Name & Position (Read-only) -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3 mb-md-0">
                                                            <label class="form-label fw-bold text-muted small">Current Name</label>
                                                            <p class="mb-0 fs-5"><?php echo htmlspecialchars($lecturer['name']); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold text-muted small">Current Position</label>
                                                            <p class="mb-0 fs-5"><?php echo htmlspecialchars($lecturer['position']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($lecturer['address'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($lecturer['phone'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($lecturer['email'] ?? ''); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Biography</label>
                                        <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($lecturer['bio'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">LinkedIn URL</label>
                                            <input type="url" name="linkedin_url" class="form-control" value="<?php echo htmlspecialchars($lecturer['linkedin_url'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Google Scholar URL</label>
                                            <input type="url" name="google_scholar_url" class="form-control" value="<?php echo htmlspecialchars($lecturer['google_scholar_url'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">ResearchGate URL</label>
                                            <input type="url" name="researchgate_url" class="form-control" value="<?php echo htmlspecialchars($lecturer['researchgate_url'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ORCID URL</label>
                                            <input type="url" name="orcid_url" class="form-control" value="<?php echo htmlspecialchars($lecturer['orcid_url'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Tab -->
                    <div class="tab-pane fade" id="projects" role="tabpanel">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-list me-1"></i> Work in Progress / Projects</span>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                    <i class="fas fa-plus me-1"></i> Add Project
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Period</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($projects)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No projects added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($projects as $project): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                        <td><?php echo htmlspecialchars($project['category']); ?></td>
                                                        <td>
                                                            <?php
                                                            echo date('M Y', strtotime($project['start_date']));
                                                            echo ' - ';
                                                            echo $project['end_date'] ? date('M Y', strtotime($project['end_date'])) : 'Present';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <!-- <button class="btn btn-sm btn-warning edit-project"
                                                                data-id="<?php echo $project['id']; ?>"
                                                                data-title="<?php echo htmlspecialchars($project['title']); ?>"
                                                                data-category="<?php echo htmlspecialchars($project['category']); ?>"
                                                                data-description="<?php echo htmlspecialchars($project['description']); ?>"
                                                                data-start="<?php echo $project['start_date']; ?>"
                                                                data-end="<?php echo $project['end_date']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button> -->
                                                            <a href="actions/delete_lecturer_item.php?type=project&id=<?php echo $project['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="btn btn-sm btn-danger"
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
                    </div>

                    <!-- Education Tab -->
                    <div class="tab-pane fade" id="education" role="tabpanel">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-list me-1"></i> Education History</span>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                    <i class="fas fa-plus me-1"></i> Add Education
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Institution</th>
                                                <th>Degree</th>
                                                <th>Field</th>
                                                <th>Period</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($education)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No education records added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($education as $edu): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($edu['institution']); ?></td>
                                                        <td><?php echo htmlspecialchars($edu['degree']); ?></td>
                                                        <td><?php echo htmlspecialchars($edu['field_of_study']); ?></td>
                                                        <td>
                                                            <?php
                                                            echo date('M Y', strtotime($edu['start_date']));
                                                            echo ' - ';
                                                            echo date('M Y', strtotime($edu['end_date']));
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <a href="actions/delete_lecturer_item.php?type=education&id=<?php echo $edu['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="btn btn-sm btn-danger"
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
                    </div>

                    <!-- Skills Tab -->
                    <div class="tab-pane fade" id="skills" role="tabpanel">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-list me-1"></i> Skills</span>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                                    <i class="fas fa-plus me-1"></i> Add Skill
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Skill Name</th>
                                                <th>Category</th>
                                                <th>Icon</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($skills)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No skills added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($skills as $skill): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($skill['skill_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($skill['category']); ?></td>
                                                        <td>
                                                            <?php if (!empty($skill['skill_icon'])): ?>
                                                                <i class="<?php echo htmlspecialchars($skill['skill_icon']); ?>"></i>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="actions/delete_lecturer_item.php?type=skill&id=<?php echo $skill['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="btn btn-sm btn-danger"
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
                    </div>

                    <!-- Interests Tab -->
                    <div class="tab-pane fade" id="interests" role="tabpanel">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-list me-1"></i> Research Interests</span>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addInterestModal">
                                    <i class="fas fa-plus me-1"></i> Add Interest
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Interest</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($interests)): ?>
                                                <tr>
                                                    <td colspan="2" class="text-center">No interests added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($interests as $interest): ?>
                                                    <tr>
                                                        <td><?php echo nl2br(htmlspecialchars($interest['interest_text'])); ?></td>
                                                        <td>
                                                            <a href="actions/delete_lecturer_item.php?type=interest&id=<?php echo $interest['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="btn btn-sm btn-danger"
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
                    </div>

                    <!-- Publications Tab -->
                    <div class="tab-pane fade" id="publications" role="tabpanel">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-list me-1"></i> Publications</span>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addPublicationModal">
                                    <i class="fas fa-plus me-1"></i> Add Publication
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Year</th>
                                                <th>URL</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($publications)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No publications added yet</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($publications as $pub): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($pub['title']); ?></td>
                                                        <td><?php echo $pub['year']; ?></td>
                                                        <td>
                                                            <?php if (!empty($pub['url'])): ?>
                                                                <a href="<?php echo htmlspecialchars($pub['url']); ?>" target="_blank">
                                                                    <i class="fas fa-external-link-alt"></i> View
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="actions/delete_lecturer_item.php?type=publication&id=<?php echo $pub['id']; ?>&lecturer_id=<?php echo $selectedLecturerId; ?>"
                                                                class="btn btn-sm btn-danger"
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

                </div>

                <!-- Preview Button -->
                <div class="text-end mb-4">
                    <a href="../index.php?page=lecturer-detail&id=<?php echo $selectedLecturerId; ?>"
                        target="_blank"
                        class="btn btn-info">
                        <i class="fas fa-eye me-1"></i> Preview Profile
                    </a>
                </div>

            <?php endif; ?>
            
            </div><!-- End container-fluid -->
        </main>
    </div>

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/add_lecturer_project.php" method="POST">
                    <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Project Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g., Research, Development, Consulting">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date (Leave empty if ongoing)</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Education Modal -->
    <div class="modal fade" id="addEducationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Education</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/add_lecturer_education.php" method="POST">
                    <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Institution *</label>
                            <input type="text" name="institution" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Degree *</label>
                            <input type="text" name="degree" class="form-control" placeholder="e.g., PhD, Master, Bachelor" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Field of Study *</label>
                            <input type="text" name="field_of_study" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date *</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Education</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Skill Modal -->
    <div class="modal fade" id="addSkillModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/add_lecturer_skill.php" method="POST">
                    <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Skill Name *</label>
                            <input type="text" name="skill_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g., Programming Languages, Tools, Frameworks" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (Font Awesome class)</label>
                            <input type="text" name="skill_icon" class="form-control" placeholder="e.g., fab fa-python">
                            <small class="text-muted">Visit <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a> for icons</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Skill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Interest Modal -->
    <div class="modal fade" id="addInterestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Research Interest</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/add_lecturer_interest.php" method="POST">
                    <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Interest *</label>
                            <textarea name="interest_text" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Interest</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Publication Modal -->
    <div class="modal fade" id="addPublicationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Publication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions/add_lecturer_publication.php" method="POST">
                    <input type="hidden" name="lecturer_id" value="<?php echo $selectedLecturerId; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Publication Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Year *</label>
                            <input type="number" name="year" class="form-control" min="1900" max="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="url" name="url" class="form-control" placeholder="https://">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Publication</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>