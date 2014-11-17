<?php

function htpasswd_check($clear, $hash, $config) {
  /* htpasswd supports the following hashing methods:
   * - MD5 (standard)
   * - blowfish
   * - crypt (DES)
   * - sha1
   * - plain
   *
   * All but the Apache-specific MD5 implementation
   * are available in PHP.
   */

  if (preg_match('/^\$apr1\$(.*?)\$.*$/', $hash, $match)) {
    $result = htpasswd_apr_md5($clear, $match[1]);
  }
  elseif (preg_match('/^\$2y\$.*$/', $hash, $match)) {
    $result = crypt($clear, $match[0]);
  }
  elseif (preg_match('/^\{SHA\}.*$/', $hash, $match)) {
    $result = '{SHA}' . base64_encode(sha1($clear, TRUE));
  }

  // The crypt and clear formats are not distinguishable.
  elseif (empty($config['plain'])) {
    $result = crypt($clear, $hash);
  }
  else {
   $result = $clear;
  }

  return hash_equals($result, $hash);
}

/**
 * Parts of this APR-MD5 implementation are derived from
 * an example at http://php.net/crypt
 */
function htpasswd_apr_md5($clear, $salt) {
  $len = strlen($clear);
  $text = $clear . '$apr1$' . $salt;
  $bin = pack('H32', md5($clear . $salt . $clear));
  for($i = $len; $i > 0; $i -= 16) {
    $text .= substr($bin, 0, min(16, $i));
  }
  for($i = $len; $i > 0; $i >>= 1) {
    $text .= ($i & 1) ? chr(0) : $clear{0};
  }
  $bin = pack('H32', md5($text));

  for($i = 0; $i < 1000; $i++) {
    $new = ($i & 1) ? $clear : $bin;
    if ($i % 3) $new .= $salt;
    if ($i % 7) $new .= $clear;
    $new .= ($i & 1) ? $bin : $clear;
    $bin = pack('H32', md5($new));
  }

  $tmp = '';
  for ($i = 0; $i < 5; $i++) {
    $k = $i + 6;
    $j = $i + 12;
    if ($j == 16) {
      $j = 5;
    }
    $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
  }

  $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
  $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
  './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
  return '$apr1$' . $salt . '$' . $tmp;
}
