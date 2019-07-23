<?php


  /**
   *
   * System defines
   *
   */
  if (! defined ('_ROOT')) :


  /**
   *
   * Root
   *
   */
  define ('_ROOT', dirname (__DIR__));


  /**
   *
   * Resource (asset, component, core, external, view)
   *
   */
  define ('_RESOURCE', _ROOT . '/resource');


  /**
   *
   * Config
   *
   */
  define ('_CONFIG', _ROOT . '/config');


  /**
   *
   * Storage
   *
   */
  define ('_STORAGE', _ROOT . '/storage');


  // System defines end
  endif;


  /**
   *
   * Vendors
   *
   */
  require_once _ROOT . '/vendor/autoload.php';


  /**
   *
   * Environments
   *
   */
  try {
    Dotenv\Dotenv::create (_ROOT)->load ();
  }

  catch (Dotenv\Exception\InvalidPathException $e) {
    //
  }

  if (! isset ($_SERVER['REQUEST_SCHEME']))
    $_SERVER['REQUEST_SCHEME'] = isset ($_ENV['APP_HTTP_SCHEME']) ? $_ENV['APP_HTTP_SCHEME'] : 'http';

  if (! isset ($_SERVER['HTTP_HOST']))
    $_SERVER['HTTP_HOST'] = isset ($_ENV['APP_HTTP_HOST']) ? $_ENV['APP_HTTP_HOST'] : '';

  if (! isset ($_SERVER['QUERY_STRING']))
    $_SERVER['QUERY_STRING'] = '';

  if (! isset ($_SERVER['REQUEST_URI']))
    $_SERVER['REQUEST_URI'] = isset ($_ENV['APP_BASE_PATH']) ? $_ENV['APP_BASE_PATH'] : '';


  /**
   *
   * Values defines
   *
   */
  if (! defined ('_HOST')) :

  define ('_HOST', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);
  define ('_BASE', rtrim (dirname ($_SERVER['SCRIPT_NAME']), '/'));
  define ('_HTTP', _HOST . _BASE);
  define ('_URI',  str_replace ('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));

  define ('_TIMESTAMP',  time ());
  define ('_DATE',       date ('Y-m-d', _TIMESTAMP));
  define ('_TIME',       date ('H:i:s', _TIMESTAMP));
  define ('_DATETIME',   _DATE .' '. _TIME);
  define ('_SECRET',     $_ENV['APP_SECRET']);


  /**
   *
   * Dynamic environment
   *
   */
  define ('_UPLOAD', _ROOT . '/public' . $_ENV['APP_UPLOAD_PATH']);
  define ('_UPLOAD_BASE', ltrim ($_ENV['APP_UPLOAD_PATH'], '/'));


  // values defines end
  endif;


  /**
   *
   * Load models
   *
   */
  Shadow\Helper\Floader::autoload (_ROOT . '/database/models');


  /**
   *
   * Create the application
   *
   * Here we will load the environment and create the application instance
   * that serves as the central piece of this framework. We'll use this
   * application as an "IoC" container and router for this framework.
   *
   */
  $app = new Shadow\Application (_ROOT);


  /**
   *
   * Register container bindings
   *
   * Now we will register a few bindings in the service container. We will
   * register the exception handler and the console kernel. You may add
   * your own bindings here if you like or you can make another file.
   *
   */
  $app->singleton (
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
  );

  $app->singleton (
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
  );

  // http request object
  if (env ('API_ENABLE', false)) {
    $app->singleton ('client', function ($app) {
      return new GuzzleHttp\Client (['base_uri' => env ('API_BASE_URI', '')]);
    });
  }

  // SendGrid email service
  if (env ('SG_ENABLE', false)) {
    $app->singleton ('mailer', function ($app) {
      return new Shadow\Email\SendGrid (['key' => env ('SG_API_KEY', '')]);
    });
  }


  /**
   *
   * Register middleware
   *
   * Next, we will register the middleware with the application. These can
   * be global middleware that run before and after each request into a
   * route or middleware that'll be assigned to some specific routes.
   *
   */
  $app->middleware ([
    \Illuminate\Session\Middleware\StartSession::class,
  ]);

  $app->routeMiddleware ([
    'auth' => \App\Http\Middleware\Auth::class,
    'admin.auth' => \App\Http\Middleware\AdminAuth::class
  ]);

  /**
   *
   * Register service providers
   *
   * Here we will register all of the application's service providers which
   * are used to bind services into the container. Service providers are
   * totally optional, so you are not required to uncomment this line.
   *
   */

  // Must be registered before twig config file
  if (env ('APP_I18N_ENABLE', false)) {
    $app->register (Shadow\Provider\LocaleProvider::class);
  }

  $app->configure ('twigbridge');
  $app->register (\TwigBridge\ServiceProvider::class);

  $app->configure ('session');
  $app->register (\Illuminate\Session\SessionServiceProvider::class);

  $app->configure ('database');


  /**
   *
   * Bind to application
   *
   */
  $app->bind (\Illuminate\Session\SessionManager::class, function () use ($app) {
    return $app->make ('session');
  });


  /**
   *
   * Load the application routes
   *
   * Next we will include the routes file so that they can all be added to
   * the application. This will provide all of the URLs the application
   * can respond to, as well as the controllers that may handle them.
   *
   */
  $app->router->group ([

    'namespace' => 'App\Http\Controllers',

  ], function ($router) use ($app) {

    $testingEnv = array_get ($_ENV, 'APP_ENV', '') == 'testing';

    Shadow\Helper\Floader::once (! $testingEnv);  // require_once => true
    Shadow\Helper\Floader::inject (compact (["app", "router"]));
    Shadow\Helper\Floader::autoload (_ROOT . '/app');

  });


  /**
   *
   * Return
   *
   */
  return $app;
