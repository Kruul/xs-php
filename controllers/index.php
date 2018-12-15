<?

class Controller_Index extends Controller_Template_Base {
  public function action_index() {
    // Установка глобальной переменной вида title
    // Она используется как в базовом шаблоне вида, так и в шаблоне вида контента
    View::set_global('title', 'XS-PHP');

    // Создание экземпляра класса вида контента
    $content_view = View::factory('content');

    // Устанавливаем локальные переменные вида контента
    $content_view->text1 = 'XS-PHP - это очень маленький (7 файлов без примеров и базовых плагинов) и быстрый PHP MVC фреймворк.';
    $content_view->set('text2', '<a href="https://github.com/xooler/xs-php">https://github.com/xooler/xs-php</a>');

    // Устанавливаем экземпляр класса вида контента
    // в свойство content родительского класса базового шаблона
    $this->content = $content_view;
  }
}
