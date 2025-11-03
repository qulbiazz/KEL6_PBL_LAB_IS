// Main JavaScript functions

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

// Gallery modal functions
function openGalleryModal(image, caption) {
    const modal = document.getElementById('galleryModal');
    const modalImage = document.getElementById('galleryModalImage');
    const modalCaption = document.getElementById('galleryModalCaption');
    
    if (modal && modalImage && modalCaption) {
        modalImage.src = 'uploads/gallery/' + image;
        modalCaption.textContent = caption;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeGalleryModal() {
    const modal = document.getElementById('galleryModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Close modal on clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('galleryModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeGalleryModal();
            }
        });
    }
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
                
                // If it's same page or just hash
                if (hashParts[0] === '' || hashParts[0] === 'index.php' || hashParts[0] === window.location.pathname.split('/').pop()) {
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

// Image placeholder handler
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
