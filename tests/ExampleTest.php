<?php

  use Laravel\Lumen\Testing\DatabaseMigrations;
  use Laravel\Lumen\Testing\DatabaseTransactions;


  /**
   *
   * Shine example test
   *
   */
  class ExampleTest extends TestCase {


    /**
     *
     * Test example
     *
     */
    public function testExample () {

      $this->get('/');

      $this->assertEquals (
        $this->app->version(), $this->response->getContent()
      );
    }
  }
