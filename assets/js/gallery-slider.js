document.addEventListener('DOMContentLoaded', function () {
    const swiperThumbs = new Swiper('.swiper-thumbs', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
    });

    const swiperMain = new Swiper('.swiper-main', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: swiperThumbs,
        },
    });

    Fancybox.bind('[data-fancybox="gallery"]', {
        // Your custom options
    });
});