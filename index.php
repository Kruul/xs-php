<?

/**
 * XS-PHP
 * Маленький и быстрый PHP MVC фреймворк
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */

/**
 * Автозагрузка классов контроллеров, моделей и плагинов
 */
spl_autoload_register(function($class_name) {
  // Проверка корректности имени класса
  if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class_name)) {
    throw new Exception('Класс "' . $class_name . '" не найден');
  }

  // Если в названии класса присутсвует символ "_",
  // то это может быть классом контроллера или модели
  if(strpos($class_name, '_') !== false) {
    // Разюиение название на части
    $class_name_parts = explode('_', strtolower($class_name));

    if($class_name_parts[0] == 'controller' || $class_name_parts[0] == 'model') {
      // Добавление "s" в конце так как директории называются controllers и models
      $class_name_parts[0] .= 's';

      $class_filepath = __DIR__ . '/' . implode('/', $class_name_parts) . '.php';
      if(!file_exists($class_filepath)) throw new Exception('Класс "' . $class_name . '" не найден');

      include_once($class_filepath);
      return;
    }
  }

  // Иначе - поиск класса плагина
  $lower_class_name = strtolower($class_name);

  // Проверка существования единичного файла плагина
  $class_filepath_single = __DIR__ . '/plugins/' . $lower_class_name . '.php';
  if(file_exists($class_filepath_single)) {
    include_once($class_filepath_single);
    return;
  }

  // Проверка существования плагина в директории
  $class_filepath_dir = __DIR__ . '/plugins/' . $lower_class_name . '/' . $lower_class_name . '.php';
  if(file_exists($class_filepath_dir)) {
    include_once($class_filepath_dir);
    return;
  }
});

// Подключение системных файлов
include(__DIR__ . '/system/config.php');
include(__DIR__ . '/system/controller.php');
include(__DIR__ . '/system/router.php');
include(__DIR__ . '/system/view.php');

// Обработка ошибок в зависимости от переменной окружения
set_error_handler(function($errno, $errstr, $errfile, $errline) {
  // Получение типа ошибки
  $errtype = '';
  if($errno == E_ERROR) $errtype = 'E_ERROR';
  if($errno == E_WARNING) $errtype = 'E_WARNING';
  if($errno == E_PARSE) $errtype = 'E_PARSE';
  if($errno == E_NOTICE) $errtype = 'E_NOTICE';
  if($errno == E_CORE_ERROR) $errtype = 'E_CORE_ERROR';
  if($errno == E_CORE_WARNING) $errtype = 'E_CORE_WARNING';
  if($errno == E_COMPILE_ERROR) $errtype = 'E_COMPILE_ERROR';
  if($errno == E_COMPILE_WARNING) $errtype = 'E_COMPILE_WARNING';
  if($errno == E_USER_ERROR) $errtype = 'E_USER_ERROR';
  if($errno == E_USER_WARNING) $errtype = 'E_USER_WARNING';
  if($errno == E_USER_NOTICE) $errtype = 'E_USER_NOTICE';
  if($errno == E_STRICT) $errtype = 'E_STRICT';
  if($errno == E_RECOVERABLE_ERROR) $errtype = 'E_RECOVERABLE_ERROR';
  if($errno == E_DEPRECATED) $errtype = 'E_DEPRECATED';
  if($errno == E_USER_DEPRECATED) $errtype = 'E_USER_DEPRECATED';

  // Сохранение в файл лога
  $log_name = time() . '-' . uniqid() . '.txt';

  $log_content = '';
  $log_content .= date('d.m.Y в H:i:s') . "\n\n";
  $log_content .= "Uri: {$_SERVER['REQUEST_URI']}\n\n";
  if(count($_COOKIE) != 0) {
    $cookie_data = json_encode($_COOKIE, JSON_PRETTY_PRINT);
    $log_content .= "Cookie: {$cookie_data}\n\n";
  }
  if(isset($_SESSION) && count($_SESSION) != 0) {
    $session_data = json_encode($_SESSION, JSON_PRETTY_PRINT);
    $log_content .= "Session: {$session_data}\n\n";
  }
  if(count($_POST) != 0) {
    $post_data = json_encode($_POST, JSON_PRETTY_PRINT);
    $log_content .= "Post: {$post_data}\n\n";
  }
  if(count($_FILES) != 0) {
    $files_data = json_encode($_FILES, JSON_PRETTY_PRINT);
    $log_content .= "Files: {$files_data}\n\n";
  }
  $log_content .= "В строке $errline файла $errfile\n\n";
  $log_content .= $errstr;

  file_put_contents(__DIR__ . '/logs/' . $log_name, $log_content);

  // Если разработка, вывод лога на странице
  if(ENV != 'prod') {
    echo '<pre>';
    echo "ОШИБКА $errtype\n";
    echo "\tВ строке $errline файла $errfile\n";
    echo "\t" . str_replace("\n", "\n\t", $errstr);
    echo '</pre>';
  }

  // Если ошибка критическая, завершение работы
  if($errno == E_ERROR ||
     $errno == E_PARSE ||
     $errno == E_CORE_ERROR ||
     $errno == E_COMPILE_ERROR ||
     $errno == E_USER_ERROR ||
     $errno == E_RECOVERABLE_ERROR) {
    // Если продакшен, вывод 500 страницы
    if(ENV == 'prod') Controller::return_500();
    exit();
  }

  return true;
});
error_reporting(E_ALL);

// Подключения загрузочного файла приложения
include(__DIR__ . '/bootstrap.php');

// Запуск роутера
$route = Router::exec();
if($route === null) Controller::return_404();
$controller = $route['controller'];
$action = $route['action'];

// Формирование названия контроллера и действия
$controller_parts = explode('_', $controller);
foreach($controller_parts as &$part) $part = ucfirst($part);
$controller_class_name = 'Controller_' . implode('_', $controller_parts);

// Проверка существования контроллера
try {
  if(!class_exists($controller_class_name)) Controller::return_404();
}
catch(Exception $e) {
  Controller::return_404();
}

// Парсинг JSON входящих данных и сохранение в $_POST
$content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER["CONTENT_TYPE"] : '';
if(stripos($content_type, 'application/json') !== false) {
  try {
    $_POST = json_decode(file_get_contents('php://input'), true);
  }
  catch(Exception $e) {}
}

// Создание экземпляра контроллера
$handler = new $controller_class_name();

// Установка классу свойств названий контроллера и действия
$handler->controller = $controller;
$handler->action = $action;

// Запуск метода предобработки запроса и метода before
$handler->before_handlers();
$handler->before();

// Попытка запуска действия контроллера с REST перфиксом
$rest_action_name = strtolower($_SERVER['REQUEST_METHOD']) . '_action_' . $action;
if(method_exists($handler, $rest_action_name)) {
  call_user_func_array(array($handler, $action_name), $route['params']);
}
// Попытка запуска действия без REST префикса
else {
  // Проверка существования действия
  $action_name = 'action_' . $action;
  if(!method_exists($handler, $action_name)) Controller::return_404();

  // Запуск действия контроллера
  call_user_func_array(array($handler, $action_name), $route['params']);
}

// Запуск метода постобработки запроса и метода after
$handler->after();
$handler->after_handlers();

// Вывод ответа контроллера в зависимости от типа
if(gettype($handler->response) == 'array') echo json_encode($handler->response, JSON_UNESCAPED_UNICODE);
else echo $handler->response;
