Session Auth
============

The session plugin is special in that it does not connect to a specific CMS.
Rather, it allows password-less XMPP authentication based on an existing web
session. This is particularly useful to web-based clients.

The control flow is like this:

* A user authenticates using the normal website login system, and receives
  a session cookie.

* The user opens the associated web chat client.

* Instead of presenting a second login form, the client sends an HTTP request
  to a special public facing script in ejabberd-auth-php (./www/rpc.php).

* The script sees the session cookie, generates a one-time login valid for
  a short time (60 seconds by default), stores it and sends it to the client.

* The client uses this key as a password on ejabberd. Since ejabberd is configured
  to use ejabberd-auth-php, the main application receives the key and checks it.
  If it exists and hasn't expired yet, it confirms the authentication and the user
  is logged in without a password.

Installation
------------

First, configure the database connection in `config.php` by filling in the host,
database, user, password and table name.

Then, install the table by running `php plugins/session/install.php`.

Finally, link the `www/rpc.php` file in your website root somewhere within
your forum's cookie domain and path (most forums set the path to `/`, so the
domain should be sufficient).

Usage
-----

Note: Standard security policies prevent JavaScript from making cross-domain
requests, and particularly from transmitting cookies with such requests.
Therefore, your forum, the `rpc.php` script, *and* the web client must be hosted
on the same domain for this feature to work.

Whenever you need to authenticate to ejabberd, make a POST request to the URL
that points at `www/rpc.php` with `salt` set to a reasonably random 16 character
value.

If the client making the POST request transmits a valid session for the site you're
authenticating with, then you will receive a JSON-encoded response as follows:

    `{"user":"<user>","secret":"<secret>","time":"<time>"}

From the point in `<time>` to however long you configured the timeout
(60 seconds are recommended), `<secret>` will be accepted as a password
by ejabberd for `<user>` on any domains you set up to use the session
plugin.

Security Considerations
-----------------------

The one-time key is transmitted in clear, and can be intercepted if the connection
is not encrypted. But the same channel is already used to transmit the password
when logging in, as well as the session cookie.
