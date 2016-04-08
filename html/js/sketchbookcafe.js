// Header Menu
$(document).ready(function() {
    $('#headerUserWrap').mouseover(function() {
        $('#headerUserMenu').stop().slideDown(133);

    });

    $('#headerUserWrap').mouseout(function() {
        $('#headerUserMenu').stop().slideUp(133);

    });
});