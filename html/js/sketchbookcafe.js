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

// Form: On Key Press
function formOnKeyPress (id, f_submit_inactive)
{
    // Set values
    var submit_text = f_submit_inactive;
    if (submit_text === undefined || submit_text === null)
    {
        submit_text = 'Submit'; // default value;
    }

    // Allow submit
    document.getElementById(id).disabled = 0;

    // Change submit text
    document.getElementById(id).value = submit_text;
}

// Form: On Submit
function formOnSubmit (id, f_submit_active)
{
    // Set values
    var submit_text = f_submit_active;
    if (submit_text === undefined || submit_text === null)
    {
        submit_text = 'Submitting...'; // default
    }

    // Disable submit
    document.getElementById(id).disabled = 1;

    // Change submit text
    document.getElementById(id).value = submit_text;
}

// Hideshow
function hideshow (id)
{
    if (document.getElementById)
    {
        obj = document.getElementById(id);
        if (obj.style.display == 'none')
        {
            obj.style.display = '';
        } else {
            obj.style.display = 'none';
        }
    }
}