<?php
/**
 * Image Helper Functions
 * Provides functions to handle missing images with placeholders
 */

/**
 * Get image path with fallback to placeholder
 * @param string $imagePath - Relative path to the image
 * @param string $type - Type of image (banner, product, team, etc.)
 * @return string - Valid image path or placeholder
 */
function getImageWithPlaceholder($imagePath, $type = 'default') {
    // Check if image file exists
    if (!empty($imagePath) && file_exists($imagePath)) {
        return $imagePath;
    }
    
    // Return placeholder based on type
    return getPlaceholderPath($type);
}

/**
 * Get placeholder path based on image type
 * @param string $type - Type of placeholder needed
 * @return string - Path to placeholder image
 */
function getPlaceholderPath($type = 'default') {
    $placeholders = [
        'banner' => 'assets/images/placeholder-banner.jpg',
        'product' => 'assets/images/placeholder-product.jpg',
        'team' => 'assets/images/placeholder-team.jpg',
        'partner' => 'assets/images/placeholder-partner.jpg',
        'news' => 'assets/images/placeholder-news.jpg',
        'gallery' => 'assets/images/placeholder-gallery.jpg',
        'research' => 'assets/images/placeholder-research.jpg',
        'default' => 'assets/images/placeholder.jpg'
    ];
    
    return isset($placeholders[$type]) ? $placeholders[$type] : $placeholders['default'];
}

/**
 * Generate inline SVG placeholder
 * @param int $width - Width of placeholder
 * @param int $height - Height of placeholder
 * @param string $text - Text to display on placeholder
 * @param string $bgColor - Background color (hex)
 * @return string - Base64 encoded SVG
 */
function generateSvgPlaceholder($width = 800, $height = 600, $text = 'No Image', $bgColor = '#667eea') {
    $svg = <<<SVG
<svg width="{$width}" height="{$height}" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#grad)"/>
    <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="24" fill="white" text-anchor="middle" dy=".3em">
        {$text}
    </text>
    <text x="50%" y="60%" font-family="Arial, sans-serif" font-size="14" fill="rgba(255,255,255,0.7)" text-anchor="middle" dy=".3em">
        {$width} × {$height}
    </text>
</svg>
SVG;
    
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Check if image exists and return appropriate src with onerror handler
 * @param string $imagePath - Path to image
 * @param string $alt - Alt text
 * @param string $class - CSS classes
 * @param string $type - Type of image for placeholder
 * @return string - Complete img tag with error handling
 */
function renderImageTag($imagePath, $alt = '', $class = '', $type = 'default') {
    $safePath = htmlspecialchars($imagePath);
    $safeAlt = htmlspecialchars($alt);
    $safeClass = htmlspecialchars($class);
    
    // Check if file exists
    $fileExists = !empty($imagePath) && file_exists($imagePath);
    
    if ($fileExists) {
        return sprintf(
            '<img src="%s" alt="%s" class="%s" onerror="handleImageError(this)">',
            $safePath,
            $safeAlt,
            $safeClass
        );
    } else {
        // Return placeholder div instead
        $placeholder = generateSvgPlaceholder(800, 600, $alt ?: 'No Image');
        return sprintf(
            '<img src="%s" alt="%s" class="%s" onerror="handleImageError(this)">',
            $placeholder,
            $safeAlt,
            $safeClass
        );
    }
}
?>
