<?php


  /**
   *
   * Web index
   *
   */
  $router->get ('/', ['as' => 'index', function () use ($app) {

    return view ('index')
      ->with ('version', $app->version ());

  }]);
