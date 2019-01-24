<?

/**
 * Пример модели
 */
class Model_Test {
  /**
   * Пример получения списка пользователей
   *
   * @param   int    $limit  Количество записей
   * @return  array          Массив пользователей
   */
  public static function get_users($limit) {
    return DB::get_all(
      "SELECT id, name, age
       FROM users
       LIMIT ?i",
      $limit
    );
  }

  /**
   * Заглушка проверки авторизации
   *
   * @return  bool  Результат проверки
   */
  public static function check_auth() {
    $_COOKIE['id'] = 1;

    return true;
  }

  /**
   * Заглушка получения информации о пользователе
   *
   * @param   int    $id  Идентификатор пользователя
   * @return  array       Массив с информацией о пользователе
   */
  public static function get_user_info($id) {
    return [];
  }
}
