<?

/**
 * Контроллер базового шаблона вида
 */
class Controller_Template_Base extends Controller {
  /**
   * Название базового шаблона вида
   * @var  string
   */
  public $template_name = 'layout';

  /**
   * Автоматический рендеринг
   * Создано для возможности отключения в ajax действиях
   * @var  boolean
   */
  public $auto_renderer = true;

  /**
   * Свойство для установки контента базового шаблона
   * @var  string
   */
  public $content = '';

  /**
   * Код в методе after создает базовый шаблон и устанавливает контент шаблону
   */
  public function after() {
    if($this->auto_renderer) {
      View::set_global('controller', $this->controller);
			View::set_global('action', $this->action);

      $template = View::factory($this->template_name);
      $template->content = $this->content;

			$this->response = $template;
		}
  }
}
