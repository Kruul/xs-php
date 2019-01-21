<?

/**
 * Пример базового контроллера для наследования
 */
class Controller_Base extends Controller_Template {
  /**
   * Методе before совершает базовые для всех наследующих его
   * контоллеров действия
   * Например, проверяет авторизацию пользователя,
   * получает информацию о пользователе и устанавливает ее
   * для последующего доступа
   */
  public function before() {
    // Проверка авторизации в выдуманной модели
    // В случае неудачи происходит редирект на страницу авторизации
    if(!Model_Test::check_auth()) Controller::redirect('/auth');

    // Получение данных и установка в свойство контроллера
    // и глобальную переменную вида
    $user = Model_Test::get_user_info($_COOKIE['id']);
    $this->user = $user;
    View::set_global('user', $user);
  }

  /**
   * Примеры методов обработчиков ошибок
   */
  public static function handler_404() {
    echo '<h1>404 error</h1>';
  }
  public static function handler_500() {
    echo '<h1>500 error</h1>';
  }
}
