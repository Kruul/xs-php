<?

/**
 * Роутер
 * Настройки роутов приложения хранятся в переменной $routes в файле /bootstrap.php
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class Router {
  /**
   * Хранение скомпилированных роутов
   * @var  array
   */
  private static $routes = [];

  /**
   * Заменяет паттерны в роутах и компилирование резулярных выражений
   * Устанавливает результат в приватное свойство $routes
   *
   * @param   array  $routes    Массив настроек роутов
   * @param   array  $patterns  Паттерны для замены в роутах
   */
  public static function compile_routes($routes, $patterns) {
    foreach($routes as $pattern => $route) {
      $compiled_pattern = $pattern;

      foreach($patterns as $short => $regexp) {
        $compiled_pattern = str_replace(':' . $short, $regexp, $compiled_pattern);
      }
      $compiled_pattern = str_replace('/', '\/', $compiled_pattern);
      $compiled_pattern = str_replace('\\\\', '\\', $compiled_pattern);

      unset($routes[$pattern]);
      $routes[$compiled_pattern] = $route;
    }

    self::$routes = $routes;
  }

  /**
   * Обрабатывает URI адрес запроса для получения контроллера, действия и параметров
   *
   * @return  array                    Массив с контроллером, действием и параметрами
   */
  public static function exec() {
    $router_config = Config::get('router');
    if($router_config === null) {
      $router_config = [
        'routes' => [
          '(.*)' => '$1'
        ],
        'patterns' => [ ]
      ];
    }

    $routes = $router_config['routes'];
    $patterns = $router_config['patterns'];

    if(count(self::$routes) == 0) self::compile_routes($routes, $patterns);

    $sub_uri = $_SERVER['REQUEST_URI'];

    // Удаляем базовый путь из строки запроса
    if(strlen(BASE_PATH) > 0 && strpos($_SERVER['REQUEST_URI'], BASE_PATH) === 0) {
      $sub_uri = substr($sub_uri, strlen(BASE_PATH));
    }

    $parsed_url = parse_url($sub_uri);
    if(!isset($parsed_url['path'])) Controller::return_404();
    $uri = urldecode($parsed_url['path']);
    $found_uri = null;

    // Поиск соответсвия в настройках роутов
    foreach(self::$routes as $pattern => $route) {
      if(preg_match("/^" . $pattern . "$/u", $uri)) {
        // Замена роута на строку с названием контроллера и действия
        $found_uri = preg_replace("/^" . $pattern . "$/u", $route, $uri);
        break;
      }
    }

    // Если роут не был найден в списке, возвращаем null
    if($found_uri === null) return null;

    $uri_parts = explode('/', $found_uri);
    // Удаление первого пустого элемента, так как путь начинается с "/"
    array_shift($uri_parts);

    $controller = array_shift($uri_parts);
    if(!$controller) $controller = 'index';

    $action = array_shift($uri_parts);
    if(!$action) $action = 'index';

    $params = $uri_parts;

    return [
      'controller' => $controller,
      'action' => $action,
      'params' => $params
    ];
  }
}
