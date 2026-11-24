<?php
/**
 * MenuBuilder Helper
 *
 * This helper will build Dynamic Menu
 *
 * @author M. M. Rifat-Un-Nabi <to.rifat@gmail.com>
 * @package MenuBuilder
 * @subpackage MenuBuilder.views.helpers
 */
App::uses('AppHelper', 'View/Helper');
class MenuBuilderHelper extends AppHelper {
/**
 * Helper dependencies
 *
 * @var array
 * @access public
 */
	public $helpers = array('Html');

/**
 * Array of global menu
 *
 * @var array
 * @access protected
 */
	protected $_menu = array();

/**
 * Current user group
 *
 * @var String
 * @access protected
 */
	protected $_group = null;

/**
 * Current depth of menu
 *
 * @var Integer
 * @access protected
 */
	protected $_depth = 0;

/**
 * defaults property
 *
 * @var array
 * @access public
 */
	public $defaults = array(
		'separator' => false,
		'children' => null,
		'title' => null,
		'url' => null,
		'ulId' => null,
		'alias' => array(),
		'partialMatch' => false,
		'permissions' => array(),
		'id' => null,
		'class' => null,
		'selName'=>null,
	);

/**
 * settings property
 *
 * @var array
 * @access public
 */
	public $settings = array(
		'activeClass' => 'active',
		'firstClass' => '',
		'childrenClass' => 'has-children',
		'childrenDropdown' => '',
		'menuClass' => 'navigation',
		'evenOdd' => false,
		'itemFormat' => '<li%s>%s%s</li>',
		'wrapperFormat' => '<ul%s>%s</ul>',
		'noLinkFormat' => '<a href="#">%s</a>',
		'menuVar' => 'menu',
		'authVar' => 'user',
		'authModel' => 'User',
		'authField' => 'group',
	);

/**
 * Constructor.
 *
 * @access public
 */
	public function __construct(View $View, $settings = array()) {
		if (isset($settings['defaults'])) {
			$this->defaults = array_merge($this->defaults, $settings['defaults']);
			unset($settings['defaults']);
		}

		$this->settings = array_merge($this->settings, (array) $settings);

		if (!isset($View->viewVars[$this->settings['menuVar']])) {
			return;
		}

		$this->_menu = $View->viewVars[$this->settings['menuVar']];

		if (isset($View->viewVars[$this->settings['authVar']]) &&
			isset($View->viewVars[$this->settings['authVar']][$this->settings['authModel']]) &&
			isset($View->viewVars[$this->settings['authVar']][$this->settings['authModel']][$this->settings['authField']])) {
			 	$this->_group = $View->viewVars[$this->settings['authVar']][$this->settings['authModel']][$this->settings['authField']];
		}

		parent::__construct($View, (array)$settings);
	}

/**
 * Returns the whole menu HTML.
 *
 * @param string optional Array key.
 * @param array optional Aditional Options.
 * @param array optional Data which has the key.
 * @return string HTML menu
 * @access public
 */
	public function build($id = null, $options = array(), &$data = null, &$isActive = false) {
		if (is_null($data)) {
			$data =& $this->_menu;
		}

		if (!empty($options)) {
			$this->settings = array_merge($this->settings, $options);
		}

		if (isset($data[$id])) {
			$parent =& $data[$id];
		} else {
			$parent =& $data;
		}

		$out = '';
		$offset = 0;
		$nowIsActive = false;
		if (is_array($parent)) {
			foreach ($parent as $pos => $item) {
				$this->_depth++;

				$itemIsActive = false;
				$ret = $this->_buildItem($item, $pos-$offset, $itemIsActive);

				if ($ret==='') {
					$offset++;
				}

				$out .= $ret;

				$this->_depth--;

				$isActive = $isActive || $itemIsActive;
			}
		}

		if ($out==='') {
			return '';
		}

		$ulId = (isset($this->settings['ulId'])) ? $this->settings['ulId'] : $id;

		if (isset($id) && ($id !== 'children')) {
			$class = ' id="'.$ulId.'" class="'.$this->settings['menuClass'].'"';
		} else {
			$childClass = $isActive ? '' : 'hidden-ul';
			if (!empty($this->settings['childrenDropdown'])) {
				$childClass = trim($this->settings['childrenDropdown'] . ' ' . $childClass);
			}
			$class = ' class="'.$childClass.'"';
		}

		if (isset($options['class'])) {
			$class .= ' class="'.$options['class'].'"';
		}
		
		$pad = str_repeat("\t", $this->_depth);		
		
		if((isset($id) && ($id == 'children')))
		$wrapperFormat=$this->settings['wrapperFormat'];
		else
		$wrapperFormat='<ul%s>%s</ul>';
		return sprintf('%s'.$wrapperFormat."\n", $pad, $class, "\n".$out.$pad);
	}

/**
 * Returns a menu item HTML.
 *
 * @param array Array of menu item
 * @param int optional Position of the item.
 * @return string HTML menu item
 * @access protected
 */
	protected function _buildItem(&$item, $pos = -1, &$isActive = false) {
		$item = array_merge($this->defaults, $item);

		if ($item['separator']) {
			return $item['separator'];
		}

		if (is_null($item['title'])) {
			return '';
		}

		if (!empty($item['permissions']) && !in_array($this->_group, (array)$item['permissions'])) {
			return '';
		}

		$children = '';
		$childIsActive = false;
		if ($hasChildren = is_array($item['children'])) {
			$this->_depth++;

			$children = $this->build('children', array(), $item, $childIsActive);

			$this->_depth--;
		}

		// For Permissions empty child
		if ($children==='') {
			$hasChildren = false;
		}

		$check = false;
		$controller = strtolower((string)$this->params['controller']);
		$action = isset($this->params['action']) ? strtolower((string)$this->params['action']) : '';
		$prefix = isset($this->params['prefix']) ? strtolower((string)$this->params['prefix']) : '';
		if ($prefix !== '' && strpos($action, $prefix . '_') === 0) {
			$action = substr($action, strlen($prefix) + 1);
		}

		if (!empty($item['url']) && is_array($item['url'])) {
			$itemCtrl = !empty($item['url']['controller']) ? strtolower(trim((string)$item['url']['controller'])) : '';
			if ($itemCtrl !== '') {
				if ($item['partialMatch']) {
					$check = (strpos(Router::normalize($controller), Router::normalize($itemCtrl)) !== false);
				} else {
					$check = (Router::normalize($controller) === Router::normalize($itemCtrl));
					if ($check && isset($item['url']['action']) && $item['url']['action'] !== null) {
						$itemAction = strtolower(trim((string)$item['url']['action']));
						if ($itemAction !== '' && $itemAction !== 'index') {
							$check = (Router::normalize($action) === Router::normalize($itemAction));
						}
					}
				}
			}
		}

		if ($check == false && !empty($item['selName'])) {
			if (strpos($item['selName'], ",") !== false) {
				$selArr = explode(",", $item['selName']);
				foreach ($selArr as $selValue) {
					$selValue = strtolower(trim($selValue));
					if ($selValue !== '' && Router::normalize($controller) === Router::normalize($selValue)) {
						$check = true;
						break;
					}
				}
			} else {
				$selValue = strtolower(trim((string)$item['selName']));
				if ($selValue !== '') {
					$check = (Router::normalize($controller) === Router::normalize($selValue));
				}
			}
		}

		$isActive = $childIsActive || $check;

		$arrClass = array();

		if ($pos===0) {
			$arrClass[] = $this->settings['firstClass'];
		}

		if ($isActive) {
			$arrClass[] = $this->settings['activeClass'];
		}

		if ($hasChildren) {
			$arrClass[] = $this->settings['childrenClass'];
		}

		if ($this->settings['evenOdd']) {
			$arrClass[] = (($pos&1)?'even':'odd');
		}

		$class = '';
		$arrClass = array_filter($arrClass);
		if (isset($item['class'])) {
			if (is_array($item['class'])) {
				$arrClass = array_merge($arrClass, $item['class']);
			}

			else $arrClass[] = $item['class'];
		}

		if (!empty($arrClass)) {
			$class = ' class="'.implode(' ', $arrClass).'"';
		}

		if (isset($item['id'])) {
			$class = ' id="'.$item['id'].'"'.$class;
		}

		$hasEmptyCtrl = empty($item['url']) || !is_array($item['url']) || empty($item['url']['controller']);
		if ($hasEmptyCtrl && !$hasChildren) {
			return '';
		}
		$aClasses = array();
		if ($hasChildren) {
			$aClasses[] = 'has-ul';
		}
		if ($isActive) {
			$aClasses[] = 'active';
		}
		$aClassAttr = !empty($aClasses) ? ' class="' . implode(' ', $aClasses) . '"' : '';

		if ($hasEmptyCtrl || (is_string($item['url']) && ($item['url'] === '#' || strpos($item['url'], 'javascript:') === 0))) {
			$url = '<a' . $aClassAttr . ' href="javascript:void(0);">' . $item['title'] . '</a>';
		} else {
			$url = '<a' . $aClassAttr . ' href="' . Router::url($item['url']) . '">' . $item['title'] . '</a>';
		}

		$pad = str_repeat("\t", $this->_depth);
		if ($hasChildren) {
			$urlPad = str_repeat("\t", $this->_depth+1);
			$url = "\n".$urlPad.$url;
			$children = "\n".$children.$pad;
		}

		return sprintf('%s'.$this->settings['itemFormat']."\n", $pad, $class, $url, $children);
	}

}
