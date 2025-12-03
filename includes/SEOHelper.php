<?php
/**
 * SEO Helper Functions for INLET Website
 * Provides meta tags, structured data, and performance optimizations
 */

class SEOHelper {
    private static $defaultMeta = [
        'title' => 'INLET - Information and Learning Engineering Technology',
        'description' => 'Innovation and Learning Engineering Technology Laboratory at State Polytechnic of Malang. Research center focused on IoT, AI, smart systems, and innovative technology solutions for industry and education.',
        'keywords' => 'INLET, Polinema, Laboratory, Computer Engineering, IoT, Research, Technology, State Polytechnic of Malang, Innovation, AI, Smart Systems',
        'author' => 'INLET - State Polytechnic of Malang',
        'language' => 'en-US',
        'robots' => 'index, follow',
        'og_type' => 'website',
        'og_locale' => 'id_ID',
        'twitter_card' => 'summary_large_image'
    ];
    
    private static $siteUrl = '';
    private static $siteName = 'INLET - Information and Learning Engineering Technology';
    
    /**
     * Initialize SEO Helper with site URL
     */
    public static function init($siteUrl) {
        self::$siteUrl = rtrim($siteUrl, '/');
    }
    
    /**
     * Generate complete meta tags for a page
     */
    public static function generateMetaTags($options = []) {
        $meta = array_merge(self::$defaultMeta, $options);
        
        $output = '';
        
        // Basic Meta Tags
        $output .= '<meta name="description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
        $output .= '<meta name="keywords" content="' . htmlspecialchars($meta['keywords']) . '">' . "\n";
        $output .= '<meta name="author" content="' . htmlspecialchars($meta['author']) . '">' . "\n";
        $output .= '<meta name="robots" content="' . htmlspecialchars($meta['robots']) . '">' . "\n";
        $output .= '<meta name="language" content="' . htmlspecialchars($meta['language'] ?? 'en-US') . '">' . "\n";
        
        // Canonical URL (use clean URL without tracking parameters)
        $canonicalUrl = $meta['canonical'] ?? self::getCanonicalUrl();
        $output .= '<link rel="canonical" href="' . htmlspecialchars($canonicalUrl) . '">' . "\n";
        
        // Open Graph Tags
        $output .= '<meta property="og:title" content="' . htmlspecialchars($meta['title']) . '">' . "\n";
        $output .= '<meta property="og:description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
        $output .= '<meta property="og:type" content="' . htmlspecialchars($meta['og_type']) . '">' . "\n";
        $output .= '<meta property="og:url" content="' . htmlspecialchars($canonicalUrl) . '">' . "\n";
        $output .= '<meta property="og:site_name" content="' . htmlspecialchars(self::$siteName) . '">' . "\n";
        $output .= '<meta property="og:locale" content="' . htmlspecialchars($meta['og_locale']) . '">' . "\n";
        
        if (isset($meta['image'])) {
            $output .= '<meta property="og:image" content="' . htmlspecialchars($meta['image']) . '">' . "\n";
            $output .= '<meta property="og:image:alt" content="' . htmlspecialchars($meta['title']) . '">' . "\n";
        }
        
        // Twitter Card Tags
        $output .= '<meta name="twitter:card" content="' . htmlspecialchars($meta['twitter_card']) . '">' . "\n";
        $output .= '<meta name="twitter:title" content="' . htmlspecialchars($meta['title']) . '">' . "\n";
        $output .= '<meta name="twitter:description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
        
        if (isset($meta['image'])) {
            $output .= '<meta name="twitter:image" content="' . htmlspecialchars($meta['image']) . '">' . "\n";
        }
        
        // Additional Performance Meta
        $output .= '<meta name="theme-color" content="#2563eb">' . "\n";
        $output .= '<meta name="format-detection" content="telephone=no">' . "\n";
        
        return $output;
    }
    
    /**
     * Generate JSON-LD Structured Data for Organization
     */
    public static function getOrganizationSchema() {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => 'INLET - Information and Learning Engineering Technology',
            'alternateName' => 'INLET Polinema',
            'url' => self::$siteUrl,
            'logo' => self::$siteUrl . '/assets/images/inlet_logo_only.png',
            'description' => 'Laboratorium riset dan inovasi di Politeknik Negeri Malang yang fokus pada pengembangan teknologi elektro dan sistem cerdas.',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl. Soekarno Hatta No.9, Jatimulyo',
                'addressLocality' => 'Malang',
                'addressRegion' => 'Jawa Timur',
                'postalCode' => '65141',
                'addressCountry' => 'ID'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+62-341-404424',
                'contactType' => 'customer service',
                'email' => 'inletpolinema@gmail.com'
            ],
            'parentOrganization' => [
                '@type' => 'CollegeOrUniversity',
                'name' => 'Politeknik Negeri Malang',
                'url' => 'https://polinema.ac.id'
            ],
            'sameAs' => [
                'https://instagram.com/inlet.polinema',
                'https://youtube.com/@inlet.polinema'
            ]
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    /**
     * Generate JSON-LD for WebSite with SearchAction
     */
    public static function getWebSiteSchema() {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => self::$siteName,
            'url' => self::$siteUrl,
            'description' => self::$defaultMeta['description'],
            'inLanguage' => 'id-ID',
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'INLET Polinema',
                'logo' => self::$siteUrl . '/assets/images/inlet_logo_only.png'
            ]
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    /**
     * Generate JSON-LD for BreadcrumbList
     */
    public static function getBreadcrumbSchema($items) {
        $listItems = [];
        foreach ($items as $index => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url']
            ];
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    /**
     * Generate JSON-LD for Article (News/Research)
     */
    public static function getArticleSchema($article) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['title'],
            'description' => $article['description'] ?? substr(strip_tags($article['content']), 0, 160),
            'image' => $article['image'] ?? self::$siteUrl . '/assets/images/default-article.jpg',
            'author' => [
                '@type' => 'Organization',
                'name' => 'INLET Polinema'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'INLET Polinema',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => self::$siteUrl . '/assets/images/inlet_logo_only.png'
                ]
            ],
            'datePublished' => $article['created_at'] ?? date('c'),
            'dateModified' => $article['updated_at'] ?? $article['created_at'] ?? date('c'),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $article['url'] ?? self::getCurrentUrl()
            ]
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    /**
     * Generate JSON-LD for Product
     */
    public static function getProductSchema($product) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'],
            'description' => $product['description'] ?? '',
            'image' => $product['image'] ?? self::$siteUrl . '/assets/images/default-product.jpg',
            'brand' => [
                '@type' => 'Brand',
                'name' => 'INLET Polinema'
            ],
            'manufacturer' => [
                '@type' => 'Organization',
                'name' => 'INLET - Politeknik Negeri Malang'
            ]
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    /**
     * Generate preload hints for critical resources
     */
    public static function getPreloadHints() {
        $output = '';
        
        // Preload critical CSS
        $output .= '<link rel="preload" href="assets/css/tailwind.css" as="style">' . "\n";
        $output .= '<link rel="preload" href="assets/css/style.css" as="style">' . "\n";
        
        // Preload critical fonts
        $output .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        $output .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        
        // DNS Prefetch for external resources
        $output .= '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">' . "\n";
        $output .= '<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">' . "\n";
        
        return $output;
    }
    
    /**
     * Get current page URL
     */
    public static function getCurrentUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query parameters for cleaner canonical URL
        $path = strtok($uri, '?');
        
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Get clean canonical URL (without tracking parameters)
     */
    public static function getCanonicalUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Get only allowed parameters (page, id)
        $allowedParams = [];
        if (isset($_GET['page'])) {
            $allowedParams['page'] = $_GET['page'];
        }
        if (isset($_GET['id'])) {
            $allowedParams['id'] = $_GET['id'];
        }
        
        $queryString = !empty($allowedParams) ? '?' . http_build_query($allowedParams) : '';
        
        return $protocol . '://' . $host . $path . $queryString;
    }
    
    /**
     * Generate page-specific SEO data
     */
    public static function getPageSEO($page, $data = []) {
        $seo = self::$defaultMeta;
        
        switch ($page) {
            case 'home':
                $seo['title'] = 'INLET - Information and Learning Engineering Technology | Politeknik Negeri Malang';
                $seo['description'] = 'INLET adalah laboratorium riset dan inovasi Teknik Informatika di Politeknik Negeri Malang. Mengembangkan teknologi IoT, sistem cerdas, dan solusi inovatif.';
                break;
                
            case 'about':
                $seo['title'] = 'Tentang INLET - Laboratorium Riset Teknik Informatika Polinema';
                $seo['description'] = 'Pelajari tentang INLET, laboratorium riset yang berfokus pada pengembangan teknologi elektro, IoT, dan sistem pembelajaran inovatif di Politeknik Negeri Malang.';
                break;
                
            case 'product-detail':
                if (!empty($data['product'])) {
                    $seo['title'] = $data['product']['name'] . ' - Produk INLET Polinema';
                    $seo['description'] = substr(strip_tags($data['product']['description']), 0, 160);
                    $seo['image'] = self::$siteUrl . '/uploads/products/' . $data['product']['image'];
                    $seo['og_type'] = 'product';
                }
                break;
                
            case 'news-detail':
                if (!empty($data['news'])) {
                    $seo['title'] = $data['news']['title'] . ' - Berita INLET';
                    $seo['description'] = substr(strip_tags($data['news']['content']), 0, 160);
                    $seo['image'] = self::$siteUrl . '/uploads/news/' . $data['news']['image'];
                    $seo['og_type'] = 'article';
                }
                break;
                
            case 'research-detail':
                if (!empty($data['research'])) {
                    $seo['title'] = $data['research']['title'] . ' - Riset INLET';
                    $seo['description'] = substr(strip_tags($data['research']['content']), 0, 160);
                    $seo['image'] = self::$siteUrl . '/uploads/research/' . $data['research']['image'];
                    $seo['og_type'] = 'article';
                }
                break;
                
            case 'borrowing':
                $seo['title'] = 'Peminjaman Alat & Ruangan - INLET Polinema';
                $seo['description'] = 'Ajukan peminjaman alat laboratorium dan ruangan INLET secara online. Proses mudah dan cepat untuk mahasiswa dan dosen Politeknik Negeri Malang.';
                break;
                
            case 'attendance':
                $seo['title'] = 'Absensi Mahasiswa - INLET Polinema';
                $seo['description'] = 'Sistem absensi digital untuk mahasiswa yang mengunjungi laboratorium INLET Politeknik Negeri Malang.';
                break;
                
            case 'gallery':
                $seo['title'] = 'Galeri - INLET Polinema';
                $seo['description'] = 'Galeri foto kegiatan, penelitian, dan dokumentasi laboratorium INLET Politeknik Negeri Malang.';
                break;
        }
        
        return $seo;
    }
}
