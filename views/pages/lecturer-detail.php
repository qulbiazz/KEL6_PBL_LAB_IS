<div class="min-h-screen bg-gray-50">
    <!-- Hero Section dengan Profile -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <div class="flex flex-col md:flex-row items-center md:items-start md:justify-between gap-8">
                <!-- Profile Image (40%) -->
                <div class="md:w-2/5 flex justify-center md:justify-start order-1 md:order-1">
                    <?php
                    // Get image path with fallback to default avatar
                    $imagePath = !empty($lecturer['photo']) ? 'uploads/team/' . $lecturer['photo'] : 'assets/images/default-avatar.png';
                    $imageName = htmlspecialchars($lecturer['name'] ?? 'Lecturer');
                    ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>"
                        alt="<?php echo $imageName; ?>"
                        class="w-48 h-48 md:w-64 md:h-64 lg:w-80 lg:h-80 rounded-full object-cover border-8 border-white shadow-2xl"
                        onerror="this.src='assets/images/default-avatar.png'">
                </div>

                <!-- Profile Info (60%) -->
                <div class="md:w-3/5 flex flex-col text-center md:text-left md:items-end order-2 md:order-2">
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-2">
                        <?php
                        $nameParts = explode(' ', $lecturer['name']);
                        $lastName = array_pop($nameParts);
                        echo implode(' ', $nameParts) . ' <span class="text-blue-300">' . htmlspecialchars($lastName) . '</span>';
                        ?>
                    </h1>

                    <?php if (!empty($lecturer['address'])): ?>
                        <p class="text-blue-100 mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <?php echo htmlspecialchars($lecturer['address']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($lecturer['phone'])): ?>
                        <p class="text-blue-100 mb-2">
                            <i class="fas fa-phone mr-2"></i>
                            <?php echo htmlspecialchars($lecturer['phone']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($lecturer['email'])): ?>
                        <p class="text-blue-100 mb-4">
                            <i class="fas fa-envelope mr-2"></i>
                            <a href="mailto:<?php echo htmlspecialchars($lecturer['email']); ?>"
                                class="hover:text-white transition">
                                <?php echo htmlspecialchars($lecturer['email']); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <!-- Bio -->
                    <?php if (!empty($lecturer['bio'])): ?>
                        <p class="text-lg text-blue-50 leading-relaxed mb-6">
                            <?php echo nl2br(htmlspecialchars($lecturer['bio'])); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Social Links -->
                    <div class="flex gap-4 justify-center md:justify-end">
                        <?php if (!empty($lecturer['linkedin_url'])): ?>
                            <a href="<?php echo htmlspecialchars($lecturer['linkedin_url']); ?>"
                                target="_blank"
                                class="w-12 h-12 bg-white text-blue-600 rounded-full flex items-center justify-center hover:bg-blue-100 transition">
                                <i class="fab fa-linkedin text-xl"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($lecturer['google_scholar_url'])): ?>
                            <a href="<?php echo htmlspecialchars($lecturer['google_scholar_url']); ?>"
                                target="_blank"
                                class="w-12 h-12 bg-white text-blue-600 rounded-full flex items-center justify-center hover:bg-blue-100 transition">
                                <i class="fas fa-graduation-cap text-xl"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($lecturer['researchgate_url'])): ?>
                            <a href="<?php echo htmlspecialchars($lecturer['researchgate_url']); ?>"
                                target="_blank"
                                class="w-12 h-12 bg-white text-blue-600 rounded-full flex items-center justify-center hover:bg-blue-100 transition">
                                <i class="fab fa-researchgate text-xl"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($lecturer['orcid_url'])): ?>
                            <a href="<?php echo htmlspecialchars($lecturer['orcid_url']); ?>"
                                target="_blank"
                                class="w-12 h-12 bg-white text-blue-600 rounded-full flex items-center justify-center hover:bg-blue-100 transition">
                                <i class="fab fa-orcid text-xl"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation Tabs -->
    <div class="bg-white shadow-md sticky top-0 z-30">
        <div class="container mx-auto px-6 md:px-8 lg:px-12">
            <nav class="flex gap-8 overflow-x-auto custom-scrollbar">
                <a href="#about" class="nav-tab active py-4 px-2 border-b-4 border-blue-600 text-blue-600 font-semibold whitespace-nowrap">
                    ABOUT
                </a>
                <?php if (!empty($lecturer['projects'])): ?>
                    <a href="#projects" class="nav-tab py-4 px-2 border-b-4 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition whitespace-nowrap">
                        WORK IN PROGRESS
                    </a>
                <?php endif; ?>
                <?php if (!empty($lecturer['education'])): ?>
                    <a href="#education" class="nav-tab py-4 px-2 border-b-4 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition whitespace-nowrap">
                        EDUCATION
                    </a>
                <?php endif; ?>
                <?php if (!empty($lecturer['skills'])): ?>
                    <a href="#skills" class="nav-tab py-4 px-2 border-b-4 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition whitespace-nowrap">
                        SKILLS
                    </a>
                <?php endif; ?>
                <?php if (!empty($lecturer['interests'])): ?>
                    <a href="#interests" class="nav-tab py-4 px-2 border-b-4 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition whitespace-nowrap">
                        INTERESTS
                    </a>
                <?php endif; ?>
                <?php if (!empty($lecturer['publications'])): ?>
                    <a href="#publications" class="nav-tab py-4 px-2 border-b-4 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition whitespace-nowrap">
                        PUBLISHED ARTICLES
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 4px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Firefox */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f1f1;
        }
    </style>

    <div class="container mx-auto px-6 md:px-8 lg:px-12 py-12">
        <!-- About Section -->
        <section id="about" class="mb-16 scroll-mt-24">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">About</h2>
            </div>
            <div class="bg-white rounded-lg shadow-md p-8">
                <p class="text-gray-700 text-lg leading-relaxed">
                    <?php echo !empty($lecturer['bio']) ? nl2br(htmlspecialchars($lecturer['bio'])) : 'No biography available.'; ?>
                </p>
            </div>
        </section>

        <!-- Work in Progress Section -->
        <?php if (!empty($lecturer['projects'])): ?>
            <section id="projects" class="mb-16 scroll-mt-24">
                <h2 class="text-4xl font-bold text-gray-800 mb-8">WORK IN PROGRESS</h2>

                <!-- Filter Dropdown -->
                <div class="mb-6">
                    <select id="projectFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Select a filter</option>
                        <?php
                        $categories = array_unique(array_column($lecturer['projects'], 'category'));
                        foreach ($categories as $category):
                            if (!empty($category)):
                        ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </select>
                </div>

                <div class="space-y-6">
                    <?php foreach ($lecturer['projects'] as $project): ?>
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition project-item"
                            data-category="<?php echo htmlspecialchars($project['category']); ?>">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($project['title']); ?></h3>
                                <span class="text-blue-600 font-semibold">
                                    <?php
                                    if ($project['end_date']) {
                                        echo date('F Y', strtotime($project['start_date'])) . ' - ' . date('F Y', strtotime($project['end_date']));
                                    } else {
                                        echo date('F Y', strtotime($project['start_date'])) . ' - Present';
                                    }
                                    ?>
                                </span>
                            </div>

                            <?php if (!empty($project['category'])): ?>
                                <p class="text-sm text-gray-600 mb-3 uppercase font-semibold tracking-wide">
                                    <?php echo htmlspecialchars($project['category']); ?>
                                </p>
                            <?php endif; ?>

                            <p class="text-gray-700 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Education Section -->
        <?php if (!empty($lecturer['education'])): ?>
            <section id="education" class="mb-16 scroll-mt-24">
                <h2 class="text-4xl font-bold text-gray-800 mb-8">EDUCATION</h2>

                <div class="space-y-8">
                    <?php foreach ($lecturer['education'] as $edu): ?>
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                                        <?php echo htmlspecialchars($edu['institution']); ?>
                                    </h3>
                                    <p class="text-lg text-gray-700 font-semibold uppercase tracking-wide">
                                        <?php echo htmlspecialchars($edu['degree']); ?>
                                    </p>
                                </div>
                                <span class="text-blue-600 font-semibold whitespace-nowrap ml-4">
                                    <?php
                                    echo date('F Y', strtotime($edu['start_date'])) . ' - ' . date('F Y', strtotime($edu['end_date']));
                                    ?>
                                </span>
                            </div>

                            <?php if (!empty($edu['field_of_study'])): ?>
                                <p class="text-gray-600 mb-3">
                                    <?php echo htmlspecialchars($edu['field_of_study']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($edu['description'])): ?>
                                <p class="text-gray-700 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($edu['description'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Skills Section -->
        <?php if (!empty($lecturer['skills'])): ?>
            <section id="skills" class="mb-16 scroll-mt-24">
                <h2 class="text-4xl font-bold text-gray-800 mb-8">SKILLS</h2>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <?php foreach ($lecturer['skills'] as $category => $skills): ?>
                        <div class="mb-8 last:mb-0">
                            <h3 class="text-xl font-bold text-gray-700 mb-4 uppercase tracking-wide">
                                <?php echo htmlspecialchars($category); ?>
                            </h3>

                            <div class="flex flex-wrap gap-6">
                                <?php foreach ($skills as $skill): ?>
                                    <div class="text-center">
                                        <?php if (!empty($skill['skill_icon'])): ?>
                                            <div class="w-16 h-16 mx-auto mb-2 text-5xl text-gray-600 flex items-center justify-center">
                                                <i class="<?php echo htmlspecialchars($skill['skill_icon']); ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-700 font-medium">
                                            <?php echo htmlspecialchars($skill['skill_name']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Interests Section -->
        <?php if (!empty($lecturer['interests'])): ?>
            <section id="interests" class="mb-16 scroll-mt-24">
                <h2 class="text-4xl font-bold text-gray-800 mb-8">INTERESTS</h2>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <?php foreach ($lecturer['interests'] as $interest): ?>
                        <p class="text-gray-700 text-lg leading-relaxed mb-4 last:mb-0">
                            <?php echo nl2br(htmlspecialchars($interest['interest_text'])); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Publications Section -->
        <?php if (!empty($lecturer['publications'])): ?>
            <section id="publications" class="mb-16 scroll-mt-24">
                <h2 class="text-4xl font-bold text-gray-800 mb-8">PUBLISHED ARTICLES</h2>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <ul class="space-y-4">
                        <?php foreach ($lecturer['publications'] as $pub): ?>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-trophy text-blue-600 text-xl mt-1 flex-shrink-0"></i>
                                <div>
                                    <?php if (!empty($pub['url'])): ?>
                                        <a href="<?php echo htmlspecialchars($pub['url']); ?>"
                                            target="_blank"
                                            class="text-blue-600 hover:text-blue-700 transition">
                                            <?php echo htmlspecialchars($pub['title']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-blue-600"><?php echo htmlspecialchars($pub['title']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($pub['year'])): ?>
                                        <span class="text-gray-600"> - <?php echo $pub['year']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <!-- Back to Team Button -->
        <div class="text-center mt-12">
            <a href="index.php#team"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Team
            </a>
        </div>
    </div>
</div>

<script>
    // Smooth scroll for navigation tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 100,
                    behavior: 'smooth'
                });
            }

            // Update active tab
            document.querySelectorAll('.nav-tab').forEach(t => {
                t.classList.remove('active', 'border-blue-600', 'text-blue-600');
                t.classList.add('border-transparent', 'text-gray-600');
            });
            this.classList.add('active', 'border-blue-600', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-600');
        });
    });

    // Project filter
    const filterSelect = document.getElementById('projectFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            const projects = document.querySelectorAll('.project-item');

            projects.forEach(project => {
                if (selectedCategory === 'all' || project.dataset.category === selectedCategory) {
                    project.style.display = 'block';
                } else {
                    project.style.display = 'none';
                }
            });
        });
    }

    // Update active tab on scroll
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section[id]');
        const navTabs = document.querySelectorAll('.nav-tab');

        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= (sectionTop - 150)) {
                current = section.getAttribute('id');
            }
        });

        navTabs.forEach(tab => {
            tab.classList.remove('active', 'border-blue-600', 'text-blue-600');
            tab.classList.add('border-transparent', 'text-gray-600');

            if (tab.getAttribute('href') === '#' + current) {
                tab.classList.add('active', 'border-blue-600', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-600');
            }
        });
    });
</script>