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

// Создание экземпляра контроллера
$handler = new $controller_class_name();

// Установка классу свойств названий контроллера и действия
$handler->controller = $controller;
$handler->action = $action;

// Запуск метода предобработки запроса и метода before
$handler->before_handlers();
$handler->before();

// Индикатор вызова обработчика
$action_called = false;

// Если используются REST обработчики и присутствует метод
// $rest_methods - список REST методов, в /bootstrap.php
if(isset($rest_methods) && in_array($_SERVER['REQUEST_METHOD'], $rest_methods)) {
  // Проверка существования действия
  $action_name = strtolower($_SERVER['REQUEST_METHOD']) . '_action_' . $action;
  if(method_exists($handler, $action_name)) {
    // Запуск действия контроллера и изменение индикатора вызова
    call_user_func_array(array($handler, $action_name), $route['params']);
    $action_called = true;
  }
}

// Если не был вызван REST обработчик
if(!$action_called) {
  // Проверка существования действия
  $action_name = 'action_' . $action;
  if(!method_exists($handler, $action_name)) Controller::return_404();

  // Запуск действия контроллера
  call_user_func_array(array($handler, $action_name), $route['params']);
}

// Запуск метода постобработки запроса и метода after
$handler->after();
$handler->after_handlers();

// Вывод ответа контроллера
echo $handler->response;
