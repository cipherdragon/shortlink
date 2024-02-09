$('#link-create-form').submit(on_create);
$('#change-password-form').submit(on_change_password);
$('#logout-btn').click(logout);

let user = null;

function toggle_popup(popup_id, visibility) {
    const dialog = document.getElementById(popup_id);

    if (visibility === 'show') $(dialog).removeClass('hidden');
    else $(dialog).addClass('hidden');

    dialog.querySelector('form')?.reset();
}

function setup_popup_open_btn() {
    $('[data-toggle-dialog].ui-action-btn').each((_, el) => {
        $(el).click(() => toggle_popup($(el).data('toggleDialog'), 'show'));
    });
}

function setup_popup_close_btn() {
    $('.popup-overlay').each((_, el) => {
        $('.btn-cancel', el).click(() => toggle_popup($(el)[0].id, 'hide'));
    })
}

async function on_change_password(event) {
    event.preventDefault();
    const form = event.target;
    const new_password = form.password.value;

    if (user === null) {
        alert("Can't change password due to an error!");
    }

    const request_body = {
        username: user.username,
        password: new_password,
    };

    await fetch(`/shortlink/api/account.php`, {
        method: 'PUT',
        body: JSON.stringify(request_body),
    }).then(response => {
        if (response.status === 200) {
            alert('Password changed successfully!');
        } else {
            alert('Failed to change password!');
        }
    })

    form.reset();
    toggle_popup('change-password-popup', 'hide');
}

async function logout() {
    await fetch('/shortlink/api/logout.php');
    window.location.href = '/rd';
}

async function on_create(event) {
    event.preventDefault();
    const form = event.target;
    const slug = form.slug.value;
    const destination = form.destination.value;

    const request_body = {
        slug: slug,
        destination: destination,
    };

    await fetch(`/shortlink/api/redirection.php`, {
        method: 'POST',
        body: JSON.stringify(request_body),
    });

    form.reset();
    get_my_redirects();
    toggle_popup('link-create-popup', 'hide');
}

async function delete_redirection(slug) {
    const request_body = {
        slug: slug,
    };

    await fetch(`/shortlink/api/redirection.php`, {
        method: 'DELETE',
        body: JSON.stringify(request_body),
    });

    get_my_redirects();
}

function create_redirection_item(baseurl, slug, destination) {
    const card = $('[data-ui-template].redirection-card').clone().removeAttr('data-ui-template');

    card.find('.redirection-card__short-link__baseurl').text(baseurl);
    card.find('.redirection-card__short-link__slug').text(slug);
    card.find('.redirection-card__destination__url').text(destination);

    const edit_btn = card.find('.redirection-card__controls__edit-btn');
    const delete_btn = card.find('.redirection-card__controls__delete-btn');

    delete_btn.click(() => delete_redirection(slug));


    return card[0];
}

function refresh_redirection_list(redirections) {
    const redirection_list = $('.redirection-list-items').empty()[0];

    redirections.forEach(redirection => {
        redirection_list.appendChild(create_redirection_item("http://localhost:8080/", redirection.slug, redirection.destination));
    });
}

async function get_my_redirects() {
    const response = await fetch('/shortlink/api/redirection.php');
    const data = await response.json();

    refresh_redirection_list(data);
}

function load_user() {
    fetch('/shortlink/api/account.php')
        .then(response => response.json())
        .then(data => {
            if (data.username === null) return;

            user = data;
            $('#username').text(user.username);
        });
}

get_my_redirects();
load_user();
setup_popup_close_btn();
setup_popup_open_btn();