<?php

/**
 * 	
 *	@author Dmitry M03G Gurin
 */

class RoutList {

	public $path = '';
	private $uriarr = array();
	private $uri = '';
	private $prefix = '';

	function __construct() {
		$this->prefix = '';
	}

	public $params = array();

	public function router() {
		$this->uriarr = getUrlParams(TRUE, FALSE);
		$this->uri = $_SERVER['REQUEST_URI'];

		addRule('/routlist', 'renderHome');

		addRule('/routlist/page_{int|page_id}', 'renderPage');
		addRule('/routlist/page_{int|page_id}/print', 'renderPrintPage');
		addRule('/routlist/page_{int|page_id}/print_blank', 'renderPrintPage');

		addRule('/routlist/newtemplate', 'newTemplate');

		addRule('/routlist/service', 'renderService');
		addRule('/routlist/service/addbort', 'renderAddBort');
		addRule('/routlist/service/area', 'renderArea');
		addRule('/routlist/service/area/add', 'renderAddArea');
		addRule('/routlist/service/type', 'renderTypeBort');
		addRule('/routlist/service/type_{int|type_id}/mod', 'renderModBort');
		addRule('/routlist/service/barcode', 'renderBarcode');

		addRule('/routlist/monitoring', 'renderMonitoring');
		addRule('/routlist/monitoring/area_{int|area_id}', 'renderMonitoringArea');
		addRule('/routlist/monitoring/area_{int|area_id}/mon_{int|mon_id}/edit', 'renderMonEdit');
		addRule('/routlist/monitoring/area_{int|area_id}/mon_{int|mon_id}/screen_{int|screen_id}/edit', 'renderScreenEdit');
		addRule('/routlist/mon_{int|mon_id}', 'renderMon');
		addRule('/routlist/mon_{int|mon_id}/screen_{int|screen_id}', 'renderMon');

		addRule('/routlist/overview', 'renderOverview');
		addRule('/routlist/overview/type_{int|type_id}', 'renderOverviewType');

		addRule('/routlist/template_{int|template_id}', 'renderTemplate');
		addRule('/routlist/template_{int|template_id}/print', 'renderPrintTpl');
		addRule('/routlist/template_{int|template_id}/edit', 'renderEditTpl');
		addRule('/routlist/template_{int|template_id}/save', 'editTemplate');
		addRule('/routlist/template_{int|template_id}/copy', 'copyTemplate');
		addRule('/routlist/template_{int|template_id}/confirm', 'confirmTemplate');
		addRule('/routlist/template_{int|template_id}/delete', 'deleteTemplate');

		addRule('/routlist/shablist', 'renderShabList');
		addRule('/routlist/shablist/toscreen_{int|screen_id}', 'renderShabList');
		addRule('/routlist/shablist/type_{int|type_bort_id}', 'renderShabList');
		addRule('/routlist/shablist/type_{int|type_bort_id}/category_{int|category_id}', 'renderShabList');		

		addRule('/routlist/routpages', 'renderRoutPages');
		addRule('/routlist/routpages/toscreen_{int|screen_id}', 'renderRoutPages');
		addRule('/routlist/routpages/area_{int|area_id}', 'renderRoutPages');
		addRule('/routlist/routpages/area_{int|area_id}/type_{int|type_bort_id}', 'renderRoutPages');
		addRule('/routlist/routpages/area_{int|area_id}/type_{int|type_bort_id}/category_{int|category_id}', 'renderRoutPages');
	}


	private function setShabPar() {
		CM::$init->Shab->module = 'routlist';
		CM::$init->Shab->addCss('/shab/modules/' . CM::$init->Shab->module . '/css/' . CM::$init->Shab->module . '.css');
	}

	public function renderHome() {
		$data_arr['leftmenu'] = $this->genLeftMenu();
		error_log("shop - renderhome");

		// $res = q("SELECT * FROM `config` WHERE 1");
		// while ($row = fa($res)) {
		// 	error_log($row['id']);
		// }

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('content', "render_home");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/shop/js/shop.js?v=0.14');
		$shab->title = "title";
		$shab->renderTpl();
	}

	public function arrUrl() {
		$mhash = explode("#", $_SERVER['REQUEST_URI']);
		$uri = $mhash[0];
		$hash = $mhash[1];
		$getmass = explode("?", $uri);
		if ($getmass[0]) {
			$uri = $getmass[0];
		}
		$urimass = explode("/", $uri);
		// error_log($uri);
		// foreach ($urimass as $key => $value) {
		// 	error_log("$key $value");
		// }
		foreach ($urimass as $key => $value) {
			if ($value) {
				$ch = explode("_", $value, 2);
				$mass[$ch[0]] = $ch[1];
			}
		}
		// for ($i=0; $i < count($urimass); $i++) { 
		// 	error_log($urimass[$i]);
		// 	$ch = explode("_", $urimass[$i], 2);
		// 	$mass[$ch[0]] = $ch[1];
		// }
		$mass['getparam'] = $getmass[1];
		$mass['uri'] = $uri;
		return $mass;
	}

	// Очень странное построение меню и доп.меню
	public function genLeftMenu() {
		$resmenu = array();

		$menus = q("SELECT * FROM `" . $this->prefix . "leftmenu` WHERE 1 ");
		while ($menu = fa($menus)) {
			$resmenu['main'][] = array('title' => $menu['title'], 'url' => $menu['url']);
		}

		$urlarr = $this->arrUrl();

		// Список маршрутых листов
		if (array_key_exists('routpages', $urlarr)){			
			if (!$urlarr['area'] AND !$urlarr['type'] AND !$urlarr['category']) {
				$resmenu['secondary']['title'] = 'Участки производства:';
				$menus = q("SELECT * FROM `" . $this->prefix . "uchi` WHERE 1 ");
				while ($menu = fa($menus)) {
					$resmenu['secondary']['items'][] = array('title' => $menu['nameu'], 'url' => $this->uri . '/area_' . $menu['idu']);
				}
				$resmenu['secondary']['items'][] = array('title' => 'Нераспределённые', 'url' => $this->uri . '/area_0');
			}
			// Список маршрутых листов. Выбран участок.
			if ($urlarr['area'] AND !$urlarr['type'] AND !$urlarr['category']) {
				$resmenu['secondary']['title'] = 'Типы бортов:';
				$menus = q("SELECT * FROM `" . $this->prefix . "tp` WHERE 1 ");
					while ($menu = fa($menus)) {
						$resmenu['secondary']['items'][] = array('title' => $menu['nametp'], 'url' => $this->uri . '/type_' . $menu['idtp']);
					}
				$resmenu['secondary']['items'][] = array('title' => 'Нераспределённые', 'url' => $this->uri . '/type_0');
			}
			// Список маршрутых листов. Выбраны участок и тип борта
			if ($urlarr['type']) {
				if (isset($urlarr['category'])){
					$tempurl = explode('/category_', $this->uri)[0];
				} else {
					$tempurl = $this->uri;
				}
				$resmenu['secondary']['title'] = 'Модификации:';
				$menus = q("SELECT * FROM `" . $this->prefix . "cat` WHERE `tp` = " . $this->params['type_bort_id'] . " ");
				while ($menu = fa($menus)) {
					$resmenu['secondary']['items'][] = array('title' => $menu['namec'], 'url' => $tempurl . '/category_' . $menu['idc']);
				}
				$resmenu['secondary']['items'][] = array('title' => 'Нераспределённые', 'url' => $tempurl . '/category_0');
			}
		}

		if($urlarr['template']) {
			$template = q("	SELECT status, dateu FROM `templates` WHERE `templates`.id = " . $this->params['template_id'] . " LIMIT 1");
			$template = fa($template);
			$resmenu['secondary']['title'] = 'Операции с шаблоном:';
			$resmenu['secondary']['items'][] = array('title' => 'Распечатать', 'url' => '/routlist/template_' . $this->params['template_id'] . '/print');
			$resmenu['secondary']['items'][] = array('title' => 'Копировать', 'url' => '/routlist/template_' . $this->params['template_id'] . '/copy');
			if (!$template['dateu'] AND !$template['status']) {
				$resmenu['secondary']['items'][] = array('title' => 'Утвердить', 'url' => '/routlist/template_' . $this->params['template_id'] . '/confirm');
				$resmenu['secondary']['items'][] = array('title' => 'Редактировать', 'url' => '/routlist/template_' . $this->params['template_id'] . '/edit');
				$resmenu['secondary']['items'][] = array('title' => 'Удалить', 'url' => '/routlist/template_' . $this->params['template_id'] . '/delete');
			}
			$resmenu['secondary']['items'][] = array('title' => 'В архив', 'url' => '/routlist/template_' . $this->params['template_id'] . '/arch');
		}

		if($urlarr['page']) {
			$resmenu['secondary']['items'][] = array('title' => 'Печать маршрутной карты', 'url' => $this->uri . '/print');
			$resmenu['secondary']['items'][] = array('title' => 'Печать заполненного бланка', 'url' => $this->uri . '/print_blank');
		}

		if (array_key_exists('service', $urlarr)){
			$resmenu['secondary']['items'][] = array('title' => 'Участки', 'url' => '/routlist/service/area');
			$resmenu['secondary']['items'][] = array('title' => 'Типы бортов', 'url' => '/routlist/service/type');
			$resmenu['secondary']['items'][] = array('title' => 'Печать штрих-кодов', 'url' => '/routlist/service/barcode');
			$resmenu['secondary']['items'][] = array('title' => 'Добавить борт (генерация м.л.)', 'url' => '/routlist/service/addbort');
		}

		if (array_key_exists('overview', $urlarr)){
			$menus = q("SELECT * FROM `tp` WHERE 1 ");
			while ($menu = fa($menus)) {
				$resmenu['secondary']['items'][] = array('title' => $menu['nametp'], 'url' => '/routlist/overview/type_' . $menu['idtp']);
			}
		}

		if (array_key_exists('monitoring', $urlarr)){
			$menus = q("SELECT * FROM `uchi` WHERE 1 ");
			while ($menu = fa($menus)) {
				$resmenu['secondary']['items'][] = array('title' => $menu['nameu'], 'url' => '/routlist/monitoring/area_' . $menu['idu']);
			}
		}

		if (array_key_exists('shablist', $urlarr)){
			if (!$urlarr['type']){
				$resmenu['secondary']['title'] = 'Типы бортов:';
				$menus = q("SELECT * FROM `tp` WHERE 1 ");
				while ($menu = fa($menus)) {
					$resmenu['secondary']['items'][] = array('title' => $menu['nametp'], 'url' => $this->uri . '/type_' . $menu['idtp']);
				}
				$resmenu['secondary']['items'][] = array('title' => 'Нераспределённые', 'url' => $this->uri . '/type_0');
			}

			if ($urlarr['type']) {
				if (isset($urlarr['category'])){
					$tempurl = explode('/category_', $this->uri)[0];
				} else {
					$tempurl = $this->uri;
				}
				$resmenu['secondary']['title'] = 'Модификации:';
				$menus = q("SELECT * FROM `cat` WHERE `tp` = " . $this->params['type_bort_id'] . " ");
				while ($menu = fa($menus)) {
					$resmenu['secondary']['items'][] = array('title' => $menu['namec'], 'url' => $tempurl . '/category_' . $menu['idc']);
				}
				$resmenu['secondary']['items'][] = array('title' => 'Нераспределённые', 'url' => $tempurl . '/category_0');
			}
		}
		$resmenu['tt'] = $this->params;
		$resmenu['ttt'] = $this->arrUrl;

		return $resmenu;
	}

	/** 
	*	получение данных из таблицы. Если id указан, вернёт одно значение. Иначе - всю таблицу.
	*	@param stribg $table - имя таблицы в базе
	* 	@param string $tid - поле идентификатора
	*	@param $id - идентификатор
	*	@param string $endparam - параметр, который добавляется в конец запроса к базе. ORDER BY или LIMIT, например.
	*	@return $ret_area
	*
	*/
	public function getData($table, $tid = false, $id = false, $endparam = false) {
		$ret_area = array();
		if (!$id) {
			$param = '1';			
		} else {
			$param = $tid . " = '" . $id . "'";
		}
		// error_log("SELECT * FROM `" . $table . "` WHERE " . $param . " " . $endparam);
		$areas = q("SELECT * FROM `" . $table . "` WHERE " . $param . " " . $endparam);
		while ($area = fa($areas)){
			$ret_area[] = $area;
		}
		// if (count($ret_area) == 1 AND $id) {
		// 	$ret_area = $ret_area[0];
		// }
		return $ret_area;
	}

	// public function getType($id) {
	// 	$type = q("SELECT * FROM `tp` WHERE idtp = " . $id);
	// 	$type = fa($type);
	// 	return $type;
	// }

	// public function getCategory($id) {
	// 	$category = q("SELECT * FROM `cat` WHERE idc = " . $id);
	// 	$category = fa($category);
	// 	return $category;
	// }

	// public function getListWork($id) {
	// 	$lw = array();
	// 	$lworks = q("SELECT * FROM `list_work` WHERE `ids` = " . $id . " ORDER BY `num`");
	// 	while ($lwork = fa($lworks)) {
	// 		$lw[] = $lwork;
	// 	}
	// 	return $lw;
	// }

	// добавление нового шаблона
	public function newTemplate() {	
		$tpl = q("SELECT * FROM `templates` WHERE `name` = '' AND `title` = '' AND `uies` = '' LIMIT 1");
		if (nr($tpl)) {
			$tpltemp = fa($tpl);
			$t = $tpltemp['id'];
		} else {
			sql_insert(array('date' => time(), 'ts' => 2), 'templates');
			$t = sql_inid();	
		}
		
		redirect('/routlist/template_' . $t . '/edit');
	}

	//удаление шаблона
	public function deleteTemplate() {
		q("DELETE FROM `list_work` WHERE `ids` = " . $this->params['template_id']);
		q("DELETE FROM `templates` WHERE `id` = " . $this->params['template_id']);
		redirect('/routlist/shablist');
	}

	// утверждение шаблона
	public function confirmTemplate() {
		q("UPDATE `templates` SET `dateu` = " . time() . ", `status` = 1 WHERE `id` = " . $this->params['template_id']);
		redirect('/routlist/template_' . $this->params['template_id']);
	}

	// копирование шаблона
	public function copyTemplate() {
		$template = $this->getData('templates', 'id', $this->params['template_id'])[0];
		array_shift($template);
		$template['dateu'] = $template['status'] = 0;
		$template['copy'] = 1;
		$template['ver']++;
		sql_insert($template, 'templates');
		$t = sql_inid();

		$listwork = $this->getData('list_work', 'ids', $this->params['template_id']);		
		foreach ($listwork as $work) {
			array_shift($work);
			$work['ids'] = $t;
			sql_insert($work, 'list_work');
		}		

		redirect('/routlist/template_' . $t . '/edit');		
	}

	// метод для получения данных о шаблоне
	public function getTemplate($id) {
		$tpl = array();
		$template = q("	SELECT 
						`templates`.id as tid, 
						`templates`.title as ttitle, 
						`uies`, 
						`date`, 
						`dateu`,
						`au`, 
						`name`, 
						`u`, 
						`t`, 
						`c`, 
						`ts`, 
						`status`, 
						`ver`, 
						`copy`, 
						`tsh`.title as tshtitle, 
						`t11`, 
						`t12`, 
						`t13`, 
						`t21`, 
						`t22`, 
						`t23`
						FROM `templates` INNER JOIN `tsh` ON `templates`.ts = `tsh`.id WHERE `templates`.id = " . $id . " LIMIT 1");
		$template = fa($template);
		$tpl['id_tpl'] = $template['tid'];
		$tpl['title_tpl'] = $template['ttitle'];
		$tpl['name_tpl'] = $template['name'];
		$tpl['title_tsh'] = $template['tshtitle'];
		$tpl['uies'] = $template['uies'];
		$tpl['date'] = $template['date'];
		$tpl['date_confirm'] = $template['dateu'];
		$tpl['author'] = $template['au'];		
		$tpl['t11'] = $template['t11'];
		$tpl['t12'] = $template['t12'];
		$tpl['t13'] = $template['t13'];
		$tpl['t21'] = $template['t21'];
		$tpl['t22'] = $template['t22'];
		$tpl['t23'] = $template['t23'];
		$tpl['area'] = $template['u'];
		$tpl['type'] = $template['t'];
		$tpl['category'] = $template['c'];
		$tpl['template'] = $template['ts'];
		$tpl['status'] = $template['status'];
		$tpl['version'] = $template['ver'];
		$tpl['copy'] = $template['copy'];
		return $tpl;
	}

	public function editTemplate() {
		if ($_POST) {
			q("UPDATE `templates` SET 
				`name` = '" . $_POST['namesb'] . "', 
				`title` = '" . $_POST['nametempl'] . "',  
				`uies` = '" . $_POST['uies'] . "', 
				`date` = '" . time() . "', 
				`au` = '0', 
				`u` = '" . $_POST['area'] . "', 
				`t` = '" . $_POST['type'] . "', 
				`c` = '" . $_POST['category'] . "', 
				`ts` = '" . $_POST['template'] . "',
				`ver` = '" . $_POST['ver'] . "',
				`copy` = 0 
				WHERE `id` = '" . $this->params['template_id'] . "'");
			q("DELETE FROM `list_work` WHERE `ids` = " . $this->params['template_id']);
			foreach ($_POST['namework'] as $key => $value) {
				q("INSERT INTO `list_work` (`uch`, `rab`, `ids`, `num`, `tr`, `mt`) VALUES (
					'" . $_POST['namearea'][$key] . "', 
					'" . $value . "', 
					'" . $this->params['template_id'] . "', 
					'" . $_POST['numwork'][$key] . "', 
					'" . strtr($_POST['trud'][$key], ',', '.') . "', 
					'" . strtr($_POST['mjtime'][$key], ',', '.') . "')");
			}
		}
		// error_log(strtr($_POST['trud'][$key], ',', '.'));
		redirect('/routlist/template_' . $this->params['template_id'] . '/edit');
	}

	// рендер страницы "Сервис"
	public function renderService() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/service", 'name' => "Сервис");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_service");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		// $shab->addScript('/shab/modules/routlist/js/formTemplate.js?v=0.14');
		// $shab->addScript('/shab/base/js/jquery-ui-1.10.4.custom.min.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderMonitoring() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/monitoring", 'name' => "Мониторинг");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_monitoring");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderOverview() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/overview", 'name' => "Обзор производства");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_overview");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderOverviewType() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$areas = q("SELECT DISTINCT `idu`, `nameu`, `nu` FROM `templates` LEFT JOIN `uchi` ON `templates`.u = `uchi`.idu WHERE `t` = '" . $this->params['type_id'] . "' ORDER BY `u`");
		while($area = fa($areas)){
			$data_arr['area'][] = $area;
		}

		// получаем список бортов в работе
		$borts = q("SELECT * FROM `borts` WHERE `t` = '" . $this->params['type_id'] . "' ORDER BY `numb`");
		while ($bort = fa($borts)){

			$result['bort'][$bort['id']] = $bort;

			// получаем список шаблонов для определённого типа борта
			$templates = q("SELECT * FROM `templates` WHERE `t` = '" . $this->params['type_id'] . "' AND `status` = 1 ORDER BY `u`");
			while ($template = fa($templates)){
				$result['bort'][$bort['id']]['templates'][$template['id']] = array('title' => $template['name'], 'area' => $template['u']);

				// получаем список сгенерированных по шаблонам маршруток
				
				$lists = q("SELECT * FROM `lists` WHERE `pni` = '" . $bort['numb'] . "' AND `id_sh` = '" . $template['id'] . "'");
				$list = fa($lists);
				if ($list['datep']) {
					// $result['bort']['templates'][$template['id']]['list'] = $list;

					$operations = q("SELECT * FROM `list_work` LEFT JOIN `complete` ON `list_work`.id = `complete`.idwork AND `idlist` = '" . $list['id'] . "' AND `del` = 0 WHERE `ids`='" . $template['id'] . "' ORDER BY `num`");
					$count = nr($operations);
					$listtime = $countmt = $compl = 0;
					while ($opertion = fa($operations)){
						if ($opertion['idc']){
							$compl+= ($opertion['tr'] + $opertion['mt']);
						}
						$listtime+= ($opertion['tr'] + $opertion['mt']);
					}
					$per = $listtime / 100;
					$co = floor($compl / $per);
					$result['bort'][$bort['id']]['templates'][$template['id']]['complete'] = $co;

				}
			}
		}

		$data_arr['overview'] = $result;
		$data_arr['screens'] = $sc;
		
		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['base'] = "shab/base/tpl/base_empty.tpl.php";
		$shab->tpls['container'] = "shab/base/tpl/container/empty.tpl.php";
		$shab->setTpl('content', "render_over");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderMonitoringArea() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$areas = q("SELECT * FROM `uchi` INNER JOIN `monitor` ON `uchi`.idu = `monitor`.uch WHERE `uchi`.idu = " . $this->params['area_id']);
		while ($area = fa($areas)){
			$title = $area['nameu'];
			$data_arr['mon'][] = $area;
		}

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/monitoring", 'name' => "Мониторинг");
		$breadcrumbs[] = array('url' => "/routlist/monitoring/area_" . $this->params['area_id'], 'name' => "Участок: " . $title);
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_monitoring_area");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderMon() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$data_arr['sc'] = 0;
		if($this->params['screen_id']){
			$data_arr['sc'] = $this->params['screen_id'];
		}

		$screens = q("SELECT * FROM `mons` WHERE `gen` = " . $this->params['mon_id']);
		while ($screen = fa($screens)){
			$screen['sha'] = unserialize($screen['sha']);
			$screen['mli'] = unserialize($screen['mli']);
			if ($screen['sett'] == 2) {
				$wh = '';
				foreach ($screen['sha'] as $key => $template) {
					$wh.= "`id_sh` = " . $template . " OR ";										
				}				
			} else {
				$wh = '';
				foreach ($screen['mli'] as $key => $mlist) {
					$wh.= "`lists`.id = " . $mlist . " OR ";										
				}
			}
			$wh = substr($wh, 0, -3);
			$lists = q("SELECT `lists`.id as lid, `lists`.pni, `templates`.id as tid, `templates`.name FROM `lists` LEFT JOIN templates ON `lists`.id_sh = `templates`.id WHERE " . $wh);
			while ($list = fa($lists)){
				$screen['lists'][$list['lid']] = $list;
			}
			$sc[$screen['id']] = $screen;
		}

		foreach ($sc as $key_screen => $screen) {
			foreach ($screen['lists'] as $key_list => $list) {
				$operations = q("SELECT * FROM `list_work` LEFT JOIN `complete` ON `list_work`.id = `complete`.idwork AND `idlist` = '" . $key_list . "' AND `del` = 0 WHERE `ids`='" . $list['tid'] . "' ORDER BY `num`");
				$count = nr($operations);
				$listtime = $countmt = $compl = 0;
				while ($opertion = fa($operations)){
					if (count($data_arr['l'][$key_list]['oper']) < $count){
						$e = $opertion;
						$data_arr['l'][$key_list]['oper'][] = $e;
					}
					if ($opertion['idc']){
						$compl+= ($opertion['tr'] + $opertion['mt']);
					}
					$listtime+= ($opertion['tr'] + $opertion['mt']);
					if ($opertion['mt']){
						$countmt++;
					}
				}

				$per = $listtime / 100;
				$hour = ((1400 - (($count + $countmt - 1) * 1 + ($count + $countmt) * 4)) / $listtime) / 14;
				$rem = floor($listtime - $compl) . "." . ($listtime - $compl - floor($listtime - $compl)) * 60;
				$data_arr['l'][$key_list]['time'] = $listtime;
				$data_arr['l'][$key_list]['hour'] = $hour;
				$data_arr['l'][$key_list]['compl'] = floor($compl / $per);
				$data_arr['l'][$key_list]['rem'] = $rem;
				// error_log("$listtime $countmt");
			}
		}

		$data_arr['screens'] = $sc;
		
		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['base'] = "shab/base/tpl/base_empty.tpl.php";
		$shab->tpls['container'] = "shab/base/tpl/container/empty.tpl.php";
		$shab->setTpl('content', "render_mon");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();

	}

	public function renderMonEdit() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$screens = q("SELECT `monitor`.id as mid, `monitor`.name as mname, `mons`.id, `mons`.name FROM `monitor` INNER JOIN `mons` ON `monitor`.id = `mons`.gen WHERE `monitor`.id = " . $this->params['mon_id']);
		while ($screen = fa($screens)){
			$mon = $screen['mname'];
			$data_arr['screens'][] = $screen;
		}

		$data_arr['area'] = $this->getData('uchi', 'idu', $this->params['area_id'])[0];

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/monitoring", 'name' => "Мониторинг");
		$breadcrumbs[] = array('url' => "/routlist/monitoring/area_" . $this->params['area_id'], 'name' => "Участок: " . $data_arr['area']['nameu']);
		$breadcrumbs[] = array('url' => "/routlist/monitoring/area_" . $this->params['area_id'] . "/mon_" . $this->params['mon_id'], 'name' => "Монитор: " . $mon . ". Редактирование");
		
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_mon_edit");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderScreenEdit() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$screens = q("	SELECT `monitor`.id as mid, `monitor`.name as mname, `mons`.id, `mons`.name, sett, mli, sha 
						FROM `monitor` 
						INNER JOIN `mons` 
						ON `monitor`.id = `mons`.gen 
						WHERE `monitor`.id = " . $this->params['mon_id']  ." AND `mons`.id = " . $this->params['screen_id'] . " LIMIT 1");
		while ($screen = fa($screens)){
			$sc = $screen;
			$data_arr['screens'][] = $screen;
		}

		if ($sc['sett'] == 1) {
			$listparam = unserialize($sc['mli']);
			$table = 'lists';
		} else {
			$listparam = unserialize($sc['sha']);
			$table = 'templates';
		}

		$wh = '';
		foreach ($listparam as $param) {
			$wh.= "`" . $table . "`.id = " .$param . " OR ";
		}
		$wh = substr($wh, 0, -3);

		if ($sc['sett'] == 1) {
			$pars = q("SELECT `lists`.id, `templates`.name FROM `lists` INNER JOIN `templates` ON `lists`.id_sh = `templates`.id WHERE 1 AND " . $wh);		
		} else {
			$pars = q("SELECT `id`, `name` FROM `templates` WHERE 1 AND " . $wh);
		}
		while ($par = fa($pars)){
			$data_arr['p'][] = $par;
		}

		$data_arr['area'] = $this->getData('uchi', 'idu', $this->params['area_id'])[0];

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/monitoring", 'name' => "Мониторинг");
		$breadcrumbs[] = array('url' => "/routlist/monitoring/area_" . $this->params['area_id'], 'name' => "Участок: " . $data_arr['area']['nameu']);
		$breadcrumbs[] = array('url' => "/routlist/monitoring/area_" . $this->params['area_id'] . "/mon_" . $this->params['mon_id'], 'name' => "Монитор: " . $sc['mname']);
		$breadcrumbs[] = array('url' => "/routlist/monitoring/area_" . $this->params['area_id'] . "/mon_" . $this->params['mon_id'] . "/screen_" . $this->params['mon_id'] . "/edit", 'name' => "Экран: " . $sc['name'] . ". Редактирование");
		
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_screen_edit");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}


	// рендер страницы редактирвоания Шаблона
	public function renderEditTpl() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$template = $this->getTemplate($this->params['template_id']);		
		$data_arr['infotemplate'] = $template;
		$data_arr['lw'] = $this->getData('list_work', 'ids', $this->params['template_id'], 'ORDER BY `num`');
		$data_arr['lu'] = $this->getData('uchi');
		$data_arr['lt'] = $this->getData('tp');
		$data_arr['lc'] = $this->getData('cat');
		$data_arr['lte'] = $this->getData('tsh');

		if (!$data_arr['infotemplate']['title_tpl'] AND !$data_arr['infotemplate']['name_tpl']) {
			$data_arr['infotemplate']['new_tpl'] = 1;
		}

		if (!$template['version']) {
			q("UPDATE `templates` SET `ver` = 1 WHERE `id` = " . $this->params['template_id']);
			$data_arr['infotemplate']['version'] = 1;
			$data_arr['warning']['ver'] = 'У данного шаблона отсутствовало указание версии. Присвоен новый номер версии: 1.';
		}

		if ($template['copy']) {
			$data_arr['warning']['ver'] = 'Обратите внимание: данный шаблон является копией, поэтому его версия автоматически увеличилась на единицу. Если это необходимо, измените версию.';
		}

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/shablist", 'name' => "Список шаблонов");
		if(!$template['title_tpl']){
			$breadcrumbs[] = array('name' => "Новый шаблон");
		} else {
			$breadcrumbs[] = array('url' => "/routlist/template_" . $this->params['template_id'], 'name' => "Шаблон: " . $template['title_tpl']);
		}		
		$breadcrumbs[] = array('name' => "Редактирование");
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_edittpl");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/formTemplate.js?v=0.14');
		$shab->addScript('/shab/base/js/jquery-ui-1.10.4.custom.min.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();

	}

	// рендер страницы печати шаблшона
	public function renderPrintTpl() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$template = $this->getTemplate($this->params['template_id']);		
		$data_arr['infotemplate'] = $template;
		$data_arr['area'] = $this->getData('uchi', 'idu', $template['area'])[0];
		$data_arr['type'] = $this->getData('tp', 'idtp', $template['type'])[0];
		$data_arr['lw'] = $this->getData('list_work', 'ids', $this->params['template_id'], 'ORDER BY `num`');

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/shablist", 'name' => "Список шаблонов");
		$breadcrumbs[] = array('url' => "/routlist/template_" . $this->params['template_id'], 'name' => "Шаблон: " . $template['title_tpl']);
		$breadcrumbs[] = array('name' => "Печать");
		$data_arr['breadcrumbs'] = $breadcrumbs;

		if ($_POST AND $template['status']) {
			q("INSERT INTO `lists` (`id_sh`, `pni`, `date`, `datep`, `lu`, `lt`, `lc`, `lts`) 
				VALUES (" . $template['id_tpl'] . ",
						'" . $_POST['pni'] . "',
						'" . time() . "', 
						'" . time() . "', 
						'" . $template['area'] . "', 
						'" . $template['type'] . "', 
						'" . $template['category'] . "',
						'" . $template['ts'] . "')");
			$data_arr['id_page'] = sql_inid();
		}

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		if ($_POST) {
			$shab->tpls['base'] = "shab/base/tpl/base_empty.tpl.php";
			$shab->tpls['container'] = "shab/base/tpl/container/empty.tpl.php";
		}
		$shab->setTpl('content', "render_printtpl");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();

	}

	public function renderTypeBort() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$tps = q("SELECT * FROM `tp` WHERE 1");
		while ($tp = fa($tps)){
			$data_arr['type'][] = $tp;
		}

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/service", 'name' => "Сервис");
		$breadcrumbs[] = array('url' => "/routlist/service/type", 'name' => "Типы бортов");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_service_type");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
		
	}

	public function renderModBort() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$tps = q("SELECT * FROM `tp` WHERE `idtp` = " . $this->params['type_id']);
		$data_arr['type'] = fa($tps);

		$mods = q("SELECT * FROM `cat` WHERE `tp` = " . $this->params['type_id']);
		while ($mod = fa($mods)){
			$data_arr['mod'][] = $mod;
		}

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/service", 'name' => "Сервис");
		$breadcrumbs[] = array('url' => "/routlist/service/type", 'name' => "Тип борта: " . $data_arr['type']['nametp']);
		$breadcrumbs[] = array('url' => "/routlist/service/type_" . $this->params['type_id'] . "/mod", 'name' => "Модификации");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_service_mod");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
		
	}

	public function renderBarcode() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$areas = q("SELECT * FROM `uchi` WHERE 1");
		while ($area = fa($areas)){
			$data_arr['area'][] = $area;
		}

		$users = q("SELECT * FROM `users` WHERE 1");
		while ($user = fa($users)){
			$data_arr['user'][] = $user;
		}

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/service", 'name' => "Сервис");
		$breadcrumbs[] = array('url' => "/routlist/service/barcode", 'name' => "Печать штрих-кодов");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_service_barcode");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
		
	}

	public function renderArea() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$areas = q("SELECT * FROM `uchi` INNER JOIN `users` ON `uchi`.ruk = `users`.id WHERE 1");
		while ($area = fa($areas)){
			$data_arr['area'][] = $area;
		}

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/service", 'name' => "Сервис");
		$breadcrumbs[] = array('url' => "/routlist/service/area", 'name' => "Участки");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_service_area");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
		
	}
	
	public function renderAddArea() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/service", 'name' => "Сервис");
		$breadcrumbs[] = array('url' => "/routlist/service/area", 'name' => "Участки");
		$breadcrumbs[] = array('url' => "/routlist/service/area/add", 'name' => "Добавление участка");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_service_area_add");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
		
	}

	// рендер страницы добавления борта и генерации маршруток
	public function renderAddBort() {
		$data_arr = array();
		$data_arr['leftmenu'] = $this->genLeftMenu();

		$data_arr['lt'] = $this->getData('tp');
		$data_arr['lc'] = $this->getData('cat');

		if ($_POST['pni'] AND $_POST['type'] AND $_POST['mod']) {
			$templates = q("SELECT * FROM `templates` WHERE `t` = " . $_POST['type'] . " AND `c` = " . $_POST['mod'] . " ORDER BY `name`");
			while ($template = fa($templates)){
				if(!$ts[$template['uies']] OR $ts[$template['uies']]['v'] < $template['ver']) {
					unset($tempts[$ts[$template['uies']]['id']]);
					$ts[$template['uies']]['v'] = $template['ver'];
					$ts[$template['uies']]['id'] = $template['id'];
					$tempts[] = $template['id'];
				}
				$data_arr['templates'][] = $template;
			}
			$data_arr['check_id'] = $tempts;				
		}

		if ($_POST['ch'] AND $_POST['numbort']) {
			q("INSERT INTO `borts` (`numb`, `t`) VALUES ('" . $_POST['numbort'] . "', '" . $_POST['typebort'] . "')");
			foreach ($_POST['ch'] as $key => $value) {
				// error_log($value);
				q("INSERT INTO `lists` (`id_sh`, `pni`, `date`) VALUES (" . $key . ", '" . $_POST['numbort'] . "', '" . time() . "')");
				$listgen[] = sql_inid();
			}
			$where = '';
			foreach ($listgen as $key => $value) {
				$where.= '`lists`.id = ' . $value . ' OR ';
			}
			$where = substr($where, 0, -3);
			$pages = q("SELECT `lists`.id as ln, id_sh, `lists`.date as ld, pni, `templates`.name, `templates`.uies	FROM `lists` INNER JOIN `templates` ON `templates`.id = `lists`.id_sh WHERE " . $where);
			while ($page = fa($pages)) {
				$data_arr['pages'][] = $page;
			}
		}

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/service", 'name' => "Сервис");
		$breadcrumbs[] = array('url' => "/routlist/service/addbort", 'name' => "Добавление борта");
		
		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_addbort");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();

	}

	// рендер страницы печати маршрутки
	public function renderPrintPage() {
		$urlarr = $this->arrUrl();
		$data_arr = array();

		// $page = $this->getData('templates', 'id', $this->params['template_id'])[0];

		$page = q("	SELECT `lists`.id as ln, `id_sh`, `lists`.date as ld, `datep`, `pni`, `lu`, `lt`, `lc`, `templates`.name, `templates`.uies, `ts` 
					FROM `lists` INNER JOIN `templates` ON `templates`.id = `lists`.id_sh WHERE `lists`.id = " . $this->params['page_id']);
		$page = fa($page);

		if(!$page['datep']) {
			q("UPDATE `lists` SET `datep` = " . time() . " WHERE `id` = " . $page['ln']);
			$page['datep'] = time();
		}

		$data_arr['page'] = $page;

		$list_work = q("SELECT * FROM `list_work` LEFT JOIN `complete` ON `list_work`.id = `complete`.idwork AND `idlist` = " . $this->params['page_id'] . " AND `del` = 0 WHERE `ids` = " . $page['id_sh'] . " ORDER BY `num`");
		while ($lw = fa($list_work)) {
			if ($lw['iduser']) {
				$lw['user'] = $this->getData('users', 'id', $lw['iduser'])[0]['mininame'];
			}
			$data_arr['lw'][] = $lw;
		}

		$template = $this->getTemplate($page['id_sh']);
		$data_arr['infotemplate'] = $template;
		$data_arr['area'] = $this->getData('uchi', 'idu', $template['area'])[0];
		$data_arr['type'] = $this->getData('tp', 'idtp', $template['type'])[0];

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['base'] = "shab/base/tpl/base_empty.tpl.php";
		$shab->tpls['container'] = "shab/base/tpl/container/empty.tpl.php";
		$shab->setTpl('content', "render_printpage");
		if ($urlarr['print'] == 'blank') {
			$shab->setTpl('content', "render_printblank");
		}
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderTemplate() {
		$data_arr = array();
		$urlarr = $this->arrUrl();

		$data_arr['leftmenu'] = $this->genLeftMenu();
		
		$template = $this->getTemplate($this->params['template_id']);		
		$data_arr['infotemplate'] = $template;

		$lworks = q("SELECT * FROM `list_work` WHERE `ids` = " . $this->params['template_id'] . " ORDER BY `num`");
		while ($lwork = fa($lworks)) {
			$data_arr['lw'][] = $lwork;
		}
		if (!$template['version']){
			$data_arr['warning']['ver'] = 'Обратите внимание: у данного шаблона не указана версия. Пройдите в раздел "редактирование" и укажите версию шаблона.';
		}
		
		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/shablist", 'name' => "Список шаблонов");
		$breadcrumbs[] = array('name' => "Шаблон: " . $template['title_tpl']);

		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_template");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderPage() {
		$data_arr = array();
		$urlarr = $this->arrUrl();

		$data_arr['leftmenu'] = $this->genLeftMenu();


		
		$page = q("	SELECT `lists`.id as ln, `id_sh`, `lists`.date as ld, `pni`, `lu`, `lt`, `lc`, `templates`.name, `templates`.uies, `ts` 
					FROM `lists` INNER JOIN `templates` ON `templates`.id = `lists`.id_sh WHERE `lists`.id = " . $this->params['page_id']);
		$page = fa($page);

		$data_arr['page'] = $page;

		$list_work = q("SELECT * FROM `list_work` LEFT JOIN `complete` ON `list_work`.id = `complete`.idwork AND `idlist` = " . $this->params['page_id'] . " AND `del` = 0 WHERE `ids` = " . $page['id_sh'] . " ORDER BY `num`");
		while ($lw = fa($list_work)) {
			if ($lw['iduser']) {
				$lw['user'] = $this->getData('users', 'id', $lw['iduser'])[0]['mininame'];
			}
			$data_arr['lw'][] = $lw;
		}

		$template = $this->getTemplate($page['id_sh']);		
		$data_arr['infotemplate'] = $template;
		
		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/routpages", 'name' => "Список маршрутных листов");
		$breadcrumbs[] = array('name' => "Маршрутный лист: " . $page['name']);

		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_page");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/shop.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderRoutPages() {
		$data_arr = array();

		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/routpages", 'name' => "Список маршрутных листов");

		$data_arr['leftmenu'] = $this->genLeftMenu();

		if (is_int($this->params['area_id'])) {
			$aid = $this->params['area_id'];
			$karea = 'AND `lu` = ' . $aid;
			$typetitle = q("SELECT * FROM `" . $this->prefix . "uchi` WHERE `idu` = " . $aid . " LIMIT 1");
			$typetitle = fa($typetitle);
			if(!$typetitle){
				$typetitle['nameu'] = "Нераспределённые";
			}
			$breadcrumbs[] = array('url' => "/routlist/routpages/area_" . $this->params['area_id'], 'name' => "Участок: " . $typetitle['nameu']);
		}

		if (is_int($this->params['screen_id'])) {
			$data_arr['screen'] = $this->params['screen_id'];
		}

		if (is_int($this->params['type_bort_id'])) {
			$tid = $this->params['type_bort_id'];
			$ktype = 'AND `t` = ' . $tid;
			$typetitle = q("SELECT * FROM `" . $this->prefix . "tp` WHERE `idtp` = " . $tid . " LIMIT 1");
			$typetitle = fa($typetitle);
			if(!$typetitle){
				$typetitle['nametp'] = "Нераспределённые";
			}
			$breadcrumbs[] = array('url' => "/routlist/routpages/area_" . $this->params['area_id'] . '/type_' . $this->params['type_bort_id'], 'name' => "Тип: " . $typetitle['nametp']);
		}

		if (is_int($this->params['category_id'])) {
			$cid = $this->params['category_id'];
			$kcat = 'AND `c` = ' . $cid;
			$typetitle = q("SELECT * FROM `" . $this->prefix . "cat` WHERE `idc` = " . $cid . " LIMIT 1");
			$typetitle = fa($typetitle);
			if(!$typetitle){
				$typetitle['namec'] = "Нераспределённые";
			}
			$breadcrumbs[] = array('url' => "/routlist/routpages/area_" . $this->params['area_id'] . '/type_' . $this->params['type_bort_id'] . '/category_' . $this->params['category_id'], 'name' => "Модификация: " . $typetitle['namec']);
		}
		
		$pages = q("SELECT `lists`.id as ln, id_sh, `lists`.date as ld, pni, `templates`.name, `templates`.uies	FROM `lists` INNER JOIN `templates` ON `templates`.id = `lists`.id_sh WHERE 1 $karea $ktype $kcat");
		while ($page = fa($pages)) {
			$data_arr['pages'][] = $page;
		}

		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_routpages");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/table.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}

	public function renderShabList() {
		$data_arr = array();
		$breadcrumbs[] = array('url' => "/routlist", 'name' => 'Маршрутные листы');
		$breadcrumbs[] = array('url' => "/routlist/shablist" ,'name' => "Список шаблонов");

		$data_arr['leftmenu'] = $this->genLeftMenu();

		if (is_int($this->params['type_bort_id'])) {
			$tbid = $this->params['type_bort_id'];
			$ktype = 'AND `t` = ' . $tbid;
			$typetitle = q("SELECT * FROM `" . $this->prefix . "tp` WHERE `idtp` = " . $tbid . " LIMIT 1");
			$typetitle = fa($typetitle);
			if(!$typetitle){
				$typetitle['nametp'] = "Нераспределённые";
			}
			$breadcrumbs[] = array('url' => "/routlist/shablist/type_" . $this->params['type_bort_id'], 'name' => "Тип борта: " . $typetitle['nametp']);
		}

		if (is_int($this->params['screen_id'])) {
			$data_arr['screen'] = $this->params['screen_id'];
		}

		if (is_int($this->params['category_id'])) {
			$cid = $this->params['category_id'];
			$kcat = 'AND `c` = ' . $cid;
			$typetitle = q("SELECT * FROM `" . $this->prefix . "cat` WHERE `idc` = " . $cid . " LIMIT 1");
			$typetitle = fa($typetitle);
			if(!$typetitle){
				$typetitle['namec'] = "Нераспределённые";
			}
			$breadcrumbs[] = array('url' => "/routlist/shablist/type_" . $this->params['type_bort_id'], 'name' => "Модификация: " . $typetitle['namec']);
		}

		$templates = q("SELECT * FROM `" . $this->prefix . "templates` WHERE 1 $ktype $kcat AND `name` != '' AND `title` != '' ORDER BY `title`");
		while ($template = fa($templates)) {
			$data_arr['templates'][] = $template;
		}

		$data_arr['breadcrumbs'] = $breadcrumbs;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->tpls['container'] = "shab/base/tpl/container/left-gray.tpl.php";
		$shab->setTpl('content', "render_shablist");
		$shab->setTpl('leftmenu', "leftmenu");
		$shab->setTpl('breadcrumb', "breadcrumb");
		$shab->addScript('/shab/modules/routlist/js/table.js?v=0.14');
		$shab->addScript('/shab/base/js/dataTables/jquery.dataTables.min.js?v=0.14', 'header');
		$shab->addScript('/shab/base/js/dataTables/paging.js?v=0.14', 'header');
		$shab->title = "Маршрутные листы.";
		$shab->renderTpl();
	}
}
