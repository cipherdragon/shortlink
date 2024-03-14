# ShortLink

A URL-shortening service which can integrate seemlessly with your existing PHP
webiste.

## Problem context

This software is made to solve a problem I faced. Writing this section because
you will understand what this software is about and why it was made.

I'm a webadmin maintaining a wordpress website for a non-profit. Wordpress has a
redirection plugin but not very friendly for non-technical users. I wrote this
software to allow our editors to create shortened URLs without them asking me to
do it. Because the website is wordpress, this software is written in a way to
integrate with PHP websites.

As the admin, create a account, i.e, username and password for your editor(s).
They can then login and create shortened URLs. 

## Development notice

This software is in development. It is not ready for production use. Does not
currently even have an admin panel to create accounts. If you dare, send
requests to the API using curl or some other tool.

Installation guide and a usage manual will be provided once the software is
ready for production use.

This README is written now rather than once production release is ready just
for the sake of completeness. 

## Installation instructions

1. Get the latest release from the releases page.
2. Extract the archive to your webserver's document root.
3. Rename the extracted folder to `shortlink`.
4. Create a mysql database. Shortlink expects your mysql server to use default 3306 port.
5. Edit the config options in `shortlink/Config.php` to your liking. Set DB credentials.

At this point, all shortlink related files should be in a sub directory called
`shortlink` in your webserver's document root. Next step is to route any request which
does not exist to a single PHP file. Let's call it `index.php` file for now.
The way to reroute requests to a single PHP file depends on the server software
you are using. For Apache, this can be done by adding a rewrite rule to
`.htaccess` file. Please check the server software's documentation for this.

WordPress installations already have the server configuration to route all requests to
non-existent files to `index.php`. 

Then in the `index.php` require the file `shortlink/shortlink.php`. Here's an example:

```php
<?php
// index.php
require_once __DIR__ . '/shortlink/shortlink.php';

echo("This is the dev index.php file.");
echo("In production environments, just add that require_once line to your index.php file.");
```

Then the software should be ready to use. Next step is to set up the database.
To do that, navigate to `http://yourwebsite.com/rd/install` in your web browser.
This will set up the required tables in the database. This will also create a
default admin account with username `admin` and password `admin_password_123`.
You should change this password immediately after logging in.

Now the installation is complete. To go to the dashbaord and to change password,
navigate to `http://yourwebsite.com/rd` in your web browser.

### Creating user accounts

Unfortunately, there is no admin panel to create user accounts as of now.
It will be implemented soon, but for now, you can create user accounts by
sending requests to the API. 

First send a POST request to the `http://yourwebsite.com/shortlink/api/login.php`
endpoint with the account credentials as JSON data:

```json
{
    "username": "admin",
    "password": "admin_password_123"
}
```

If credentials are correct, the server will respond with status code 200 and a
session cookie will be sent. Keep this cookie to send with all future requests.

Next, send a POST request to the `http://yourwebsite.com/shortlink/api/user.php`
The session cookie previously received should be sent with this request. The
request body should contain the new user's credentials as JSON data:

```json
{
    "username": "new_user",
    "password": "new_user_password_123",
    "role": "USER"
}
```

`role` can either be `USER` or `ADMIN`.

If the request is successful, the server will respond with status code 200 and a
json object containing the new user's id.

Other actions, such as creating shortened URLs, can be done using the UI accessible
on `http://yourwebsite.com/rd` and the way to perform these actions are self explanatory
once you are logged in.