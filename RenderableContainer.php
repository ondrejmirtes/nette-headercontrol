<?php

use Nette\Application\Control;

/**
 * Class providing shortcut for rendering containing components.
 * E. g. $container->renderForm() => $container['form']->render();<br /><br />
 * 
 * Useful for {widget} template macro:
 * {widget container:form} => $container->renderForm();
 * 
 * @author Ondřej Mirtes
 * @copyright (c) Ondřej Mirtes 2009, 2010
 * @license MIT
 * @package HeaderControl
 */
class RenderableContainer extends Control
{

	public function __call($name, $args)
	{
		if (substr($name, 0, 6) == 'render') {
			$componentName = lcfirst(substr($name, 6));
			$component = $this->getComponent($componentName, FALSE);
			if ($component) {
				return call_user_func_array(array($component, 'render'), $args);
			}
		}
		
		return parent::__call($name, $args);
	}

}
