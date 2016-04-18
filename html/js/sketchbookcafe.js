// JQuery Stuff
$(document).ready(function() {
    // Cancel
    $('#deletethreadcancel').click(function() {
        $('#deletethread').toggle(400);
    });

    // Mailbox Delete
    $('#deletethreadlink').click(function() {
        $('#deletethread').toggle(400);
    });

    // Header Menu
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

// Element
function sbc_element(element)
{
    return document.getElementById(element);
}

// Upload File
function sbc_upload_file(imagefile_id,post_url)
{
    // Get file(s)
    var file = sbc_element(imagefile_id).files[0];
    // alert (file.name + " | " + file.size + " | | " + file.type);

    // Form data
    var formdata = new FormData();
    formdata.append(imagefile_id, file);

    // Unhide Progress
    var hidden_div = imagefile_id + '_upload';
    obj = sbc_element(hidden_div);
    obj.style.display = '';

    // Ajax
    var ajax = new XMLHttpRequest();
    ajax.upload.addEventListener("progress", sbc_upload_file_progress_handler, false);
    ajax.addEventListener("load", sbc_upload_file_complete_handler, false);
    ajax.addEventListener("error", sbc_upload_file_error_handler, false);
    ajax.addEventListener("abort", sbc_upload_file_abort_handler, false);

    // Send
    ajax.open("POST", post_url);
    ajax.send(formdata);
}

// Progress Hander
function sbc_upload_file_progress_handler(event)
{
    // Bytes
    sbc_element("loaded_n_total").innerHTML = "Uploaded " + event.loaded + " bytes of " + event.total;

    // Percent
    var percent = (event.loaded / event.total) * 100;
    sbc_element("progressBar").value = Math.round(percent);
    sbc_element("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

// Complete Handler
function sbc_upload_file_complete_handler(event)
{
	// sbc_element("status").innerHTML = event.target.responseText;
    sbc_element("status").innerHTML = 'Complete!';
	sbc_element("progressBar").value = 0;

    // String Value
    var rvalue = event.target.responseText;

    // Code?
    var code = rvalue.substr(0,5);
    if (code == 'r1000')
    {
        // URL
        var url = rvalue.substr(6);

        // Redirect
        window.location.replace(url);
    }
    else
    {
        sbc_element("status").innerHTML = 'Server Message: "' + rvalue + '"';
        // sbc_element("progressBar").innerHTML = '';
        // alert('Server Message: "' + rvalue + '"');
    }
}

// Error Handler
function sbc_upload_file_error_handler(event)
{
	sbc_element("status").innerHTML = "Upload Failed";
}

// Abort Handler
function sbc_upload_file_abort_handler(event)
{
	sbc_element("status").innerHTML = "Upload Aborted";
}

// Comment Preview
function comment_preview(textarea_id, setting_name)
{
	var xmlhttp;

	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById(textarea_id+'_preview').innerHTML=xmlhttp.responseText;
		}
	}

	var message = document.getElementById(textarea_id).value;
    message = encodeURIComponent(message);

	xmlhttp.open("POST","https://www.sketchbook.cafe/preview_comment.php",true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send("setting_name="+setting_name+"&message="+message);
}

// Master Username
// Uses javascript vars:    member_id, member_username
function sbc_username(user_id, f_class)
{
    var output          = '';
    var username        = '';
    var username_url    = '';

    // Is there a member?
    if (member_id[user_id] > 0)
    {
        // Set vars
        username        = member_username[user_id];
        username_url    = '<a href="#" class="' + f_class + '">' + username + '</a>';
    }

    // Write
    document.write(username_url);
}

// Master Avatar
// Uses javascript vars:    member_id, member_username, member_avatar_url
function sbc_avatar(user_id,f_class)
{
    var output          = '';
    var avatar_url      = '';
    var username        = '';
    var avatar_class    = '';

    // Is there a member?
    if (member_id[user_id] > 0)
    {
        // Set Vars
        username    = member_username[user_id];
        avatar_url  = member_avatar_url[user_id];

        // Class?
        if (f_class == '')
        {
            avatar_class = 'avatar';
        }
        else
        {
            avatar_class = f_class;
        }

        // Full Avatar
        if (avatar_url != '')
        {
            avatar_url  = '<a href="#"><img src="https://www.sketchbook.cafe/' + avatar_url + '" class="' + avatar_class + '"></a>';
        }
    }

    // Write Avatar
    document.write(avatar_url);
}

// SBC Page Numbers
// Used for mailbox and forums
function sbc_numbered_links(url,ppage,posts,css)
{
    var page_total = Math.ceil(posts/ppage);
    var max = 3;
    var min = page_total - 3;
    var x = 0;
    var new_url = '';

    // Do we have any pages?
    if (page_total > 1)
    {
        // Less than 6
        if (page_total <= 6)
        {
            // Loop
            while (x < page_total)
            {
                // Set New Url
                new_url = new_url + '<a href="' + url + x + '" class="' + css +'">' + (x + 1) + '</a>';

                // Commas
                if (x < (page_total -1))
                {
                    new_url = new_url + ', ';
                }
                x++;
            }
        }
        else if (page_total >= 7)
        {
            // Loop
            while (x < max)
            {
                // New URL
                new_url = new_url + '<a href="' + url + x + '" class="' + css +'">' + (x + 1) + '</a>';

                // Commas
                if (x < max)
                {
                    new_url = new_url + ', ';
                }
                x++;
            }

            // Add dots
            new_url = new_url + '..., ';

            // Min Loop
            while (min < page_total)
            {
                // New Url
                new_url = new_url + '<a href="' + url + min + '" class="' + css +'">' + (min + 1) + '</a>';

                // Commas
                if (min < (page_total - 1))
                {
                    new_url = new_url + ', ';
                }

                min++;
            }
        }
    }

    // Write
    document.write(new_url);
}