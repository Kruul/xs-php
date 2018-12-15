<?

/**
 * Роутер
 * Настройки роутов приложения хранятся в переменной $routes в файле /bootstrap.php
 *
 * @package  XS-PHP
 * @version  1.0.0
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
   * @param   array  $routes           Массив настроек роутов
   * @param   array  $routes_patterns  Паттерны для замены в роутах
   */
  public static function compile_routes($routes, $routes_patterns) {
    foreach($routes as $pattern => $route) {
      $compiled_pattern = $pattern;

      foreach($routes_patterns as $short => $regexp) {
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
   * @param   array  $routes           Массив настроек роутов
   * @param   array  $routes_patterns  Паттерны для замены в роутах
   * @return  array                    Массив с контроллером, действием и параметрами
   */
  public static function exec($routes, $routes_patterns) {
    if(count(self::$routes) == 0) self::compile_routes($routes, $routes_patterns);

    $sub_uri = $_SERVER['REQUEST_URI'];

    // Удаляем базовый путь из строки запроса
    if(strlen(BASE_PATH) > 0 && strpos($_SERVER['REQUEST_URI'], BASE_PATH) === 0) {
      $sub_uri = substr($sub_uri, strlen(BASE_PATH));
    }

    $uri = $uri_parts = urldecode(parse_url($sub_uri)['path']);

    // Поиск соответсвия в настройках роутов
    foreach(self::$routes as $pattern => $route) {
      if(preg_match("/^" . $pattern . "$/u", $uri)) {
        // Замена роута на строку с названием контроллера и действия
        $uri = preg_replace("/^" . $pattern . "$/u", $route, $uri);
        break;
      }
    }

    $uri_parts = explode('/', $uri);
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
