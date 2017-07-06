<?php

namespace Ermarian\EjabberdAuth\Bridge;

use Ermarian\EjabberdAuth\BridgeInterface;

abstract class DatabaseBridge implements BridgeInterface {
  /**
   * @var \PDO
   */
  protected $connection;

  /**
   * @var \PDOStatement
   */
  protected $userQuery;

  /**
   * @var \PDOStatement
   */
  protected $passwordQuery;

  const USERNAME = ':username';

  /**
   * PDOBridge constructor.
   *
   * @param \PDO $connection
   */
  public function __construct(\PDO $connection) {
    $this->connection = $connection;
    $this->userQuery = $this->connection->prepare($this->getUserQuery());
    $this->passwordQuery = $this->connection->prepare($this->getPasswordQuery());
  }

  /**
   * {@inheritdoc}
   */
  public function isuser($username, $server) {
    return $this->userQuery->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function auth($username, $server, $password) {
    $this->passwordQuery->execute([static::USERNAME => $username]);
    $result = $this->passwordQuery->fetch(\PDO::FETCH_ASSOC);
    return $this->checkPassword($username, $password, $result);
  }

  /**
   * Construct the PDO statement.
   *
   * @return string
   */
  abstract protected function getUserQuery();

  /**
   * Construct the PDO statement.
   *
   * @return string
   */
  abstract protected function getPasswordQuery();

  /**
   * Check a username's password against a result from the database.
   *
   * @param string $username
   * @param string $password
   * @param array $result
   *
   * @return bool
   */
  abstract protected function checkPassword($username, $password, array $result);
}
