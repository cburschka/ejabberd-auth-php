<?php

class noweb_user {
  var $session_id = '';
  var $browser = 'N/A';
  var $forwarded_for = '127.0.0.1';
  var $ip = '127.0.0.1';

  function session_create() {
    // do absolutely nothing. however, unless we tell the auth module the session
    // was successfully created, it won't pass back a success.
    return TRUE;
  }
  
  function setup() {
  }
}
