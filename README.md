ejabberd-auth-php
=================

This is a collection of bridges allowing an ejabberd XMPP server to use a locally
installed PHP-based CMS for external authentication.

Features
--------

Currently implemented bridges:

* phpBB 3.0
* phpBB 3.1 (unstable)
* Drupal 7.x
* Drupal 8.x (unstable)
* SMF 2.x
* Apache htpasswd

Potential candidates for further bridges are WordPress, MediaWiki, Joomla! and Moodle.

Installation
------------

Copy the file `config.sample.php` to `config.php` and fill in the appropriate
values.

If you want to add session authentication, also read [plugins/session/README.md](
plugins/session/README.md). Otherwise, remove the relevant section of `config.php`.

Open your ejabberd configuration and set the external authentication script:

### ejabberd < 13.10 ###

The configuration file should be located at `/etc/ejabberd/ejabberd.cfg`. Find, uncomment
and edit the following lines.

    {auth_method, external}.
    {extauth_program, ".../ejabberd-auth-php/main.php"}.

### ejabberd 13.10+ ###

The configuration file is at `/etc/ejabberd/ejabberd.yml`.

    auth_method: external
    extauth_program: ".../ejabberd-auth-php/main.php"
    
Extending
---------

In order to create a new plugin named `{xyz}`, you will need the following:

* A class extending `EjabberdAuthBridge` and implementing its methods.
* A file named `{xyz}.module` that contains the function `{xyz}_init()`.

`{xyz}_init()` will receive its appropriate conf array in config.php and must
return an instance of the extended class.

The class methods must return boolean values indicating success or failure.
It is generally recommended NOT to allow account creation, account deletion or
password changes, and instead to simply return FALSE in these methods.

If you wish to use the `session` plugin with your bridge, you will also need to
implement a function named `{xyz}_session()`. This function takes no arguments.
It is called in a non-CLI context, and should return the username of the
currently logged-in user who made the web request, or `FALSE` if no user
is logged in.

License
-------

The core project, without plugins, may be distributed or modified under the 
under the terms of the GNU General Public License, version 2 or later.

The following plugins duplicate the code of other software, and are licensed
separately:

* phpBB, all versions: GNU General Public License, version 2

The remaining plugins contain no duplicated code, and are covered by the same 
license as the core project.

GPL v2: http://www.gnu.org/licenses/gpl-2.0.txt
GPL v3: http://www.gnu.org/licenses/gpl-3.0.txt

Support
-------

I will not be able to offer support or reliable maintenance for this software,
or any of its plugins. Functionality may be changed without notice. This software
is (for now) indefinitely in pre-release mode, and there are no current plans
for a stable release.

Your best bet for using this software is to fork it and maintain your own
codebase. I will gladly take pull requests under consideration if you feel like
contributing.
