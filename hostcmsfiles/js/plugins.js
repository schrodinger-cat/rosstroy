// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeline', 'timelineEnd', 'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

// Place any jQuery/helper plugins in here.

$(document).ready(function(){
    $('.r-content__text').mCustomScrollbar({
        axis: 'y',
        theme:"dark",
        scrollButtons: {
            enable: true
        }
    });


    $('.r-portrets__slider').bxSlider({
        pager: false,
        nextSelector: '.r-portrets__next',
        prevSelector: '.r-portrets__prev',
        nextText: '',
        prevText: ''
    });

    $('.r-nom').bxSlider({
        pager: false,
        minSlides: 2,
        maxSlides: 2,
        slideWidth: 200,
        slideMargin: 30,
        nextSelector: '.r-nom__next',
        prevSelector: '.r-nom__prev',
        nextText: '',
        prevText: ''
    });

    $(".fancybox").fancybox({
        padding: 0,
        arrows: true
    });
});
