<?php

namespace Ermarian\EAP\Plugin;

use Ermarian\EAP\Bridge\HttpBridge;

class DrupalHttpBridge extends HttpBridge {

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint() {
    return 'ejabberd/auth';
  }

}
