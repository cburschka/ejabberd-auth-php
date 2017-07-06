<?php

namespace Ermarian\EjabberdAuth\Plugin;

use Ermarian\EjabberdAuth\Bridge\HttpBridge;

class DrupalHttpBridge extends HttpBridge {

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint() {
    return 'ejabberd/auth';
  }

}
