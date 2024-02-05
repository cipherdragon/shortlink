document.getElementById('link-create-form').addEventListener('submit', on_create);
document.getElementById('change-password-form').addEventListener('submit', on_change_password);
document.getElementById('logout-btn').addEventListener('click', logout);
document.getElementById('change-password-btn').addEventListener('click', show_change_password_form);
document.getElementById('shortlink-dialog-btn').addEventListener('click', show_link_create_form);


let user = null;

function show_change_password_form() {
    const change_password_popup = document.getElementById('change-password-popup');
    change_password_popup.classList.remove('hidden');
}

function show_link_create_form() {
    const link_create_popup = document.getElementById('link-create-popup');
    link_create_popup.classList.remove('hidden');
}

function setup_popup_close_btn() {
    const popups = document.querySelectorAll('.popup-overlay');
    popups.forEach(popup => {
        const cancel_btn = popup.querySelector('.btn-cancel');
        const popup_form = popup.querySelector('form');
        cancel_btn.addEventListener('click', () => {
            popup_form.reset();
            popup.classList.add('hidden');
        });
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
    document.getElementById('change-password-popup').classList.add('hidden');
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

    close_link_create_form();
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
    const redirection_card = document.createElement('div');
    const redirection_card__short_link = document.createElement('div');
    const redirection_card__short_link__a = document.createElement('a');
    const redirection_card__short_link__baseurl = document.createElement('span');
    const redirection_card__short_link__slug = document.createElement('span');
    const redirection_card__destination = document.createElement('div');
    const redirection_card__destination__a = document.createElement('a');

    const redirection_card__controls = document.createElement('div');
    const edit_btn = document.createElement('button');
    const delete_btn = document.createElement('button');

    redirection_card.classList.add('redirection-card');
    redirection_card__short_link.classList.add('redirection-card__short-link');
    redirection_card__short_link__baseurl.classList.add('redirection-card__short-link__baseurl');
    redirection_card__destination.classList.add('redirection-card__destination');
    redirection_card__controls.classList.add('redirection-card__controls');

    redirection_card__short_link__baseurl.innerHTML = baseurl;
    redirection_card__short_link__slug.innerHTML = slug;
    redirection_card__short_link__a.appendChild(redirection_card__short_link__baseurl);
    redirection_card__short_link__a.appendChild(redirection_card__short_link__slug);

    redirection_card__destination__a.innerHTML = destination;

    redirection_card__short_link.appendChild(document.createTextNode('From: '));
    redirection_card__short_link.appendChild(redirection_card__short_link__a);
    redirection_card__destination.appendChild(document.createTextNode('To: '));
    redirection_card__destination.appendChild(redirection_card__destination__a);

    redirection_card__short_link__a.href = `${baseurl}${slug}`;
    redirection_card__destination__a.href = destination;

    edit_btn.innerHTML = 'Edit';
    delete_btn.innerHTML = 'Delete';
    redirection_card__controls.appendChild(edit_btn);
    redirection_card__controls.appendChild(delete_btn);

    delete_btn.addEventListener('click', () => delete_redirection(slug));

    redirection_card.appendChild(redirection_card__short_link);
    redirection_card.appendChild(redirection_card__destination);
    redirection_card.appendChild(redirection_card__controls);

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