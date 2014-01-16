<?php

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeSMF2 extends EjabberdAuthBridge {
  function isuser($username, $server) {
    $query_where = 'member_name = {string:name}';
    $query_where_params = array(
      'name' => $username,
    );

    return 1 == count(smf_ssi('queryMembers', $query_where, $query_where_params, 1, 'id_member DESC', NULL));
  }

  function auth($username, $server, $password) {
    return smf_ssi('checkPassword', $username, $password, TRUE);
  }
}
