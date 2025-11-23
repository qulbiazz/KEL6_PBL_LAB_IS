<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'InLet Admin Panel'; ?></title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="flex-1 p-4 md:p-8 w-full lg:w-auto min-h-screen lg:ml-64">
