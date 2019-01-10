$(window).on('load', function() {

    //force page scroll position to top at page refresh
    // $('html, body').animate({ scrollTop: 0 }, 'normal');

    // will first fade out the loading animation
    $(".square").fadeOut("slow", function() {
        // will fade out the whole DIV that covers the website.
        $("#preLoader").delay(300).fadeOut("slow", function(){
            $(".form").fadeIn().addClass("animated slideInUp d-block");
        });
    });

});
