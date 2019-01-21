<?

/**
 * Триминг строки
 */
Validator::add_sanitize('trim', function($value) {
  return @trim($value);
});

/**
 * Конвертация в целочисленный тип
 */
Validator::add_sanitize('intval', function($value) {
  return @intval($value);
});

Validator::add_sanitize('strval', function($value) {
  return @strval($value);
});

Validator::add_sanitize('currency', function($value) {
  return number_format(@floatval($value), 2, '.', '');
});

Validator::add_sanitize('tolower', function($value) {
  return mb_strtolower($value);
});

Validator::add_sanitize('floatval', function($value) {
  return @floatval($value);
});

Validator::add_sanitize('xss', function($value) {
  return htmlspecialchars(strip_tags($value));
});
