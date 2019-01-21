<?

/**
 * Непустое поле
 */
Validator::add_filter('not_empty', function($value) {
  return $value != '';
});

Validator::add_filter('equal', function($value, $params) {
  return $value == $params;
});

Validator::add_filter('equal_strict', function($value, $params) {
  return $value === $params;
});

/**
 * Поле содержит подстроку
 * @param  string  $params  Искомая подстрока
 */
Validator::add_filter('contain', function($value, $params) {
  return mb_strpos($value, $params) !== false;
});

Validator::add_filter('length_equal', function($value, $params) {
  return mb_strlen($value) == $params;
});

Validator::add_filter('length_min', function($value, $params) {
  return mb_strlen($value) >= $params;
});

Validator::add_filter('length_max', function($value, $params) {
  return mb_strlen($value) <= $params;
});

Validator::add_filter('length_between', function($value, $params) {
  $length = mb_strlen($value);
  return $length >= $params['min'] && $length <= $params['max'];
});

Validator::add_filter('accepted', function($value) {
  return in_array($value, [ 'yes', 'on', '1', 1, 'true', true ], true);
});

Validator::add_filter('is_numeric', function($value) {
  return is_numeric($value);
});

/**
 * Числовое значение поля больше чем параметр
 * @param  string  $params  Параметр для сравления
 */
Validator::add_filter('min', function($value, $params) {
  return $value >= $params;
});

Validator::add_filter('max', function($value, $params) {
  return $value <= $params;
});

Validator::add_filter('between', function($value, $params) {
  return $value >= $params['min'] && $value <= $params['max'];
});

Validator::add_filter('in', function($value, $params) {
  return in_array($value, $params);
});

Validator::add_filter('email', function($value, $params) {
  return preg_match('/^.+\@\S+\.\S+$/', $value);
});

Validator::add_filter('alpha', function($value, $params) {
  return preg_match('/^([a-z])+$/i', $value);
});

Validator::add_filter('alphanum', function($value, $params) {
  return preg_match('/^([a-z0-9])+$/i', $value);
});

Validator::add_filter('alphadash', function($value, $params) {
  return preg_match('/^([-a-z0-9_-])+$/i', $value);
});

Validator::add_filter('regex', function($value, $params) {
  return preg_match($params, $value);
});

Validator::add_filter('date_format', function($value, $params) {
  $parsed = date_parse_from_format($params, $value);
  return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
});

Validator::add_filter('callback', function($value, $params) {
  return $params($value);
});
