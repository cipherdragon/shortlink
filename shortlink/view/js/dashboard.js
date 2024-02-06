document.getElementById('link-create-form').addEventListener('submit', on_create);
document.getElementById('change-password-form').addEventListener('submit', on_change_password);
document.getElementById('logout-btn').addEventListener('click', logout);

let user = null;

function toggle_popup(popup_id, visibility) {
    const dialog = document.getElementById(popup_id);

    if (visibility === 'show') dialog.classList.remove('hidden');
    else dialog.classList.add('hidden');

    const form = dialog.querySelector('form'); 
    form && form.reset();
}

function setup_popup_open_btn() {
    const action_btns = document.querySelectorAll('[data-toggle-dialog].ui-action-btn');
    action_btns.forEach(popup_launch_btn => {
        const popup_id = popup_launch_btn.dataset.toggleDialog;
        popup_launch_btn.addEventListener('click', () => toggle_popup(popup_id, 'show'));
    });
}

function setup_popup_close_btn() {
    const popups = document.querySelectorAll('.popup-overlay');
    popups.forEach(popup => {
        const cancel_btn = popup.querySelector('.btn-cancel');
        cancel_btn && cancel_btn.addEventListener('click', () => toggle_popup(popup.id, 'hide'));
    });
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
    const redirection_card_template = document.querySelector('[data-ui-template].redirection-card');
    const redirection_card = redirection_card_template.cloneNode(true);
    redirection_card.removeAttribute('data-ui-template');

    const redirection_card__short_link__baseurl = redirection_card.querySelector('.redirection-card__short-link__baseurl');
    const redirection_card__short_link__slug = redirection_card.querySelector('.redirection-card__short-link__slug');
    const redirection_card__destination__url = redirection_card.querySelector('.redirection-card__destination__url');

    const edit_btn = redirection_card.querySelector('.redirection-card__controls__edit-btn');
    const delete_btn = redirection_card.querySelector('.redirection-card__controls__delete-btn');

    redirection_card__short_link__baseurl.innerHTML = baseurl;
    redirection_card__short_link__slug.innerHTML = slug;
    redirection_card__destination__url.innerHTML = destination;

    delete_btn.addEventListener('click', () => delete_redirection(slug));

    return redirection_card;
}

function refresh_redirection_list(redirections) {
    const redirection_list = document.getElementsByClassName('redirection-list-items')[0];
    redirection_list.innerHTML = '';

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
            document.getElementById('username').innerHTML = user.username;
        });
}

get_my_redirects();
load_user();
setup_popup_close_btn();
setup_popup_open_btn();