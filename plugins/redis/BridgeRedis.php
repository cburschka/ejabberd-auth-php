<?php

define("SESSION","session:");
define("EMAIL_UID","email.uid:");

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeRedis extends EjabberdAuthBridge {
  function __construct($redis, $config) {
      $this->redis = $redis;
	  if (!empty($config['log_path']) && is_dir($config['log_path']) && is_writable($config['log_path']))
	        $this->logfile = fopen($config['log_path'] . 'activity-' . date('Y-m-d') . '.log', 'a');
	  else $this->logfile = STDERR;
  }
  
  function log($data) {
	  fwrite($this->logfile, sprintf("%s [%d] - %s\n", date('Y-m-d H:i:s'), getmypid(), $data));
  }

  function prune() {
   
  }

  function isuser($username, $server) {
	  $this->log('Isuer ...');
	  // check your redis user
      // $username = $username . "@" . $server;
      // $result = $this->redis->hExists(EMAIL_UID . $username, "uid");
      // $this->log("result:" . $result);
      // return $result;
	  return true;
  }

  function auth($username, $server, $password) {
	  $this->log('Auth ...');
	  // user and password authentication
      // $username = $username . "@" . $server;
      // $uid = $this->redis->hget(EMAIL_UID . $username, "uid");
      // $uid2 = $this->redis->hget(SESSION . $password, "uid");
      // $this->log('uid:'. $uid);
      // $this->log('uid2:'. $uid2);
      // if(!empty($uid) && $uid == $uid2){
      //     $this->redis->hset(SESSION,$password,"updated_at",time());
      //     return true;
      // }
      // return false;
	  return true;
  }
  
}
