document.getElementById('link-create-form').addEventListener('submit', on_create);
document.getElementById('change-password-form').addEventListener('submit', on_change_password);
document.getElementById('logout-btn').addEventListener('click', logout);
document.getElementById('toggle-change-password').addEventListener('click', toggle_change_password);

let user = null;

function toggle_change_password() {
    const change_password_form = document.getElementById('change-password-form');

    if (change_password_form.style.display === 'none') {
        change_password_form.style.display = 'grid';
    } else {
        change_password_form.style.display = 'none';
    }
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
    toggle_change_password();
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

async function get_my_redirects() {
    const response = await fetch('/shortlink/api/redirection.php');
    const data = await response.json();

    const table_body = document.getElementById('link-table-body');
    table_body.innerHTML = '';

    data.forEach(redirection => {
        const delete_btn = document.createElement('button');
        delete_btn.classList.add('btn');
        delete_btn.classList.add('btn-primary');
        delete_btn.innerHTML = 'Delete';
        delete_btn.addEventListener('click', () => delete_redirection(redirection.slug));

        const delete_btn_td = document.createElement('td');
        delete_btn_td.appendChild(delete_btn);

        const tr = document.createElement('tr');
        const content = 
            `<td>${redirection.slug}</td>
            <td>${redirection.destination}</td>`;
        
        tr.innerHTML = content;
        tr.appendChild(delete_btn_td);

        table_body.appendChild(tr);
        console.log(redirection);
    });
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