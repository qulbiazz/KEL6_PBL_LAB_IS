<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - InLet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-4">About InLet</h1>
                <p class="text-xl">Information and Learning Engineering Technology</p>
            </div>
        </div>
    </section>

    <!-- Vision & Mission -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="bg-blue-50 p-8 rounded-lg">
                    <div class="text-4xl text-blue-600 mb-4">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Vision</h2>
                    <p class="text-gray-600 leading-relaxed">
                        To become a leading research and development center in information and learning engineering technology, 
                        driving innovation and excellence in education technology solutions that transform the way people learn and interact with information.
                    </p>
                </div>
                <div class="bg-green-50 p-8 rounded-lg">
                    <div class="text-4xl text-green-600 mb-4">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Mission</h2>
                    <ul class="text-gray-600 leading-relaxed space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            <span>Develop innovative learning technologies and information systems</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            <span>Conduct cutting-edge research in educational technology</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            <span>Foster collaboration with industry and academic partners</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            <span>Create impactful solutions for modern learning challenges</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Who We Are</h2>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        InLet (Information and Learning Engineering Technology) is a dynamic research laboratory dedicated to advancing 
                        the field of educational technology and information systems. Founded with the vision of transforming learning 
                        experiences through technology, we bring together researchers, developers, and educators to create innovative solutions.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Our team consists of experienced professionals with diverse backgrounds in computer science, education, 
                        and engineering. We work on various projects ranging from learning management systems to artificial intelligence 
                        applications in education.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Through our research activities, product development, and industry partnerships, we aim to make a significant 
                        impact on how technology is used in education and information management.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Our Focus Areas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Educational Technology</h3>
                                <p class="text-gray-600 text-sm">Developing innovative learning platforms and tools</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-robot text-green-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Artificial Intelligence</h3>
                                <p class="text-gray-600 text-sm">AI applications in education and information systems</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-purple-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-database text-purple-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Information Systems</h3>
                                <p class="text-gray-600 text-sm">Building robust and scalable information management solutions</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-orange-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-mobile-alt text-orange-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Mobile Applications</h3>
                                <p class="text-gray-600 text-sm">Creating mobile-first learning experiences</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-5xl font-bold mb-2">50+</div>
                    <p class="text-blue-100">Research Projects</p>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">30+</div>
                    <p class="text-blue-100">Team Members</p>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">100+</div>
                    <p class="text-blue-100">Publications</p>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">20+</div>
                    <p class="text-blue-100">Industry Partners</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
