<?php


  /**
   *
   * Shine Test Case
   *
   */
  abstract class TestCase extends Laravel\Lumen\Testing\TestCase {


    /**
     *
     * Creates the application
     *
     */
    public function createApplication () {
      return require __DIR__.'/../bootstrap/app.php';
    }
  }
