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
});
