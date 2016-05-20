// JQuery Stuff
$(document).ready(function() {

    // Body Fade
    // $("body").css("display", "none");
    $("body").fadeIn(200); 

    // Compose note
    $('#challenges_page_create_button').click(function() {
       //  $('#fpNewThreadDiv').toggle('drop', {direction: 'right'}, 150);
        $("#challenges_page_create_wrap").slideToggle(300, function() {
        });
    });


    // Comment Fade
    //$(".tr").css("display","none");
    //$(".tr").hide().each(function(i) {
    //  $(this).delay(i*100).slideToggle(600);
    //});
    // $(".commentWrap").fadeIn(800);

    // Profiles
    $(".user_page_top_avatar_div>img").each(function(i, img) {
        $(img).css({
            position: "relative",
            left: ($(img).parent().width()/2) - ($(img).width()/2)
        });
    });

    // Realign image in div
    $(".challenge_thumbnail_div>img").each(function(i, img) {
        $(img).css({
            position: "relative",
            left: ($(img).parent().width()/2) - ($(img).width()/2)
        });
    });


    // Compose note
    $('#mailbox_compose_button').click(function() {
       //  $('#fpNewThreadDiv').toggle('drop', {direction: 'right'}, 150);
        $("#mailbox_compose_div").slideToggle(300, function() {
        });
    });

    // Forum Thread Button
    $('#forum_main_new_thread_button').click(function() {
       //  $('#fpNewThreadDiv').toggle('drop', {direction: 'right'}, 150);
        $("#forum_main_new_thread_div").slideToggle(500, function() {
        });
    });

    // Forum Thread Button
    $('#fpNewThreadButton').click(function() {
       //  $('#fpNewThreadDiv').toggle('drop', {direction: 'right'}, 150);
        $("#fpNewThreadDiv").slideToggle(500, function() {
        });
    });

    // Cancel
    $('#deletethreadcancel').click(function() {
        //$('#deletethread').toggle(400);
        $("#deletethread").slideToggle(800, function() {
        });
    });

    // Mailbox Delete
    $('#deletethreadlink').click(function() {
        //$('#deletethread').toggle(400);
        $("#deletethread").slideToggle(300, function() {
        });
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
function sbc_button_sumbit_disable()
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
        username_url    = '<a href="https://www.sketchbook.cafe/u/' + username + '/" class="' + f_class + '">' + username + '</a>';
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
            avatar_url  = '<a href="https://www.sketchbook.cafe/u/' + username + '/"><img src="https://www.sketchbook.cafe/' + avatar_url + '" class="' + avatar_class + '"></a>';
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


// Date Ago
function sbc_dateago_calc(current_date, old_date)
{
    var seconds = Math.floor(current_date - old_date);

    var year    = 31536000;
    var month   = 2592000;
    var week    = 604800;
    var day     = 86400;
    var hour    = 3600;
    var minute  = 60;

    // Years
    interval = Math.floor(seconds / year);
    if (interval >= 1)
    {
        if (interval != 1)
        {
            return interval + ' years ago ';
        }
        else
        {
            return interval + ' year ago';
        }
    }

    // Months
    interval = Math.floor(seconds / month);
    if (interval >= 1)
    {
        if (interval != 1)
        {
            return interval + ' months ago ';
        }
        else
        {
            return interval + ' month ago';
        }
    }

    // Weeks
    interval = Math.floor(seconds / week);
    if (interval >= 1)
    {
        if (interval != 1)
        {
            return interval + ' weeks ago ';
        }
        else
        {
            return interval + ' week ago';
        }
    }

    // Days
    interval = Math.floor(seconds / day);
    if (interval >= 1)
    {
        if (interval != 1)
        {
            return interval + ' days ago ';
        }
        else
        {
            return interval + ' day ago';
        }
    }

    // Hours
    interval = Math.floor(seconds / hour);
    if (interval >= 1)
    {
        if (interval != 1)
        {
            return interval + ' hours ago ';
        }
        else
        {
            return interval + ' hour ago';
        }
    }

    // Minutes
    interval = Math.floor(seconds / minute);
    if (interval >= 1)
    {
        if (interval != 1)
        {
            return interval + ' minutes ago ';
        }
        else
        {
            return interval + ' minute ago';
        }
    }

    // Seconds
    interval = Math.floor(seconds);
    if (interval >= 1)
    {
        if (interval != 1)
        {
            return interval + ' seconds ago ';
        }
        else
        {
            return interval + ' second ago';
        }
    }
}

function sbc_dateago(current_date, old_date)
{
    // Calculate
    var value = '';
    value = sbc_dateago_calc(current_date, old_date);

    document.write(value);
}

// Simple Number Formatting
function sbc_number_format_calc(numberString)
{
    numberString += '';
    var x = numberString.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    rgxp = /(\d+)(\d{3})/;

    // Format Number
	while (rgxp.test(x1)) {
		x1 = x1.replace(rgxp, '$1' + ',' + '$2');
	}

    return x1 + x2;
}
function sbc_number_format(numberString)
{
    var value = '';
    value = sbc_number_format_calc(numberString);
    document.write(value);
}

// Number Display
function sbc_number_display(numberValue,name1,name2)
{
    var name = '';
    var value = '';
    if (numberValue != 1)
    {
        name = name2;
    }
    else
    {
        name = name1;
    }

    // Calculate
    value = sbc_number_format_calc(numberValue);

    document.write(value + ' ' + name);
}

// Ajax Submit Form
function sbc_ajax_submit(page_url,f_window,id)
{
    var xmlhttp;

    if (window.XMLHttpRequest)
    {
        xmlhttp = new XMLHttpRequest();
    }
    else
    {
        xmlhttp = new ActiveObject("Microsoft.XMLHTTP");
    }

    // Window
    xmlhttp.onreadystatechange = function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
        {
            document.getElementById(f_window).innerHTML = xmlhttp.responseText;
        }
    }

    // Message
    var message = document.getElementById('textarea_commenteditform'+id+'_message').value;
    message = encodeURIComponent(message);

    // Get
    xmlhttp.open("POST",page_url,true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send('message=' + message);
}

// Ajax Get Form
function sbc_ajax_form(page_url,f_window)
{
    var xmlhttp;

    if (window.XMLHttpRequest)
    {
        xmlhttp = new XMLHttpRequest();
    }
    else
    {
        xmlhttp = new ActiveObject("Microsoft.XMLHTTP");
    }

    // Window
    xmlhttp.onreadystatechange = function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
        {
            document.getElementById(f_window).innerHTML = xmlhttp.responseText;
        }
    }

    // Get
    xmlhttp.open("GET",page_url,true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send('');
}

// Lock Post
function sbc_thread_lockpost(comment_id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/lockpost/' + comment_id + '/';
    var f_window = 'lockcomment' + comment_id;
    return sbc_ajax_form(page_url,f_window);
}

// Edit Entry Form
function sbc_thread_editentry_form(entry_id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/edit_entry/' + entry_id + '/';
    var f_window = 'edit_entry_window' + entry_id;
    return sbc_ajax_form(page_url,f_window);
}

// Thread: Edit Title
function sbc_thread_edittitle(thread_id,comment_id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/edit_threadtitle/' + thread_id + '/';
    var f_window = 'edit_comment_window' + comment_id;
    return sbc_ajax_form(page_url,f_window);
}

// Thread: Sticky Form (no longer used)
function sbc_thread_sticky_form(id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/sticky_thread/' + id + '/';
    var f_window = 'edit_comment_window' + id;
    return sbc_ajax_form(page_url,f_window);
}

// Thread: Delete
function sbc_thread_deletethread(thread_id,comment_id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/delete_thread/' + thread_id + '/';
    var f_window = 'edit_comment_window' + comment_id;
    return sbc_ajax_form(page_url,f_window);
}

// Edit Comment Form
function sbc_edit_comment_form(id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/edit_comment/' + id + '/';
    var f_window = 'edit_comment_window' + id;
    return sbc_ajax_form(page_url,f_window);
}

// Edit Submit Form
function sbc_edit_submit_form(id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/edit_comment_submit/' + id + '/';
    var f_window = 'edit_comment_window' + id;
    return sbc_ajax_submit(page_url,f_window,id);
}

// Delete Post Form
function sbc_delete_comment_form(id)
{
    var page_url = 'https://www.sketchbook.cafe/ajax/deletepost/' + id + '/';
    var f_window = 'delete_comment_window' + id;
    return sbc_ajax_form(page_url,f_window);
}

// Master Image
function sbc_image(f_id,f_class)
{
    var output      = '';
    var i_url       = '';
    var t_url       = '';

    // Is there an image?
    if (image_id[f_id] > 0)
    {
        // Set Vars
        i_url = image_url[f_id];
        t_url = image_thumb[f_id];

        // Full Image
        if (image_s3[f_id] == 1)
        {
            i_url = i_url;
        }
        else
        {
            i_url = 'https://www.sketchbook.cafe/' + i_url;
        }

        // Full Image
        output = '<div><img src="' + i_url + '" class="' + f_class + '"></img></div>';
    }

    document.write(output);
}

// Thumbnail to Image
function sbc_thumbnail_image(f_id, f_class)
{
    var output      = '';
    var i_url       = '';
    var t_url       = '';

    // Is there an image?
    if (image_id[f_id] > 0)
    {
        // Set Vars
        i_url = image_url[f_id];

        // Full Image
        if (image_s3[f_id] == 1)
        {
            new_img = i_url;
        }
        else
        {
            new_img = 'https://www.sketchbook.cafe/' + i_url;
        }

        // Replace image
        document.getElementById('thumb' + f_id).src = new_img;
    }
}

// Challenge Thumbnail
function sbc_challenge_thumbnail(f_id)
{
    var output      = '';
    var t_url       = '';

    // Is there an image?
    if (image_id[f_id] > 0)
    {
        // Set Vars
        t_url = image_thumb[f_id];

        // Full Image
        output = '<img src="https://www.sketchbook.cafe/' + t_url + '" class="challenge_thumbnail_img">';
    }

    document.write(output);
}

// Master Thumbnail
function sbc_thumbnail(f_id,f_class)
{
    var output      = '';
    var i_url       = '';
    var t_url       = '';

    // Is there an image?
    if (image_id[f_id] > 0)
    {
        // Set Vars
        i_url = image_url[f_id];
        t_url = image_thumb[f_id];

        // Full Image
        output = '<img id="thumb' + f_id + '" src="https://www.sketchbook.cafe/' + t_url + '" class="thumb_img ' + f_class + '" onClick="sbc_thumbnail_image(' + f_id + ',\'\'); return false;">';
    }

    document.write(output);
}