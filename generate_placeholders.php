<?php
/**
 * Generate Placeholder Images
 * Run this script once to create placeholder images for missing content
 */

// Create placeholder directory if not exists
$placeholderDir = 'assets/images';
if (!file_exists($placeholderDir)) {
    mkdir($placeholderDir, 0777, true);
}

// Placeholder configurations
$placeholders = [
    [
        'name' => 'placeholder.jpg',
        'width' => 800,
        'height' => 600,
        'text' => 'Image Not Available',
        'gradient' => ['#667eea', '#764ba2']
    ],
    [
        'name' => 'placeholder-banner.jpg',
        'width' => 1920,
        'height' => 1080,
        'text' => 'Banner Image',
        'gradient' => ['#667eea', '#764ba2']
    ],
    [
        'name' => 'placeholder-product.jpg',
        'width' => 800,
        'height' => 600,
        'text' => 'Product Image',
        'gradient' => ['#4facfe', '#00f2fe']
    ],
    [
        'name' => 'placeholder-team.jpg',
        'width' => 400,
        'height' => 500,
        'text' => 'Team Member',
        'gradient' => ['#43e97b', '#38f9d7']
    ],
    [
        'name' => 'placeholder-partner.jpg',
        'width' => 300,
        'height' => 150,
        'text' => 'Partner Logo',
        'gradient' => ['#fa709a', '#fee140']
    ],
    [
        'name' => 'placeholder-news.jpg',
        'width' => 800,
        'height' => 450,
        'text' => 'News Image',
        'gradient' => ['#30cfd0', '#330867']
    ],
    [
        'name' => 'placeholder-gallery.jpg',
        'width' => 600,
        'height' => 600,
        'text' => 'Gallery Image',
        'gradient' => ['#a8edea', '#fed6e3']
    ],
    [
        'name' => 'placeholder-research.jpg',
        'width' => 800,
        'height' => 450,
        'text' => 'Research Image',
        'gradient' => ['#ff9a56', '#ff6a88']
    ]
];

// Function to create gradient image
function createPlaceholderImage($config, $outputPath) {
    $width = $config['width'];
    $height = $config['height'];
    $text = $config['text'];
    $gradient = $config['gradient'];
    
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Parse gradient colors
    $startColor = hexToRgb($gradient[0]);
    $endColor = hexToRgb($gradient[1]);
    
    // Draw gradient
    for ($y = 0; $y < $height; $y++) {
        $ratio = $y / $height;
        $r = floor($startColor['r'] * (1 - $ratio) + $endColor['r'] * $ratio);
        $g = floor($startColor['g'] * (1 - $ratio) + $endColor['g'] * $ratio);
        $b = floor($startColor['b'] * (1 - $ratio) + $endColor['b'] * $ratio);
        
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $y, $width, $y, $color);
    }
    
    // Add text
    $white = imagecolorallocate($image, 255, 255, 255);
    $fontSize = min($width, $height) / 15;
    
    // Add icon (simple shape)
    $iconSize = min($width, $height) / 4;
    $centerX = $width / 2;
    $centerY = $height / 2 - $fontSize;
    
    // Draw image icon (rectangle with circle)
    $iconColor = imagecolorallocate($image, 255, 255, 255);
    imagesetthickness($image, 3);
    imagerectangle($image, 
        $centerX - $iconSize/2, 
        $centerY - $iconSize/2, 
        $centerX + $iconSize/2, 
        $centerY + $iconSize/2, 
        $iconColor
    );
    
    // Draw circle (sun/moon)
    $circleRadius = $iconSize / 6;
    imagefilledellipse($image, 
        $centerX - $iconSize/4, 
        $centerY - $iconSize/4, 
        $circleRadius * 2, 
        $circleRadius * 2, 
        $iconColor
    );
    
    // Draw mountains (triangles)
    $points = [
        $centerX - $iconSize/3, $centerY + $iconSize/3,
        $centerX - $iconSize/6, $centerY,
        $centerX, $centerY + $iconSize/3
    ];
    imagefilledpolygon($image, $points, 3, $iconColor);
    
    $points2 = [
        $centerX, $centerY + $iconSize/3,
        $centerX + $iconSize/6, $centerY + $iconSize/6,
        $centerX + $iconSize/3, $centerY + $iconSize/3
    ];
    imagefilledpolygon($image, $points2, 3, $iconColor);
    
    // Add text
    $textBox = imagettfbbox($fontSize, 0, __DIR__ . '/assets/fonts/arial.ttf', $text);
    if ($textBox === false) {
        // Fallback to built-in font if TTF not available
        $textWidth = strlen($text) * imagefontwidth(5);
        $textHeight = imagefontheight(5);
        $x = ($width - $textWidth) / 2;
        $y = $height - $fontSize * 2;
        imagestring($image, 5, $x, $y, $text, $white);
    } else {
        $textWidth = abs($textBox[4] - $textBox[0]);
        $x = ($width - $textWidth) / 2;
        $y = $height - $fontSize;
        imagettftext($image, $fontSize, 0, $x, $y, $white, __DIR__ . '/assets/fonts/arial.ttf', $text);
    }
    
    // Add dimension text
    $dimText = $width . ' × ' . $height;
    $dimFontSize = $fontSize * 0.6;
    $dimTextWidth = strlen($dimText) * imagefontwidth(3);
    $dimX = ($width - $dimTextWidth) / 2;
    $dimY = $height - $fontSize * 0.5;
    
    // Semi-transparent background for dimension text
    $bgColor = imagecolorallocatealpha($image, 0, 0, 0, 50);
    imagefilledrectangle($image, $dimX - 10, $dimY - 5, $dimX + $dimTextWidth + 10, $dimY + 20, $bgColor);
    imagestring($image, 3, $dimX, $dimY, $dimText, $white);
    
    // Save image
    imagejpeg($image, $outputPath, 85);
    imagedestroy($image);
    
    return true;
}

// Helper function to convert hex to RGB
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    ];
}

// Generate all placeholders
echo "<!DOCTYPE html>
<html>
<head>
    <title>Generating Placeholders</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { color: green; margin: 5px 0; }
        .error { color: red; margin: 5px 0; }
        .info { color: blue; margin: 5px 0; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>Generating Placeholder Images</h1>
";

foreach ($placeholders as $config) {
    $outputPath = $placeholderDir . '/' . $config['name'];
    
    echo "<div class='info'>Processing: {$config['name']} ({$config['width']}x{$config['height']})...</div>";
    
    try {
        if (createPlaceholderImage($config, $outputPath)) {
            echo "<div class='success'>✓ Created: {$outputPath}</div>";
        } else {
            echo "<div class='error'>✗ Failed to create: {$outputPath}</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
    }
}

echo "
    <br><hr><br>
    <div class='info'><strong>Done!</strong> All placeholder images have been generated.</div>
    <div class='info'>Location: {$placeholderDir}/</div>
    <br>
    <a href='index.php' style='display: inline-block; padding: 10px 20px; background: #4facfe; color: white; text-decoration: none; border-radius: 5px;'>← Back to Home</a>
</body>
</html>
";
?>
