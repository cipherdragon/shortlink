document.getElementById('login-form').addEventListener('submit', on_login);

async function on_login(event) {
    event.preventDefault();
    const form = event.target;
    const username = form.username.value;
    const password = form.password.value;

    if (username === '' || password === '') {
        alert('Username and password are required!');
        return;
    }

    const request_body = {
        username: username,
        password: password,
    };

    fetch('/shortlink/api/login.php', {
        method: 'POST',
        body: JSON.stringify(request_body),
    }).then(response => {
        if (response.status === 200) {
            window.location.href = '/rd/dashboard';
        } else {
            alert('Failed to login!');
        }
    });

}