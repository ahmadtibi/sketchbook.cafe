// Header Menu
$(document).ready(function() {
    $('#headerUserWrap').mouseover(function() {
        $('#headerUserMenu').stop().slideDown(133);

    });

    $('#headerUserWrap').mouseout(function() {
        $('#headerUserMenu').stop().slideUp(133);

    });
});

// Disable All Buttons
function sbc_button_sumbit_enable()
{
    var inputs = document.getElementsByTagName("INPUT");
    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].type === 'submit' || inputs[i].type === 'button')
        {
            inputs[i].disabled = true;
        }
    }
}

// Enable all buttons
function sbc_button_sumbit_enable()
{
    var inputs = document.getElementsByTagName("INPUT");
    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].type === 'submit' || inputs[i].type === 'button')
        {
            inputs[i].disabled = false;
        }
    }
}