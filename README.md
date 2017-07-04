ejabberd-auth-php
=================

This is a collection of bridges allowing an ejabberd XMPP server to use a locally
installed PHP-based CMS for external authentication.

Features
--------

Currently implemented bridges:

* Drupal 8
* Apache htpasswd

Installation
------------

Copy the file `config.sample.yml` to `config.yml` and fill in the appropriate
values.

Open your ejabberd configuration and set the external authentication script:

### ejabberd < 13.10 ###

The configuration file should be located at `/etc/ejabberd/ejabberd.cfg`. Find, uncomment
and edit the following lines.

    {auth_method, external}.
    {extauth_program, ".../ejabberd-auth-php/main"}.

### ejabberd 13.10+ ###

The configuration file is at `/etc/ejabberd/ejabberd.yml`.

    auth_method: external
    extauth_program: ".../ejabberd-auth-php/main"

License
-------

The core project, without plugins, may be distributed or modified
under the  under the terms of the MIT license.

The `drupal` plugin contains a module that interfaces with the
[Drupal](https://drupal.org/) project and is licensed under the
GNU General Public License, version 2 or later.

Support
-------

I will not be able to offer support or reliable maintenance for this software,
or any of its plugins. Functionality may be changed without notice. This software
is (for now) indefinitely in pre-release mode, and there are no current plans
for a stable release.

Your best bet for using this software is to fork it and maintain your own
codebase. I will gladly take pull requests under consideration if you feel like
contributing.
