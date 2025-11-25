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