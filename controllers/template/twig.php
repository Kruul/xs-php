<?

/**
 * Контроллер базового шаблона вида, использующий плагин шаблониатора Twig
 */
class Controller_Template_Twig extends Controller {
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
      View_Twig::set_global('controller', $this->controller);
			View_Twig::set_global('action', $this->action);

      $template = View_Twig::factory($this->template_name);
      $template->content = $this->content;

			$this->response = $template;
		}
  }
}
