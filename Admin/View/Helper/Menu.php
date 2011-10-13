<?php

class Admin_View_Helper_Menu {

	public $JSON;
	public $open = array();

	/*
	 * Creates a menu from several different menu.json files collected by $this->gatherJSON()
	 *
	*/
	public function create($options=array('wrap'=>'<ul id="menu">%s</ul>')) {

		$controller	= str_replace('Admin_Controller_', '', Application_Base::getController());
		$action		= Application_Base::getAction();
		$url		= Application_Base::getBaseURL();

		$request	= new Modules_Request_HTTP();
		$requestURL	= $request->getRequestURL();
		$currentURL	= str_replace(Application_Base::getRelativePath(), '', $request->getURLPart($requestURL, 'path'));

		if(empty($this->JSON)) {
			$this->gatherJSON();
		}

		usort($this->JSON, 'Admin_View_Helper_Menu::sort');

		if(is_array($this->JSON)) {

			$menu = '';

			foreach($this->JSON AS $cnt => $main) {

			#	if((isset($this->app) && $this->app->getGlobal('access')->check('Admin_Controller_' . $main['controller'])) || !isset($this->app)) {

					$active	= $controller == $main['controller'] ? ' active ':'';
					$id		= $main['id'] ? ' id="'. $main['id'] . '"' : '';
					$class	= $main['class'] ? ' ' . $main['class'] . ' ' : '';
					$open	= $main['open'] == "true" || in_array($main['title'], $this->open) ? ' open ':'';

					$menu .= '<li ' . $id . ' class="' . $active . $class . $open . '"><a href="' . $url . $main['link'] . '">' . __($main['title']) . '</a>';
					if(isset($main['sub']) && !empty($main['sub'])) {
						$menu .= '<ul>';
						foreach($main['sub'] AS $sub) {

						#	if((isset($this->app) && $this->app->getGlobal('access')->check('Admin_Controller_' . $main['controller'] . '::' . strtolower(str_replace($controller . '/', '', $sub['link'])))) || !isset($this->app)) {
								$active	= (strtolower($controller . '/' . $action) == strtolower($sub['link']) || strpos(ltrim($currentURL, '/'), ltrim($sub['link']))  === 0) ? 'active':'';
								$id		= $sub['id'] ? ' id="'. $sub['id'] . '"' : '';
								$menu .= '<li ' . $id . ' class="' . $active . '"><a href="' . $url . $sub['link'] . '">' . __($sub['title']) . '</a></li>';
						#	}
						}
						$menu .= '</ul>';
					}
					$menu .= '</li>';

			#	}

			}

			if(isset($options) && isset($options['wrap'])) {
				$menu = sprintf($options['wrap'], $menu);
			}

		}
		return $menu;
	}

	/*
	 * Collects all menu.json files from $dir directory.
	 *
	*/
	public function gatherJSON($dir=NULL) {

		$dir = $dir !== NULL ? rtrim($dir, '/') : '';

		foreach(glob($dir . '/*/menu.json') AS $item) {

			if(is_file($item)) {

				$content = json_decode(file_get_contents($item), true);
				if($content != NULL) {
					$content['controller'] = $content['controller'] ? $content['controller'] : basename(dirname($item));
					$this->JSON[] = $content;
				}

			}

		}

		return $this;

	}

	public function addOpen($title) {
		
	}

	public function removeOpen($title) {
		
	}

	public static function sort($a, $b) {

		if ($a['order'] == $b['order']) {
			return 0;
		}

		return ($a['order'] < $b['order']) ? -1 : 1;

	}

}
