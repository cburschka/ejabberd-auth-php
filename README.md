ejabberd-auth-php
=================

This is a collection of bridges allowing an ejabberd XMPP server to use a locally installed PHP-based CMS for external authentication.

Features
--------

Currently implemented bridges:

* phpBB 3.0
* phpBB 3.1 (unstable)
* Drupal 7.x
* Drupal 8.x (unstable)
* SMF 2.x

Potential candidates for further bridges are WordPress, MediaWiki, Joomla! and Moodle.

Extending
---------

In order to create a new plugin named {xyz}, you will need the following:

* A class extending EjabberdAuthBridge and implementing its methods.
* A file named {xyz}.module that contains the function {xyz}_init().

{xyz}_init() will receive its appropriate conf array in config.php and must
return an instance of the extended class.

The class methods must return boolean values indicating success or failure.
It is generally recommended NOT to allow account creation, account deletion or
password changes, and instead to simply return FALSE in these methods.

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
