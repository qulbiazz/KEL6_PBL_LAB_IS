// Team Slider with Swiper.js
document.addEventListener('DOMContentLoaded', function() {
    const teamSwiper = new Swiper('.teamSwiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        pagination: {
            el: '.team-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: '.team-button-next',
            prevEl: '.team-button-prev',
        },
        breakpoints: {
            // Mobile
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            // Tablet
            768: {
                slidesPerView: 3,
                spaceBetween: 25,
            },
            // Desktop - Maximum 4 slides
            1024: {
                slidesPerView: 4,
                spaceBetween: 30,
            },
        },
    });

    // Products Slider with Swiper.js
    const productsSwiper = new Swiper('.productsSwiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoHeight: false,
        watchSlidesProgress: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        pagination: {
            el: '.products-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: '.products-button-next',
            prevEl: '.products-button-prev',
        },
        breakpoints: {
            // Mobile
            640: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            // Tablet
            768: {
                slidesPerView: 2,
                spaceBetween: 25,
            },
            // Desktop
            1024: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
        },
        on: {
            init: function () {
                equalizeSlideHeights(this);
            },
            resize: function () {
                equalizeSlideHeights(this);
            },
        },
    });

    // News Slider with Swiper.js
    const newsSwiper = new Swiper('.newsSwiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoHeight: false,
        watchSlidesProgress: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        pagination: {
            el: '.news-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: '.news-button-next',
            prevEl: '.news-button-prev',
        },
        breakpoints: {
            // Mobile
            640: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            // Tablet
            768: {
                slidesPerView: 2,
                spaceBetween: 25,
            },
            // Desktop
            1024: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
        },
        on: {
            init: function () {
                equalizeSlideHeights(this);
            },
            resize: function () {
                equalizeSlideHeights(this);
            },
        },
    });

    // Research Slider with Swiper.js
    const researchSwiper = new Swiper('.researchSwiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoHeight: false,
        watchSlidesProgress: true,
        autoplay: {
            delay: 4500,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        pagination: {
            el: '.research-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: '.research-button-next',
            prevEl: '.research-button-prev',
        },
        breakpoints: {
            // Mobile
            640: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            // Tablet
            768: {
                slidesPerView: 2,
                spaceBetween: 25,
            },
            // Desktop
            1024: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
        },
        on: {
            init: function () {
                equalizeSlideHeights(this);
            },
            resize: function () {
                equalizeSlideHeights(this);
            },
        },
    });

    // Function to equalize slide heights
    function equalizeSlideHeights(swiper) {
        const slides = swiper.slides;
        if (!slides || slides.length === 0) return;
        
        let maxHeight = 0;
        
        // Reset heights first
        slides.forEach(slide => {
            const card = slide.querySelector('.bg-white');
            if (card) {
                card.style.height = 'auto';
            }
        });
        
        // Find max height
        slides.forEach(slide => {
            const card = slide.querySelector('.bg-white');
            if (card) {
                const height = card.offsetHeight;
                if (height > maxHeight) {
                    maxHeight = height;
                }
            }
        });
        
        // Set all to max height
        if (maxHeight > 0) {
            slides.forEach(slide => {
                const card = slide.querySelector('.bg-white');
                if (card) {
                    card.style.height = maxHeight + 'px';
                }
            });
        }
    }
});
