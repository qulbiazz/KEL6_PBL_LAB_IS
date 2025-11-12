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
// Gallery modal functions - Define globally
let currentGalleryIndex = 0;
let galleryItems = [];

function openGalleryModal(image, caption, index = 0) {
    const modal = document.getElementById('galleryModal');
    const modalImage = document.getElementById('galleryModalImage');
    const modalCaption = document.getElementById('galleryModalCaption');
    const galleryCounter = document.getElementById('galleryCounter');
    
    if (!modal || !modalImage || !modalCaption) {
        console.error('Modal elements not found:', {
            modal: !!modal,
            modalImage: !!modalImage,
            modalCaption: !!modalCaption
        });
        return;
    }
    
    currentGalleryIndex = index;
    galleryItems = document.querySelectorAll('.gallery-item');
    
    // Update modal content
    modalImage.src = 'uploads/gallery/' + image;
    modalImage.alt = caption;
    modalCaption.textContent = caption;
    
    if (galleryCounter) {
        galleryCounter.textContent = `${currentGalleryIndex + 1} / ${galleryItems.length}`;
    }
    
    // Show modal with animation
    modal.style.display = 'flex';
    modal.style.opacity = '0';
    document.body.style.overflow = 'hidden';
    
    // Fade in animation
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

function closeGalleryModal() {
    const modal = document.getElementById('galleryModal');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

function nextGalleryImage() {
    if (galleryItems.length > 0) {
        currentGalleryIndex = (currentGalleryIndex + 1) % galleryItems.length;
        const item = galleryItems[currentGalleryIndex];
        const image = item.getAttribute('data-image');
        const caption = item.getAttribute('data-caption');
        openGalleryModal(image, caption, currentGalleryIndex);
    }
}

function previousGalleryImage() {
    if (galleryItems.length > 0) {
        currentGalleryIndex = (currentGalleryIndex - 1 + galleryItems.length) % galleryItems.length;
        const item = galleryItems[currentGalleryIndex];
        const image = item.getAttribute('data-image');
        const caption = item.getAttribute('data-caption');
        openGalleryModal(image, caption, currentGalleryIndex);
    }
}

// Initialize gallery click handlers
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('galleryModal');
    
    // Initialize gallery items array
    galleryItems = document.querySelectorAll('.gallery-item');
    
    // Add click event listeners to all gallery items
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function() {
            const image = this.getAttribute('data-image');
            const caption = this.getAttribute('data-caption');
            openGalleryModal(image, caption, index);
        });
    });
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeGalleryModal();
            }
        });
    }
    
    // Close modal with ESC key and navigate with arrows
    document.addEventListener('keydown', function(e) {
        const modalElement = document.getElementById('galleryModal');
        if (modalElement && modalElement.style.display === 'flex') {
            if (e.key === 'Escape') {
                closeGalleryModal();
            } else if (e.key === 'ArrowRight') {
                nextGalleryImage();
            } else if (e.key === 'ArrowLeft') {
                previousGalleryImage();
            }
        }
    });
});

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
    
    // Initialize gallery items array
    galleryItems = document.querySelectorAll('.gallery-item');
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeGalleryModal();
            }
        });
    }
    
    // Close modal with ESC key and navigate with arrows
    document.addEventListener('keydown', function(e) {
        const modalElement = document.getElementById('galleryModal');
        if (modalElement && modalElement.style.display === 'flex') {
            if (e.key === 'Escape') {
                closeGalleryModal();
            } else if (e.key === 'ArrowRight') {
                nextGalleryImage();
            } else if (e.key === 'ArrowLeft') {
                previousGalleryImage();
            }
        }
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
                const basePart = hashParts[0];
                
                // Get current page parameter
                const urlParams = new URLSearchParams(window.location.search);
                const currentPage = urlParams.get('page') || 'home';
                
                // Check if link is to index.php or same page
                const isIndexLink = basePart === 'index.php' || basePart === '';
                
                // If on home page and clicking section link, scroll smoothly
                if (currentPage === 'home' && isIndexLink) {
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
                        history.pushState(null, null, href);
                    }
                }
                // If different page, let it navigate normally
            }
        });
    });
    
    // Handle hash in URL on page load
    if (window.location.hash) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get('page') || 'home';
        
        if (currentPage === 'home') {
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
    }
    
    // Update active navbar based on scroll position (only on home page)
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'home';
    
    if (currentPage === 'home') {
        const sections = ['products', 'team', 'news', 'gallery', 'research'];
        const navLinks = document.querySelectorAll('.nav-section-link');
        const homeLinks = document.querySelectorAll('.nav-home-link');
        
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
            
            // Update home links - only active if no section is active and near top
            if (!currentSection && scrollPosition < 500) {
                homeLinks.forEach(link => {
                    link.classList.remove('text-gray-700');
                    link.classList.add('text-blue-600', 'font-semibold');
                });
            } else {
                homeLinks.forEach(link => {
                    link.classList.remove('text-blue-600', 'font-semibold');
                    link.classList.add('text-gray-700');
                });
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
                slide.style.backgroundImage = 'linear-gradient(135deg, #9ca3af 0%, #6b7280 100%)';
                
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
