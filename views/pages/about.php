<?php
// Default values if no settings found
if (!$settings) {
    $settings = [
        'hero_title' => 'About InLet',
        'hero_subtitle' => 'Information and Learning Engineering Technology',
        'vision_text' => 'To become a leading research and development center in information and learning engineering technology.',
        'who_we_are_text' => 'InLet is a dynamic research laboratory.',
        'quote_text' => 'Through our research activities, we aim to make a significant impact.'
    ];
}

// Helper function to get color classes
function getColorClasses($color)
{
    $colors = [
        'blue' => ['bg' => 'bg-blue-100', 'hover' => 'hover:bg-blue-600', 'text' => 'text-blue-600', 'border' => 'hover:border-blue-200'],
        'green' => ['bg' => 'bg-green-100', 'hover' => 'hover:bg-green-600', 'text' => 'text-green-600', 'border' => 'hover:border-green-200'],
        'purple' => ['bg' => 'bg-purple-100', 'hover' => 'hover:bg-purple-600', 'text' => 'text-purple-600', 'border' => 'hover:border-purple-200'],
        'orange' => ['bg' => 'bg-orange-100', 'hover' => 'hover:bg-orange-600', 'text' => 'text-orange-600', 'border' => 'hover:border-orange-200']
    ];
    return $colors[$color] ?? $colors['blue'];
}
?>

<!-- Abstract Background Pattern -->
<div class="abstract-pattern opacity-50"></div>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-blue-600 to-blue-800 text-white py-24 overflow-hidden">
    <div class="absolute inset-0 bg-white/5"></div>
    <div class="container mx-auto px-6 md:px-8 lg:px-12 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight tracking-tight"><?php echo htmlspecialchars($settings['hero_title']); ?></h1>
            <p class="text-xl md:text-2xl text-blue-100 font-light tracking-wide"><?php echo htmlspecialchars($settings['hero_subtitle']); ?></p>
        </div>
    </div>
</section>

<!-- Vision & Mission -->
<section class="py-20 bg-white relative z-10 -mt-8">
    <div class="container mx-auto px-6 md:px-8 lg:px-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            <!-- Vision -->
            <div class="bg-blue-50 p-10 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-blue-100 group">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-3xl text-blue-600 mb-6 shadow-sm group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-eye"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Vision</h2>
                <p class="text-gray-600 leading-relaxed text-lg">
                    <?php echo htmlspecialchars($settings['vision_text']); ?>
                </p>
            </div>
            <!-- Mission -->
            <div class="bg-green-50 p-10 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-green-100 group">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-3xl text-green-600 mb-6 shadow-sm group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Mission</h2>
                <ul class="space-y-4 text-gray-600">
                    <?php foreach ($mission_items as $item): ?>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-200 flex items-center justify-center mt-1 mr-3">
                                <i class="fas fa-check text-green-700 text-xs"></i>
                            </div>
                            <span class="text-lg"><?php echo htmlspecialchars($item); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-6 md:px-8 lg:px-12">
        <div class="max-w-6xl mx-auto">
            <!-- Who We Are -->
            <div class="bg-white rounded-3xl shadow-xl p-10 md:p-14 mb-16 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full -mr-32 -mt-32 opacity-50"></div>
                <div class="relative z-10">
                    <h2 class="text-4xl font-bold text-gray-800 mb-8 text-center">Who We Are</h2>
                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <div class="space-y-6">
                            <?php
                            $paragraphs = explode("\n\n", $settings['who_we_are_text']);
                            foreach ($paragraphs as $paragraph):
                                if (trim($paragraph)):
                            ?>
                                    <p class="text-gray-600 leading-relaxed text-lg">
                                        <?php
                                        // Highlight "InLet" if it appears
                                        $text = htmlspecialchars(trim($paragraph));
                                        $text = str_replace('InLet', '<span class="font-semibold text-blue-600">InLet</span>', $text);
                                        echo $text;
                                        ?>
                                    </p>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                        <div class="bg-blue-50 p-8 rounded-2xl border border-blue-100">
                            <p class="text-gray-700 leading-relaxed text-lg italic font-medium">
                                "<?php echo htmlspecialchars($settings['quote_text']); ?>"
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Research Road Map -->
            <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-2xl md:rounded-3xl shadow-xl p-5 sm:p-8 md:p-14 mb-16 overflow-hidden relative border border-gray-100">
                <div class="absolute top-0 left-0 w-48 md:w-96 h-48 md:h-96 bg-blue-100 rounded-full -ml-24 md:-ml-48 -mt-24 md:-mt-48 opacity-30"></div>
                <div class="absolute bottom-0 right-0 w-32 md:w-64 h-32 md:h-64 bg-green-100 rounded-full -mr-16 md:-mr-32 -mb-16 md:-mb-32 opacity-30"></div>
                
                <div class="relative z-10">
                    <!-- Header for Mobile -->
                    <div class="text-center mb-6 lg:hidden">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 leading-tight">
                            Our Research <span class="text-blue-600">Road Map</span>
                        </h2>
                        <p class="text-gray-600 text-sm sm:text-base leading-relaxed mt-3">
                            Building a complete <strong class="text-blue-600">Support system</strong> based on learning behavior of students.
                        </p>
                    </div>
                    
                    <div class="grid lg:grid-cols-2 gap-6 lg:gap-12 items-center">
                        <!-- Left Content - Hidden on Mobile (shown above) -->
                        <div class="hidden lg:block space-y-6">
                            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 leading-tight">
                                Our Research <br><span class="text-blue-600">Road Map</span>
                            </h2>
                            <p class="text-gray-600 text-lg leading-relaxed">
                                We are aiming to build a complete <strong class="text-blue-600">Support system</strong> based on the learning behavior of the students. Start from the Learning application, Learning analytics, Multi Modal Learning Analytics, AI in Education, Adaptive Support System, Gamification, and Management Learning Monitoring System.
                            </p>
                            <div class="flex flex-wrap gap-3 pt-4">
                                <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">Learning Analytics</span>
                                <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-medium">AI in Education</span>
                                <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">Adaptive System</span>
                                <span class="px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">Gamification</span>
                            </div>
                        </div>
                        
                        <!-- Right Content - Road Map Diagram -->
                        <div class="relative">
                            <!-- Main Container -->
                            <div class="bg-white rounded-xl md:rounded-2xl shadow-lg p-3 sm:p-4 md:p-6 border border-gray-200">
                                <!-- SMILE Header -->
                                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg md:rounded-xl p-3 md:p-4 mb-3 md:mb-4 text-white">
                                    <h3 class="text-sm sm:text-base md:text-xl font-bold text-center">Smart Monitoring In Learning (SMILE)</h3>
                                </div>
                                
                                <!-- Mobile Layout: Stack vertically -->
                                <div class="block md:hidden space-y-3">
                                    <!-- Road Map Label - Mobile -->
                                    <div class="bg-gray-800 rounded-lg p-3 text-white">
                                        <p class="font-bold text-xs text-center">ROAD Map for Learning Engineering Technology Lab</p>
                                    </div>
                                    
                                    <!-- SATE Section - Mobile -->
                                    <div class="bg-gradient-to-r from-orange-400 to-orange-500 rounded-lg p-3">
                                        <h4 class="text-white font-bold text-xs sm:text-sm mb-2">Smart Adaptive Technology in Education (SATE)</h4>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Reports for Management</div>
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Measure understanding</div>
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Solution for Students</div>
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Feedback to apps</div>
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Solution for Teachers</div>
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Knowledge training</div>
                                        </div>
                                    </div>
                                    
                                    <!-- SILA Section - Mobile -->
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-3">
                                        <h4 class="text-white font-bold text-xs sm:text-sm mb-2">Smart Integration Learning Analytics (SILA)</h4>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Integrating Logs</div>
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700">Profiling students</div>
                                            <div class="bg-white/90 rounded px-2 py-1.5 text-[10px] sm:text-xs text-gray-700 col-span-2">Students' characteristic Cluster</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Learning Apps - Mobile -->
                                    <div class="bg-gray-100 rounded-lg p-3 border-2 border-gray-300">
                                        <h4 class="text-gray-800 font-bold text-xs sm:text-sm mb-2 text-center">Learning Apps</h4>
                                        <div class="flex flex-wrap gap-1.5 justify-center">
                                            <span class="bg-white border border-gray-300 rounded px-2 py-1 text-[10px] sm:text-xs font-medium text-gray-700">Viat-map</span>
                                            <span class="bg-white border border-gray-300 rounded px-2 py-1 text-[10px] sm:text-xs font-medium text-gray-700">QB-rec</span>
                                            <span class="bg-white border border-gray-300 rounded px-2 py-1 text-[10px] sm:text-xs font-medium text-gray-700">SQLearn</span>
                                            <span class="bg-white border border-gray-300 rounded px-2 py-1 text-[10px] sm:text-xs font-medium text-gray-700">LOOP</span>
                                            <span class="bg-white border border-gray-300 rounded px-2 py-1 text-[10px] sm:text-xs font-medium text-gray-700">PSEUDO</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Desktop Layout: Side by side -->
                                <div class="hidden md:flex gap-4">
                                    <!-- Left Column - Road Map Label -->
                                    <div class="bg-gray-800 rounded-xl p-4 flex items-center justify-center text-white min-w-[120px]">
                                        <div class="text-center">
                                            <p class="font-bold text-sm leading-tight">ROAD Map for</p>
                                            <p class="font-bold text-sm leading-tight">Learning</p>
                                            <p class="font-bold text-sm leading-tight">Engineering</p>
                                            <p class="font-bold text-sm leading-tight">Technology Lab</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Right Content -->
                                    <div class="flex-1 space-y-3">
                                        <!-- SATE Section -->
                                        <div class="bg-gradient-to-r from-orange-400 to-orange-500 rounded-lg p-3">
                                            <h4 class="text-white font-bold text-sm mb-2">Smart Adaptive Technology in Education (SATE)</h4>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">Reports for Management & Policy Maker</div>
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">Measure students' level understanding</div>
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">Alternative solution for Students</div>
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">Sending feedback to apps</div>
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">Alternative Solution for Teachers</div>
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">New Knowledge-data training</div>
                                            </div>
                                        </div>
                                        
                                        <!-- SILA Section -->
                                        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-3">
                                            <h4 class="text-white font-bold text-sm mb-2">Smart Integration Learning Analytics (SILA)</h4>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">Integrating Logs from Each Apps</div>
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700">Profiling students and class</div>
                                                <div class="bg-white/90 rounded px-2 py-1 text-xs text-gray-700 col-span-2">Students' characteristic Cluster</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Learning Apps Section -->
                                        <div class="bg-gray-100 rounded-lg p-3 border-2 border-gray-300">
                                            <h4 class="text-gray-800 font-bold text-sm mb-2 text-center">Learning Apps</h4>
                                            <div class="flex flex-wrap gap-2 justify-center">
                                                <span class="bg-white border border-gray-300 rounded px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">Viat-map</span>
                                                <span class="bg-white border border-gray-300 rounded px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">QB-rec</span>
                                                <span class="bg-white border border-gray-300 rounded px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">SQLearn</span>
                                                <span class="bg-white border border-gray-300 rounded px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">LOOP</span>
                                                <span class="bg-white border border-gray-300 rounded px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">PSEUDO Learn</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Decorative Elements - Hidden on mobile -->
                            <div class="hidden md:block absolute -top-4 -right-4 w-8 h-8 bg-blue-500 rounded-full opacity-20"></div>
                            <div class="hidden md:block absolute -bottom-4 -left-4 w-12 h-12 bg-green-500 rounded-full opacity-20"></div>
                        </div>
                    </div>
                    
                    <!-- Tags for Mobile - Show below diagram -->
                    <div class="flex flex-wrap gap-2 justify-center mt-5 lg:hidden">
                        <span class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Learning Analytics</span>
                        <span class="px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">AI in Education</span>
                        <span class="px-3 py-1.5 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">Adaptive System</span>
                        <span class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">Gamification</span>
                    </div>
                </div>
            </div>

            <!-- Focus Areas -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-10 text-center">Our Focus Areas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($focus_areas as $area):
                        $colors = getColorClasses($area['color']);
                    ?>
                        <div class="group bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 <?php echo $colors['border']; ?>">
                            <div class="flex items-start">
                                <div class="<?php echo $colors['bg']; ?> p-4 rounded-xl mr-6 <?php echo $colors['hover']; ?> transition-colors duration-300">
                                    <i class="<?php echo htmlspecialchars($area['icon']); ?> <?php echo $colors['text']; ?> text-2xl group-hover:text-white transition-colors duration-300"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:<?php echo $colors['text']; ?> transition-colors"><?php echo htmlspecialchars($area['title']); ?></h3>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($area['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>