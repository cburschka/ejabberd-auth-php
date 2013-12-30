<?php

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeSMF2 extends EjabberdAuthBridge {
  function isuser($username, $server) {
    $query_where = 'member_name = {string:name}';

    $query_where_params = array(
      'name' => $name,
    );

    return 1 == count(ssi_queryMembers($query_where, $query_where_params, 1, 'id_member DESC', NULL));
  }

  function auth($username, $server, $password) {
    return ssi_checkPassword($username, $password, TRUE);
  }

  function setpass($username, $server, $password) {
    return FALSE;
  }

  function tryregister($username, $server, $password) {
    return FALSE;
  }

  function removeuser($username, $server) {
    return FALSE;
  }
}
