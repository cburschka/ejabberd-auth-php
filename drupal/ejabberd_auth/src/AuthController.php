<?php

namespace Drupal\ejabberd_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\user\UserAuthInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends ControllerBase {
  /**
   * @var \Drupal\user\UserAuthInterface
   */
  protected $auth;

  /**
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * AuthController constructor.
   *
   * @param \Drupal\user\UserAuthInterface $auth
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param \Drupal\Core\Flood\FloodInterface $flood
   */
  public function __construct(UserAuthInterface $auth, EntityStorageInterface $storage, FloodInterface $flood) {
    $this->auth = $auth;
    $this->storage = $storage;
    $this->flood = $flood;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.auth'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('flood')
    );
  }

  /**
   * Process an incoming request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A POST re
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   {"result": true|false}
   */
  public function json(Request $request) {
    $response['result'] = FALSE;
    try {
      $username = $request->request->get('user');
      switch ($request->request->get('command')) {
        case 'isuser':
          $response['result'] = $this->isuser($username);
          break;
        case 'auth':
          $password = $request->request->get('password');
          $response['result'] = (bool) $this->authenticate($username, $password);
      }
    }
    catch (\Exception $exception) {
      if ($message = $exception->getMessage()) {
        $response['error'] = $message;
      }
    }
    return new JsonResponse($response);
  }

  /**
   * Check if a given username belongs to an active account.
   *
   * @param string $username
   *   A username.
   *
   * @return bool
   *   TRUE iff an account with that name exists and is not blocked.
   */
  protected function isuser($username) {
    /** @var \Drupal\user\UserInterface[] $users */
    $users = $this->storage->loadByProperties(['name' => $username]);
    return $users && reset($users)->isActive();
  }

  /**
   * Checks if a username and password are valid.
   *
   * This request is subject to flood control.
   *
   * @param string $username
   * @param string $password
   *
   * @return bool
   *   TRUE iff the correct password was entered.
   *
   * @throws \InvalidArgumentException
   *   If the login attempt is blocked by flood control.
   */
  protected function authenticate($username, $password) {
    $flood_config = $this->config('user.flood');

    // We cannot filter by IP, as the ejabberd server does not pass it on.
    // This does permit a Denial of Service attack, which we try to mitigate
    // by clearing the ejabberd flood control on a regular login.
    if (!$this->flood->isAllowed('ejabberd.failed_login_user',
      $flood_config->get('user_limit'),
      $flood_config->get('user_window'),
      $username)
    ) {
      throw new \InvalidArgumentException('Flood control was triggered.');
    }

    $result = (bool) $this->auth->authenticate($username, $password);
    if ($result) {
      $this->flood->clear('ejabberd.failed_login_user', $username);
    }
    else {
      $this->flood->register('ejabberd.failed_login_user',
        $flood_config->get('user_window'),
        $username);
    }
    return $result;
  }
}
