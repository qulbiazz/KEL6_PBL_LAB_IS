<?php
/**
 * Sitemap Generator for INLET Website
 * Generates XML sitemap dynamically based on database content
 */

// Set content type to XML
header('Content-Type: application/xml; charset=utf-8');

// Include database connection
require_once __DIR__ . '/config/database.php';

// Base URL - update this for production
$baseUrl = 'https://pbl-inlet.alwaysdata.net/inlet-web';

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
    
    <!-- Homepage - Highest Priority -->
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Main Pages -->
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=about</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=products</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=gallery</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=news</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=team</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=research</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=contact</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>

<?php
try {
    // Get products from database
    $stmt = $pdo->query("SELECT id, name, updated_at, image FROM products WHERE status = 'active' ORDER BY id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product):
        $lastmod = !empty($product['updated_at']) ? date('Y-m-d', strtotime($product['updated_at'])) : date('Y-m-d');
?>
    <!-- Product: <?= htmlspecialchars($product['name']) ?> -->
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=product&amp;id=<?= $product['id'] ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
<?php if (!empty($product['image'])): ?>
        <image:image>
            <image:loc><?= htmlspecialchars($baseUrl) ?>/uploads/products/<?= htmlspecialchars($product['image']) ?></image:loc>
            <image:title><?= htmlspecialchars($product['name']) ?></image:title>
        </image:image>
<?php endif; ?>
    </url>
<?php 
    endforeach;
} catch (Exception $e) {
    // Silently continue if products table doesn't exist
}

try {
    // Get news articles from database
    $stmt = $pdo->query("SELECT id, title, updated_at, image FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 50");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($news as $article):
        $lastmod = !empty($article['updated_at']) ? date('Y-m-d', strtotime($article['updated_at'])) : date('Y-m-d');
?>
    <!-- News: <?= htmlspecialchars($article['title']) ?> -->
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=news&amp;id=<?= $article['id'] ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
<?php if (!empty($article['image'])): ?>
        <image:image>
            <image:loc><?= htmlspecialchars($baseUrl) ?>/uploads/news/<?= htmlspecialchars($article['image']) ?></image:loc>
            <image:title><?= htmlspecialchars($article['title']) ?></image:title>
        </image:image>
<?php endif; ?>
    </url>
<?php 
    endforeach;
} catch (Exception $e) {
    // Silently continue if news table doesn't exist
}

try {
    // Get research publications from database
    $stmt = $pdo->query("SELECT id, title, updated_at FROM research WHERE status = 'published' ORDER BY created_at DESC LIMIT 50");
    $research = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($research as $publication):
        $lastmod = !empty($publication['updated_at']) ? date('Y-m-d', strtotime($publication['updated_at'])) : date('Y-m-d');
?>
    <!-- Research: <?= htmlspecialchars($publication['title']) ?> -->
    <url>
        <loc><?= htmlspecialchars($baseUrl) ?>/index.php?page=research&amp;id=<?= $publication['id'] ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
<?php 
    endforeach;
} catch (Exception $e) {
    // Silently continue if research table doesn't exist
}
?>

</urlset>
