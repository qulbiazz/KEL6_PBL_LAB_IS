/**
 * Splide Slider Configuration for INLET Website
 * Better shadow handling and seamless scrolling
 */

document.addEventListener('DOMContentLoaded', function () {
    // Products Slider
    const productsSlider = document.querySelector('.splide.products-slider');
    if (productsSlider) {
        new Splide(productsSlider, {
            type: 'loop',
            perPage: 3,
            perMove: 1,
            gap: '2rem',
            padding: '0',
            pagination: true,
            arrows: true,
            autoplay: false,
            pauseOnHover: true,
            pauseOnFocus: true,
            trimSpace: false,
            breakpoints: {
                1024: {
                    perPage: 2,
                    gap: '1.5rem'
                },
                768: {
                    perPage: 1,
                    gap: '1rem'
                }
            }
        }).mount();
    }

    // News Slider
    const newsSlider = document.querySelector('.splide.news-slider');
    if (newsSlider) {
        new Splide(newsSlider, {
            type: 'loop',
            perPage: 3,
            perMove: 1,
            gap: '2rem',
            padding: '0',
            pagination: true,
            arrows: true,
            autoplay: false,
            pauseOnHover: true,
            pauseOnFocus: true,
            trimSpace: false,
            breakpoints: {
                1024: {
                    perPage: 2,
                    gap: '1.5rem'
                },
                768: {
                    perPage: 1,
                    gap: '1rem'
                }
            }
        }).mount();
    }

    // Research Slider
    const researchSlider = document.querySelector('.splide.research-slider');
    if (researchSlider) {
        new Splide(researchSlider, {
            type: 'loop',
            perPage: 3,
            perMove: 1,
            gap: '2rem',
            padding: '0',
            pagination: true,
            arrows: true,
            autoplay: false,
            pauseOnHover: true,
            pauseOnFocus: true,
            trimSpace: false,
            breakpoints: {
                1024: {
                    perPage: 2,
                    gap: '1.5rem'
                },
                768: {
                    perPage: 1,
                    gap: '1rem'
                }
            }
        }).mount();
    }

    // Team Slider - Only initialize if more than 4 members
    const teamSlider = document.querySelector('.splide.team-slider');
    if (teamSlider) {
        const teamCount = parseInt(teamSlider.getAttribute('data-team-count') || '0');
        
        // Only mount slider if there are more than 4 team members
        if (teamCount > 4) {
            new Splide(teamSlider, {
                type: 'loop',
                perPage: 4,
                perMove: 1,
                gap: '1.5rem',
                padding: {
                    left: '1rem',
                    right: '1rem'
                },
                pagination: true,
                arrows: true,
                autoplay: true,
                interval: 3000,
                pauseOnHover: true,
                pauseOnFocus: true,
                breakpoints: {
                    1280: {
                        perPage: 3,
                        gap: '1.25rem'
                    },
                    1024: {
                        perPage: 3,
                        gap: '1rem'
                    },
                    768: {
                        perPage: 2,
                        gap: '1rem'
                    },
                    640: {
                        perPage: 1,
                        gap: '1rem'
                    }
                }
            }).mount();
        }
    }
});
