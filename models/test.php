<?

/**
 * Пример модели
 */
class Model_Test {
  public static function get_data() {
    // Производит запрос к базе данных и возвращает результат
    return DB::get_all(
      "SELECT id, name, age
       FROM users
       WHERE age > ?i AND name LIKE ?l
       LIMIT ?i",
      30, 'Ivan', 10
    );
  }
}
