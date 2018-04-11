<?

/**
 * Базовый контроллер
 * Файлы контроллеров хранятся в директории /app/controllers/
 *
 * @package  XS-PHP
 * @version  1.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class Controller {
  /**
   * Редирект с учетом базового пути
   *
   * @param  string  $uri  Uri адрес для редиректа
   *
   * @example  Controller::redirect('/auth/login');  Редирект на страницу /auth/login
   *                                                 с учетом базового пути.
   *                                                 Если базовый путь '/blog', то редирект
   *                                                 будет на /blog/auth/login
   */
  public static function redirect($url) {
    if(substr($url, 0, 7) !== 'http://' && substr($url, 0, 8) !== 'https://') {
      $url = BASE_PATH . $url;
    }

    header('Location: ' . $url);
    exit();
  }

  /**
   * Массив с функциями для запуска до методов контроллера
   * @var  array
   */
  private static $before_handlers = [];

  /**
   * Массив с функциями для запуска после методов контроллера
   * @var  array
   */
  private static $after_handlers = [];

  /**
   * Добавление функции обработки запроса до методов контроллера
   *
   * @param  callable  $function  Функция обработки запроса, должна принимать
   *                              один параметр - экземпляр текущего контроллера
   */
  public static function add_before_handler($function) {
    self::$before_handlers[] = $function;
  }

  /**
   * Добавление функции обработки запроса после методов контроллера
   *
   * @param  callable  $function  Функция обработки запроса, должна принимать
   *                              один параметр - экземпляр текущего контроллера
   */
  public static function add_after_handler($function) {
    self::$after_handlers[] = $function;
  }

  /**
   * Функция обработчик 404 ошибки
   * @var  callable
   */
  private static $error_404_handler;

  /**
   * Установка обработчика 404 ошибки
   *
   * @param  callable  $handler  Функция, вызываемая в случае 404 ошибки
   */
  public static function set_error_404_handler($handler) {
    self::$error_404_handler = $handler;
  }

  /**
   * Возврат контента страницы с 404 ошибкой
   */
  public static function return_404() {
    header('HTTP/1.1 404 Not Found');

    if(self::$error_404_handler) {
      call_user_func(self::$error_404_handler);
      exit();
    }

    echo 'Not found';
    exit();
  }

  /**
   * Название текущего контроллера
   * @var  string
   */
  public $controller;

  /**
   * Название текущего действия без 'action_' префикса
   * @var  string
   */
  public $action;

  /**
   * Ответ контроллера
   * @var  string
   */
  public $response = '';

  /**
   * Запускает функции вызываемые до методов контроллера
   */
  public function before_handlers() {
    foreach(self::$before_handlers as $handler) {
      // Передаем в функцию экземплят текущего контроллера
      $handler($this);
    }
  }

  /**
   * Запускает функции вызываемые после методов контроллера
   */
  public function after_handlers() {
    foreach(self::$after_handlers as $handler) {
      // Передаем в функцию экземплят текущего контроллера
      $handler($this);
    }
  }

  /**
   * Функция-заглушка для предотвращения ошибки в случает отсутствия метода before в контроллере
   */
  public function before() {}

  /**
   * Функция-заглушка для предотвращения ошибки в случает отсутствия метода after в контроллере
   */
  public function after() {}
}
