<?php

namespace Ermarian\EjabberdAuth\Bridge;

use Ermarian\EjabberdAuth\BridgeInterface;

/**
 * Implements EjabberdAuthBridge.
 */
class SessionBridge implements BridgeInterface {

  /**
   * @var \PDOStatement
   */
  private $_insert;

  /**
   * @var \PDOStatement
   */
  private $_prune;

  /**
   * @var \PDOStatement
   */
  private $_auth;

  /**
   * @var \PDOStatement
   */
  private $_isuser;

  /**
   * @var int
   */
  private $timeout;

  /**
   * Session constructor.
   *
   * @param \PDO $pdo
   * @param string $table
   * @param int $timeout
   */
  public function __construct(\PDO $pdo, $table, $timeout) {
    assert(preg_match('/^\w+$/', $table));
    $this->install($pdo, $table);
    $this->_insert = $pdo->prepare("INSERT INTO `{$table}` (`username`, `secret`, `created`) VALUES (:username, :secret, :created);");
    $this->_isuser = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `username` = :user AND `created` >= :limit;");
    $this->_auth = $pdo->prepare("DELETE FROM `{$table}` WHERE `username` = :user AND `secret` = :secret AND `created` >= :limit;");
    $this->_prune = $pdo->prepare("DELETE FROM `{$table}` WHERE `created` < :limit;");
    $this->timeout = $timeout ?: 60;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    $mysql = $config['mysql'];
    $options = [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
    return new static(
      new \PDO($mysql['dsn'], $mysql['username'], $mysql['password'], $options),
      $mysql['tablename'],
      $config['timeout']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prune() {
    $this->_prune->execute([':limit' => time() - $this->timeout]);
  }

  /**
   * {@inheritdoc}
   */
  public function isuser($username, $server) {
    $this->prune();
    $this->_isuser->execute([
      ':user'  => $username,
      ':limit' => time() - $this->timeout,
    ]);
    return $this->_isuser->fetch()[0] > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function auth($username, $server, $password) {
    $this->prune();
    $this->_auth->execute([
      ':user'   => $username,
      ':secret' => $password,
      ':limit'  => time() - $this->timeout,
    ]);
    return $this->_auth->rowCount() > 0;
  }

  /**
   * Insert a new temporary secret.
   *
   * @param string $username
   * @param string $server
   *
   * @return string
   *   The secret hash.
   */
  public function insert($username, $server) {
    $secret = sha1("$username@$server:" . microtime() . random_bytes(16));
    return !$this->_insert->execute([
      ':user' => $username,
      ':secret' => $secret,
      ':created' => time(),
    ]) ?: $secret;
  }

  /**
   * Ensure the table exists.
   *
   * @param \PDO $database
   * @param string $table
   */
  private function install(\PDO $database, $table) {
    if ($database->exec("SELECT 1 FROM `{$table}`;") === FALSE) {
      $database->exec("CREATE TABLE `{$table}` (
      username VARCHAR(255),
	  secret VARCHAR(40),
	  created INT,
	  PRIMARY KEY(username, secret),
	  INDEX(created))");
    }
  }
}
