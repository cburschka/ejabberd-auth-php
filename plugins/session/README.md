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

Security Considerations
=======================

The one-time key is transmitted in clear, and can be intercepted if the connection
is not encrypted. But the same channel is already used to transmit the password
when logging in, as well as the session cookie.
