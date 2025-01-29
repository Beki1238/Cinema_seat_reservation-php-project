let header = document.querySelector('header');
let menu = document.querySelector('#menu-icon');
let navbar = document.querySelector('.navbar');

window.addEventListener('scroll',()=>{
    header.classList.toggle('shadow', window.scrollY > 0);
});
menu.onclick =()=>{
 menu.classList.toggle('bx-x');
 navbar.classList.toggle('active');
}

window.onscroll = () =>{
    menu.classList.remove('bx-x');
    navbar.classList.remove('active');

}

var swiper = new Swiper(".home ",{
    spaceBetween: 30,
    centeredSlides: true,
    autoplay: {
        delay: 2500,
        disableOnIntaraction:false,
    },

    pagination:{
        el: ".swiper-pagination",
        clickable: true,
    },
   
});

var swiper = new Swiper(".coming-container",{
    spaceBetween: 20,
    loop: true,
    // centeredSlides: true,
    autoplay: {
        delay: 55000,
        disableOnIntaraction:false,
    },
    centeredSlides:true,
    breakpoints:{
        0:{
            slidesPerview:2,
        },
        568:{
            slidesPerview:3,
        },
        768:{
            slidesPerview:4,
        },
        968:{
            slidesPerview:5,
        },
    },
   
});
const swiper = new Swiper('.swiper', {
    loop: true, // Enable looping
    autoplay: {
        delay: 3000, // 3 seconds delay between slides
        disableOnInteraction: false, // Continue autoplay after interaction
    },
    pagination: {
        el: '.swiper-pagination', // Pagination bullets
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next', // Next button
        prevEl: '.swiper-button-prev', // Previous button
    },
});
// $(document).ready(function(){
//     $('.movie-slider').slick({
//         slidesToShow: 5,  // Number of movies shown at once
//         slidesToScroll: 1, // Scroll one movie at a time
//         infinite: true,    // Enable infinite scrolling
//         speed: 500,        // Slide transition speed
//         arrows: true,      // Show next/prev arrows
//         prevArrow: '<button type="button" class="slick-prev">←</button>', // Custom prev arrow
//         nextArrow: '<button type="button" class="slick-next">→</button>', // Custom next arrow
//         responsive: [
//             {
//                 breakpoint: 1024,
//                 settings: {
//                     slidesToShow: 3, // Show 3 movies on medium screens
//                     slidesToScroll: 1,
//                 }
//             },
//             {
//                 breakpoint: 600,
//                 settings: {
//                     slidesToShow: 1, // Show 1 movie on small screens
//                     slidesToScroll: 1,
//                 }
//             }
//         ]
//     });
// });


