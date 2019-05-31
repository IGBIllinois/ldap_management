function toggleClasses(e, onClass, offClass) {
    if (e.hasClass(onClass)) {
        e.addClass(offClass);
        e.removeClass(onClass);
    } else {
        e.addClass(onClass);
        e.removeClass(offClass);
    }
}

function sort_table(column) {
    // TODO there's got to be a better way to do this than regex
    // Build new url
    const currentURL = window.location.href;
    const URLMatch = currentURL.match(/sort=(.*?)(&|$)/);
    if (URLMatch == null) {
        // Add sort to the end of the string
        if (currentURL.indexOf('?') > -1) {
            window.location.href = currentURL + "&sort=" + column;
        } else {
            window.location.href = currentURL + "?sort=" + column;
        }
    } else {
        // Replace in place
        const ascMatch = currentURL.match(/asc=(.*?)(&|$)/);
        if (URLMatch[1] === column) {
            // Clicked twice, flip asc
            if (ascMatch == null) {
                window.location.href = currentURL + "&asc=0";
            } else if (ascMatch[1] === "1") {
                window.location.href = currentURL.replace(/asc=(.*?)(&|$)/g, "asc=0");
            } else {
                window.location.href = currentURL.replace(/asc=(.*?)(&|$)/g, "asc=1");
            }
        } else {
            // New column, default asc
            if (ascMatch == null) {
                window.location.href = currentURL.replace(/sort=(.*?)(&|$)/g, "sort=" + column + "$2");
            } else {
                window.location.href = currentURL.replace(/sort=(.*?)(&|$)/g, "sort=" + column + "$2").replace(/asc=(.*?)(&|$)/g, "asc=true");
            }
        }
    }
}

function filter_table(filter) {
    // Build new URL
    const currentURL = window.location.href;
    const URLMatch = currentURL.match(/filter=(.*?)(&|$)/);
    if (URLMatch == null) {
        // Add filter to the end of the URL
        if (currentURL.indexOf('?') > -1) {
            window.location.href = currentURL + "&filter=" + filter;
        } else {
            window.location.href = currentURL + "?filter=" + filter;
        }
    } else {
        // Replace filter in place
        if (URLMatch[1] === filter) {
            // Clicked twice, no filter
            window.location.href = currentURL.replace(/filter=(.*?)(&|$)/g, "filter=none$2");
        } else {
            window.location.href = currentURL.replace(/filter=(.*?)(&|$)/g, "filter=" + filter + "$2");
        }
    }
}

// TODO validation needs to be a bit more flexible
function show_add_classroom_text() {
    const prefix = $('#prefix_input').val();
    const start = $('#start_input').val();
    const end = $('#end_input').val();
    const paddedStart = start < 10 ? '0' + start : start;
    const paddedEnd = end < 10 ? '0' + end : end;

    const rule2 = Number(start) > 0;
    const rule3 = Number(end) > Number(start);

    let error = prefix + paddedStart + '-' + prefix + paddedEnd + ' will be created';
    if (prefix === "") {
        error = "Please enter a prefix.";
    } else if (isNaN(start) || Number(start) < 1) {
        error = "Start must be >=1";
    } else if (isNaN(end) || Number(end) <= Number(start)) {
        error = "End must be greater than start";
    }

    showValidateError('username',1, rule2 && rule3, error);
    document.getElementById('submit').disabled = !(rule2 && rule3);
}

function show_remove_classroom_text() {
    const prefix = $('#prefix_input').val();
    const start = $('#start_input').val();
    const end = $('#end_input').val();
    const paddedStart = start < 10 ? '0' + start : start;
    const paddedEnd = end < 10 ? '0' + end : end;

    let rule1 = 0;
    $.ajax('check_username.php', {
        async: false,
        data: {'username': prefix + paddedStart},
        method: 'POST',
        success: function (data) {
            rule1 = data;
        }
    });
    rule1 = rule1 == 1;

    const rule2 = Number(start) > 0;
    const rule3 = Number(end) > Number(start);

    console.log(start, isInt(start));

    let error = prefix + paddedStart + '-' + prefix + paddedEnd + ' will be removed';
    if (prefix == "") {
        error = "Please enter a prefix.";
    } else if (isNaN(start) || Number(start) < 1) {
        error = "Start must be >=1";
    } else if (isNaN(end) || Number(end) <= Number(start)) {
        error = "End must be greater than start";
    } else if (!rule1) {
        error = prefix + paddedStart + " does not exist";
    }

    showValidateError('username', 1, rule1 && rule2 && rule3, error);
    document.getElementById('submit').disabled = !(rule1 && rule2 && rule3);
}

function copy_panel() {
    const $this = $(this);
    const $textarea = $this.parents('.content').find('.copy-text');
    let showTextArea = true;
    if (document.queryCommandSupported('copy')) {
        showTextArea = false;
        console.log($textarea);
        $textarea.removeClass('d-none');
        $textarea[0].select();

        try {
            document.execCommand('copy');
        } catch (err) {
            showTextArea = true;
        }

        $textarea.addClass('d-none');
        window.getSelection().removeAllRanges();
    }
    if (showTextArea) {
        $textarea.removeClass('d-none');
    }
}

function showUsernameError(errorNum, valid, text) {
    if (valid) {
        $('#usernameerror' + errorNum).removeClass('text-danger').addClass('text-success');
        $('#usernameerror' + errorNum + ' .fa').removeClass('fa-times').addClass('fa-check');
    } else {
        $('#usernameerror' + errorNum).removeClass('text-success').addClass('text-danger');
        $('#usernameerror' + errorNum + ' .fa').removeClass('fa-check').addClass('fa-times');
    }
    $('#usernameerror' + errorNum + ' .text').html(text);
}


function showValidateError(field, errorNum, valid, text) {
    if (!($('#validation p#' + field + 'error' + errorNum).length)) {
        $('#validation').append('<p id=' + field + 'error' + errorNum + '><span class="fa"></span> <span class="text"></span></p>');
    }
    if (valid) {
        $('#' + field + 'error' + errorNum).removeClass('text-danger').addClass('text-success');
        $('#' + field + 'error' + errorNum + ' .fa').removeClass('fa-times').addClass('fa-check');
    } else {
        $('#' + field + 'error' + errorNum).removeClass('text-success').addClass('text-danger');
        $('#' + field + 'error' + errorNum + ' .fa').removeClass('fa-check').addClass('fa-times');
    }
    $('#' + field + 'error' + errorNum + ' .text').html(text);
}

function showValidateWarning(field, warnNum, valid, text) {
    if (!($('#validation p#' + field + 'warning' + warnNum).length)) {
        $('#validation').append('<p id=' + field + 'warning' + warnNum + '><span class="fa"></span> <span class="text"></span></p>');
    }
    if (valid) {
        $('#' + field + 'warning' + warnNum).removeClass('text-warning').addClass('text-success');
        $('#' + field + 'warning' + warnNum + ' .fa').removeClass('fa-exclamation-triangle').addClass('fa-check');
    } else {
        $('#' + field + 'warning' + warnNum).removeClass('text-success').addClass('text-warning');
        $('#' + field + 'warning' + warnNum + ' .fa').removeClass('fa-check').addClass('fa-exclamation-triangle');
    }
    $('#' + field + 'warning' + warnNum + ' .text').html(text);
}

function check_passwords() {
    const password = document.getElementById('password_input').value;
    const confirmPassword = document.getElementById('confirmPassword_input').value;

    const rule1 = (password.length >= 8 && password.length <= 127);
    const rule2 = (password.match(/[A-Z]/));
    const rule3 = (password.match(/[a-z]/));
    const rule4 = (password.match(/[^A-Za-z]/) && !password.match(/[\s]/));
    const rule5 = (password === confirmPassword);

    showValidateError('password', 1, rule1, "Password must be between 8 and 127 characters in length");
    showValidateError('password', 2, rule2, "Password must contain at least 1 uppercase letter");
    showValidateError('password', 3, rule3, "Password must contain at least 1 lowercase letter");
    showValidateError('password', 4, rule4, "Password must contain at least 1 number or special character (no spaces)");
    showValidateError('password', 5, rule5, "Password and confirm password must match");

    return rule1 && rule2 && rule3 && rule4 && rule5;
}

function check_email() {
    const email = document.getElementById('forwardingEmail_input').value;

    const rule1 = (email.length === 0 || email.match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+$/) != null);

    showValidateError('email', 1, rule1, rule1 ? "Valid forwarding email" : "Invalid forwarding email");

    return rule1;
}

let username_errors = false;
let email_errors = false;
let password_errors = false;

function add_user_errors(e) {
    if (typeof e == 'undefined') {
        username_errors = check_username();
        email_errors = check_email();
        password_errors = check_passwords();
    } else {
        if (e.target.name == 'username') {
            username_errors = check_username();
        }
        if (e.target.name == 'forwardingEmail') {
            email_errors = check_email();
        }
        if (e.target.name == 'password' || e.target.name == 'confirmPassword') {
            password_errors = check_passwords();
        }
    }
    console.log(username_errors, password_errors, email_errors);
    document.getElementById('submit').disabled = !(password_errors && username_errors && email_errors);
}

function change_emailforward_errors() {
    document.getElementById('change_emailforward_submit').disabled = !check_email();
}

function change_username_errors() {
    document.getElementById('change_username_submit').disabled = !check_username();
}

function change_group_errors() {
    const nameValid = check_groupname();
    const descValid = check_groupdescription();
    document.getElementById('submit').disabled = !(nameValid && descValid);
}

function change_groupName_errors() {
    document.getElementById('submit').disabled = !check_groupname();
}

function change_password_errors() {
    document.getElementById('submit').disabled = !check_passwords();
}

function check_username() {
    const username = document.getElementById('username_input').value;
    let warning1 = false;

    $.ajax('check_netid.php', {
        async: false,
        data: {'username': username},
        method: 'POST',
        success: function (data) {
            if (data === '1') {
                warning1 = true;
            }
        }
    });

    showValidateWarning('username', 1, warning1, warning1 ? "Username matches a UIUC netid" : "Username does not match a UIUC netid");
    if (document.getElementById('forwardingEmail') != null) {
        if (warning1) {
            document.getElementById('forwardingEmail').value = document.getElementById('username').value + "@illinois.edu";
        } else {
            if (document.getElementById('forwardingEmail').value.includes('@illinois.edu')) {
                document.getElementById('forwardingEmail').value = '';
            }
        }
    }

    let rule1 = -1;
    $.ajax('check_username.php', {
        async: false,
        data: {'username': username},
        method: 'POST',
        success: function (data) {
            rule1 = data;
        }
    });
    const rule2 = (username.match(/^[a-z]/) != null);
    const rule3 = (username.match(/[^a-z0-9_\-]/) == null);

    showValidateError('username', 1, rule1 == 0, rule1 == 0 ? "Username not in use" : (rule1 == 1 ? "Username already exists" : "Username exists as group"));
    showValidateError('username', 2, rule2, "Username must begin with a lowercase letter");
    showValidateError('username', 3, rule3, "Username must be alphanumeric (lowercase letters, numbers, underscore)");

    return rule1 == 0 && rule2 && rule3;
}

function check_groupname() {
    const name = document.getElementById('name_input').value;
    let rule1 = -1;
    $.ajax('check_username.php', {
        async: false,
        data: {'username': name},
        method: 'POST',
        success: function (data) {
            rule1 = data;
        }
    });
    const rule2 = (name.match(/^[a-z]/));
    const rule3 = !(name.match(/[^A-Za-z0-9_]/));

    showValidateError('groupname', 1, rule1 == 0, rule1 == 0 ? "Name not in use" : (rule1 == 1 ? "Name exists as user" : "Name already exists"));
    showValidateError('groupname', 2, rule2, "Name must begin with a lowercase letter");
    showValidateError('groupname', 3, rule3, "Name must be alphanumeric (letters, numbers, underscore)");

    return rule1 == 0 && rule2 && rule3;
}

function check_groupdescription() {
    console.log('check desc');
    const description = document.getElementById('description_input').value;
    const rule1 = (description.length > 0);

    showValidateError('groupdescription', 1, rule1, "Description must not be blank");

    return rule1;
}

function random_password(length) {
    const randomChars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&';
    let password = '';
    let array = new Uint8Array(length);
    window.crypto.getRandomValues(array);
    for (let i = 0; i < length; i++) {
        password += randomChars.charAt(array[i] % randomChars.length);
    }
    return password;
}

function generate_password() {
    do {
        const password = random_password(8);
        $('#password-text').html(password);
        $('#password_input').val(password);
        $('#confirmPassword_input').val(password);
    } while (!check_passwords());
}

function isInt(value) {
    return !isNaN(value) &&
        parseInt(Number(value)) === value &&
        !isNaN(parseInt(value, 10));
}

$.fn.select2.defaults.set("width", null);

$(document).ready(function () {
    $('.copy-button').click(copy_panel);
});
