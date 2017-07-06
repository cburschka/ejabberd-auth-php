<?php

namespace Ermarian\EjabberdAuth\Plugin;

use Ermarian\EjabberdAuth\Bridge\HttpBridge;

class DrupalHttpBridge extends HttpBridge {

  /**
   * {@inheritdoc}
   */
  protected static $endpoint = 'ejabberd-auth';

}
