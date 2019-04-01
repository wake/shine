<?php

  use \Rollbar\Rollbar;
  use \Rollbar\Payload\Level;


  /**
   *
   * Rollbar config enable
   *
   */
  if (! env ('ROLLBAR_ENABLE', false) || env ('ROLLBAR_TOKEN', '') == '')
    return;


  Rollbar::init ([
    'access_token' => $_ENV['ROLLBAR_TOKEN'],
    'environment' => $_ENV['APP_ENV'],
  ]);
