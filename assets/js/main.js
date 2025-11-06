// Main JavaScript functions

// Image placeholder handler - Define globally for inline onerror
function handleImageError(img) {
    if (!img.classList.contains('img-error')) {
        img.classList.add('img-error', 'img-placeholder');
        
        // Create placeholder container
        const placeholder = document.createElement('div');
        placeholder.className = img.className;
        placeholder.setAttribute('data-placeholder', img.alt || 'Image not found');
        
        // Add icon
        const icon = document.createElement('i');
        icon.className = 'fas fa-image';
        placeholder.appendChild(icon);
        
        // Replace image with placeholder
        img.parentNode.replaceChild(placeholder, img);
    }
}

// Gallery modal functions - Define globally
function openGalleryModal(image, caption) {
    const modal = document.getElementById('galleryModal');
    const modalImage = document.getElementById('galleryModalImage');
    const modalCaption = document.getElementById('galleryModalCaption');
    
    if (modal && modalImage && modalCaption) {
        modalImage.src = 'uploads/gallery/' + image;
        modalImage.alt = caption;
        modalCaption.textContent = caption;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeGalleryModal() {
    const modal = document.getElementById('galleryModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});

// Close modal on clicking outside and setup gallery
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('galleryModal');
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeGalleryModal();
            }
        });
    }
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeGalleryModal();
        }
    });
    
    // Add click handlers to gallery items
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const image = this.getAttribute('data-image');
            const caption = this.getAttribute('data-caption');
            openGalleryModal(image, caption);
        });
    });
});

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    // Handle all anchor links (including those with page#hash format)
    document.querySelectorAll('a[href*="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            
            // Check if it's a hash link
            if (href.includes('#')) {
                const hashParts = href.split('#');
                const targetId = hashParts[1];
                const targetPage = hashParts[0];
                
                // Check current page
                const currentPage = window.location.pathname.split('/').pop();
                
                // If it's same page or just hash or target is index.php and we're on index
                if (targetPage === '' || targetPage === currentPage || 
                    (targetPage === 'index.php' && (currentPage === 'index.php' || currentPage === ''))) {
                    e.preventDefault();
                    const target = document.getElementById(targetId);
                    if (target) {
                        // Get navbar height for offset
                        const navbar = document.querySelector('nav');
                        const navbarHeight = navbar ? navbar.offsetHeight : 0;
                        
                        // Calculate position with offset
                        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 20;
                        
                        // Smooth scroll
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                        
                        // Update URL hash
                        history.pushState(null, null, '#' + targetId);
                    }
                }
                // If different page, let it navigate normally (will be handled by index.php)
            }
        });
    });
    
    // Handle hash in URL on page load
    if (window.location.hash) {
        setTimeout(() => {
            const targetId = window.location.hash.substring(1);
            const target = document.getElementById(targetId);
            if (target) {
                const navbar = document.querySelector('nav');
                const navbarHeight = navbar ? navbar.offsetHeight : 0;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }, 100);
    }
    
    // Update active navbar based on scroll position (only on index.php)
    const currentPage = window.location.pathname.split('/').pop();
    if (currentPage === 'index.php' || currentPage === '') {
        const sections = ['products', 'team', 'news', 'gallery', 'research'];
        const navLinks = document.querySelectorAll('.nav-section-link');
        const homeLink = document.getElementById('nav-home');
        const homeLinkMobile = document.getElementById('nav-home-mobile');
        
        function updateActiveNav() {
            const navbar = document.querySelector('nav');
            const navbarHeight = navbar ? navbar.offsetHeight : 0;
            const scrollPosition = window.scrollY + navbarHeight + 100;
            
            let currentSection = '';
            
            // Check each section
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    const sectionTop = section.offsetTop;
                    const sectionBottom = sectionTop + section.offsetHeight;
                    
                    if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                        currentSection = sectionId;
                    }
                }
            });
            
            // Update nav links
            navLinks.forEach(link => {
                const linkSection = link.getAttribute('data-section');
                if (linkSection === currentSection) {
                    link.classList.remove('text-gray-700');
                    link.classList.add('text-blue-600', 'font-semibold');
                } else {
                    link.classList.remove('text-blue-600', 'font-semibold');
                    link.classList.add('text-gray-700');
                }
            });
            
            // Update home link - only active if no section is active and near top
            if (!currentSection && scrollPosition < 300) {
                if (homeLink) {
                    homeLink.classList.remove('text-gray-700');
                    homeLink.classList.add('text-blue-600', 'font-semibold');
                }
                if (homeLinkMobile) {
                    homeLinkMobile.classList.remove('text-gray-700');
                    homeLinkMobile.classList.add('text-blue-600', 'font-semibold');
                }
            } else {
                if (homeLink) {
                    homeLink.classList.remove('text-blue-600', 'font-semibold');
                    homeLink.classList.add('text-gray-700');
                }
                if (homeLinkMobile) {
                    homeLinkMobile.classList.remove('text-blue-600', 'font-semibold');
                    homeLinkMobile.classList.add('text-gray-700');
                }
            }
        }
        
        // Update on scroll
        window.addEventListener('scroll', updateActiveNav);
        
        // Update on load
        updateActiveNav();
    }
});

// Track page visits
function trackPageVisit() {
    fetch('api/track_visit.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            page: window.location.pathname
        })
    });
}

// Call on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', trackPageVisit);
} else {
    trackPageVisit();
}

// Add error handler to all images on page load
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.onerror = function() {
            handleImageError(this);
        };
        
        // Check if image is already broken
        if (img.complete && img.naturalWidth === 0) {
            handleImageError(img);
        }
    });
});

// Handle background images in carousel
document.addEventListener('DOMContentLoaded', function() {
    const carouselSlides = document.querySelectorAll('.carousel-slide');
    carouselSlides.forEach(slide => {
        const bgImage = slide.style.backgroundImage;
        if (bgImage) {
            const imgUrl = bgImage.replace(/url\(['"]?(.*?)['"]?\)/, '$1');
            
            // Test if image exists
            const testImg = new Image();
            testImg.onerror = function() {
                // Set placeholder background
                slide.style.backgroundImage = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                
                // Add placeholder icon
                const contentDiv = slide.querySelector('.relative.z-10');
                if (contentDiv && !slide.querySelector('.bg-placeholder-icon')) {
                    const placeholderIcon = document.createElement('div');
                    placeholderIcon.className = 'bg-placeholder-icon absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white text-9xl opacity-20';
                    placeholderIcon.innerHTML = '<i class="fas fa-image"></i>';
                    slide.insertBefore(placeholderIcon, contentDiv);
                }
            };
            testImg.src = imgUrl;
        }
    });
});
