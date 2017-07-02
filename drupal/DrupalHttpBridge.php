<?php

namespace Ermarian\EAP\drupal;

use Ermarian\EAP\Bridge\HttpBridge;

class DrupalHttpBridge extends HttpBridge {

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    // Append the /ejabberd/auth endpoint.
    $config['url'] = rtrim($config['url'], '/') . '/ejabberd/auth';
    return parent::create($config);
  }

}
