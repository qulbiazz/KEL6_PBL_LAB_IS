// Carousel functionality with animations
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.carousel-slide');
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    let currentSlide = 0;
    let typingTimeout = null;
    let autoSlideInterval = null;
    const SLIDE_DURATION = 7000; // 7 seconds per slide
    
    if (slides.length === 0) return;
    
    // Check if single banner - hide controls and disable auto-slide
    const isSingleBanner = slides.length === 1;
    
    if (isSingleBanner) {
        // Hide carousel controls for single banner
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
    }
    
    // Typing animation function
    function typeText(element, text, speed = 50) {
        return new Promise((resolve) => {
            let i = 0;
            element.textContent = '';
            
            function type() {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                    typingTimeout = setTimeout(type, speed);
                } else {
                    resolve();
                }
            }
            type();
        });
    }
    
    // Animate banner content
    async function animateBannerContent(slide) {
        const title = slide.querySelector('.banner-title');
        const typingText = slide.querySelector('.typing-text');
        const cursor = slide.querySelector('.typing-cursor');
        const description = slide.querySelector('.banner-description');
        const button = slide.querySelector('.banner-button');
        
        // Reset animations
        if (title) {
            title.classList.remove('opacity-0');
            title.classList.add('animate-slideInLeft');
        }
        
        // Typing animation for title
        if (typingText && typingText.dataset.text) {
            if (cursor) cursor.classList.add('animate-blink');
            await typeText(typingText, typingText.dataset.text, 40);
            
            // Hide cursor after typing
            setTimeout(() => {
                if (cursor) cursor.classList.add('opacity-0');
            }, 500);
        }
        
        // Slide in description
        if (description) {
            setTimeout(() => {
                description.classList.remove('opacity-0');
                description.classList.add('animate-slideInUp');
            }, 200);
        }
        
        // Slide in button
        if (button) {
            setTimeout(() => {
                button.classList.remove('opacity-0', 'translate-y-4');
                button.classList.add('animate-fadeInUp');
            }, 600);
        }
    }
    
    // Reset animations for a slide
    function resetAnimations(slide) {
        const title = slide.querySelector('.banner-title');
        const typingText = slide.querySelector('.typing-text');
        const cursor = slide.querySelector('.typing-cursor');
        const description = slide.querySelector('.banner-description');
        const button = slide.querySelector('.banner-button');
        
        // Clear any ongoing typing
        if (typingTimeout) {
            clearTimeout(typingTimeout);
            typingTimeout = null;
        }
        
        if (title) {
            title.classList.add('opacity-0');
            title.classList.remove('animate-slideInLeft');
        }
        if (typingText) typingText.textContent = '';
        if (cursor) {
            cursor.classList.remove('opacity-0', 'animate-blink');
        }
        if (description) {
            description.classList.add('opacity-0');
            description.classList.remove('animate-slideInUp');
        }
        if (button) {
            button.classList.add('opacity-0', 'translate-y-4');
            button.classList.remove('animate-fadeInUp');
        }
    }
    
    // Reset auto-slide timer
    function resetAutoSlideTimer() {
        // Only for multiple banners
        if (isSingleBanner) return;
        
        // Clear existing interval
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
            autoSlideInterval = null;
        }
        
        // Start fresh interval
        autoSlideInterval = setInterval(nextSlide, SLIDE_DURATION);
    }
    
    function showSlide(n) {
        // Reset current slide animations
        resetAnimations(slides[currentSlide]);
        
        slides.forEach(slide => slide.classList.remove('active'));
        
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        
        // Animate new slide content
        setTimeout(() => {
            animateBannerContent(slides[currentSlide]);
        }, 300);
        
        // Reset timer when slide changes (only for multiple banners)
        resetAutoSlideTimer();
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    // Event listeners for controls (only for multiple banners)
    if (!isSingleBanner) {
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    }
    
    // Initial animation for first slide
    setTimeout(() => {
        animateBannerContent(slides[0]);
    }, 500);
    
    // Start auto-slide only for multiple banners
    if (!isSingleBanner) {
        autoSlideInterval = setInterval(nextSlide, SLIDE_DURATION);
    }
});
