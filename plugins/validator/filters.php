<?

/**
 * Непустое поле
 */
Validator::add_filter('not_empty', function($value) {
  return $value != '';
});

/**
 * Нестрогое сравнение
 *
 * @param  mixed  $params  Сравниваемое значение
 */
Validator::add_filter('equal', function($value, $params) {
  return $value == $params;
});

/**
 * Строгое сравнение
 *
 * @param  mixed  $params  Сравниваемое значение
 */
Validator::add_filter('equal_strict', function($value, $params) {
  return $value === $params;
});

/**
 * Проверка содержание подстроки
 *
 * @param  string  $params  Искомая подстрока
 */
Validator::add_filter('contain', function($value, $params) {
  return mb_strpos($value, $params) !== false;
});

/**
 * Длинна строки равна
 *
 * @param  int  $params  Необходимая длинна строки
 */
Validator::add_filter('length_equal', function($value, $params) {
  return mb_strlen($value) == $params;
});

/**
 * Длинна строки больше
 *
 * @param  int  $params  Минимальная длинна строки
 */
Validator::add_filter('length_min', function($value, $params) {
  return mb_strlen($value) >= $params;
});

/**
 * Длинна строки меньше
 *
 * @param  int  $params  Максимальная длинна строки
 */
Validator::add_filter('length_max', function($value, $params) {
  return mb_strlen($value) <= $params;
});

/**
 * Длинна строки между
 *
 * @param  array  $params         Параметры
 * @param  array  $params['min']  Минимальная длинна строки
 * @param  array  $params['max']  Максимальная длинна строки
 */
Validator::add_filter('length_between', function($value, $params) {
  $length = mb_strlen($value);
  return $length >= $params['min'] && $length <= $params['max'];
});

/**
 * Параметр - число
 */
Validator::add_filter('is_numeric', function($value) {
  return @is_numeric($value);
});

/**
 * Числовой параметр больше
 *
 * @param  int|float  $params  Число для сравления
 */
Validator::add_filter('min', function($value, $params) {
  return $value >= $params;
});

/**
 * Числовой параметр меньше
 *
 * @param  int|float  $params  Число для сравления
 */
Validator::add_filter('max', function($value, $params) {
  return $value <= $params;
});

/**
 * Значение числового параметра в диапазоне
 *
 * @param  array      $params         Параметры
 * @param  int|float  $params['min']  Минимальное значение
 * @param  int|float  $params['max']  Максимальное значение
 */
Validator::add_filter('between', function($value, $params) {
  return $value >= $params['min'] && $value <= $params['max'];
});

/**
 * Нахождение значения в массиве
 *
 * @param  array  $params  Массив со значениями
 */
Validator::add_filter('in', function($value, $params) {
  return in_array($value, $params);
});

/**
 * Валидность Email адреса
 */
Validator::add_filter('email', function($value) {
  return preg_match('/^.+\@\S+\.\S+$/', $value);
});

/**
 * Строка - только из латинских символов в нижнем регистре
 */
Validator::add_filter('alpha', function($value) {
  return preg_match('/^([a-z])+$/i', $value);
});

/**
 * Строка - только из латинских символов в нижнем регистре и чисел
 */
Validator::add_filter('alphanum', function($value) {
  return preg_match('/^([a-z0-9])+$/i', $value);
});

/**
 * Строка - только из латинских символов в нижнем регистре,
 * чисел, тире и знака нижнего подчеркивания
 */
Validator::add_filter('alphadash', function($value) {
  return preg_match('/^([-a-z0-9_-])+$/i', $value);
});

/**
 * Прохождение регулярного выражения
 *
 * @param  string  $params  Регулярное выражение
 */
Validator::add_filter('regex', function($value, $params) {
  return preg_match($params, $value);
});

/**
 * Валидность даты по формату
 *
 * @param  string  $params  Формат даты
 */
Validator::add_filter('date_format', function($value, $params) {
  $parsed = date_parse_from_format($params, $value);
  return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
});

/**
 * Прохождение проверки в функции
 *
 * @param  callable  $params  Функция проверки значения
 */
Validator::add_filter('callback', function($value, $params) {
  return $params($value);
});
