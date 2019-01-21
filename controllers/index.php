<?

/**
 * Пример корневого контроллера
 */
class Controller_Index extends Controller_Base {
  /**
   * Пример корневого действия
   */
  public function action_index() {
    // Получение настроек из /config/main.php
    $main_config = Config::get('main');

    // Установка глобальной переменной вида title
    // Она используется как в базовом шаблоне вида,
    // так и в шаблоне вида контента
    View::set_global('title', $main_config['title']);

    // Создание экземпляра класса вида контента
    // Вторым параметром передается массив локальных переменных
    $content_view = View::factory('content', [
      'text1' => $main_config['description'],
      'text2' => '<a href="https://github.com/xooler/xs-php">xooler/xs-php</a>'
    ]);

    // Устанавливаем экземпляр класса вида в свойство content
    // для вставки его в шаблок (см. /controllers/template.php)
    $this->content = $content_view;
  }
}
