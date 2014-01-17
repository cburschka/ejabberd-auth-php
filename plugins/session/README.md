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

This plugin uses a database table, described in the packaged install.sql file.
Install it with this command:

    cat ./install.sql | replace '{TAB}' '&lt;tablename&gt;' | \
    mysql -h &lt;host&gt; -D &lt;db&gt; -u &lt;user&gt; -p&lt;password&gt;

Next, you need to configure the database connection both in the main configuration
file and in the local `./config.php` of this plugin.

Finally, link the `www/rpc.php` file inside your website root somewhere inside
your forum's cookie domain and path (most forums set the path to `/`, so the
domain should be sufficient).

Usage
-----

Whenever you need to authenticate to ejabberd, make a POST request to the URL
that points at `www/rpc.php` with `salt` set to a reasonably random 16 character
value.

If the client making the POST request has a valid session for the site you're
authenticating with, then you will receive a JSON-encoded response as follows:

    `{"user":"&lt;user&gt;","secret":"&lt;secret&gt;","time":"&lt;time&gt;"}

From the point in `&lt;time&gt;` to however long you configured the timeout
(60 seconds are recommended), `&lt;secret&gt;` will be accepted as a password
by ejabberd for `&lt;user&gt;` on any domains you set up to use the session
plugin.

Security Considerations
-----------------------

The one-time key is transmitted in clear, and can be intercepted if the connection
is not encrypted. But the same channel is already used to transmit the password
when logging in, as well as the session cookie.
