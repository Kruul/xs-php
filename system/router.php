<?

/**
 * Роутер
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class Router {
  /**
   * Компилирование паттернов роутов для корректной работы регулярных выражений
   *
   * @param   array  $routes  Массив роутов
   * @return  array           Массив скомпилированных роутов
   */
  public static function compile_routes($routes) {
    $compiled_routes = [];

    foreach($routes as $pattern => $route) {
      $pattern = str_replace('/', '\/', $pattern);
      $pattern = str_replace('\\\\', '\\', $pattern);

      $compiled_routes[$pattern] = $route;
    }

    return $compiled_routes;
  }

  /**
   * Обрабатывает URI адрес запроса для получения контроллера, действия и параметров
   *
   * @return  array                    Массив с контроллером, действием и параметрами
   */
  public static function exec() {
    $routes_config = Config::get('router');
    if($routes_config === null) $routes_config = [ '(.*)' => '$1' ];

    $routes = self::compile_routes($routes_config);

    $sub_uri = $_SERVER['REQUEST_URI'];

    // Удаляем базовый путь из строки запроса
    if(strlen(BASE_PATH) > 0 && strpos($_SERVER['REQUEST_URI'], BASE_PATH) === 0) {
      $sub_uri = substr($sub_uri, strlen(BASE_PATH));
    }

    $parsed_url = parse_url($sub_uri);
    if(!isset($parsed_url['path'])) return null;

    $uri = urldecode($parsed_url['path']);
    $found_uri = null;

    // Поиск соответсвия в настройках роутов
    foreach($routes as $pattern => $route) {
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
