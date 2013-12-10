<?php

/**
 * Модуль для загрузки/добавления/редактирования вакансий
 */
class Rabota {
	/* @var $shab Shab */

	public $params = array();

	function __construct() {
		$this->params = getUrlParams(false, false);		
	}
	public function router($redirect = true) {
		$massUrl = $this->arrUrl();
		if (array_key_exists('admin', $massUrl) AND admin_id > 0){
			switch ($massUrl['getparam']) {
				case 'save':
					if (isset($_POST)) {
						@$this->saveVipVacancy();
					}
					break;
				case 'add':
					@$this->renderVipVacancy();
					break;
				case 'edit':
					@$this->renderVipVacancy();
					break;
				default:
					redirect(explode("/admin", $_SERVER['REQUEST_URI'])['0']);
					break;
			}
		}

		if (isset($massUrl['vacancy'])) {
			@$this->renderVipVacancy();
		}

		if (isset($massUrl['obl'])) {
			if (isset($massUrl['city'])) {
				@$this->renderCity($massUrl['city']);
			} else {
				@$this->renderOblast($massUrl['obl']);
			}
		} elseif (isset($massUrl['city'])) {
			@$this->renderCity($massUrl['city']);
		}
		$this->renderHome();
	}

	private function setShabPar() {
		CM::$init->Shab->module = 'rabota';
		CM::$init->Shab->setParams(['set_head_menu' => 'doctorspage']);
		CM::$init->Shab->addCss('/shab/modules/' . CM::$init->Shab->module . '/css/' . CM::$init->Shab->module . '.css');
	}

	public function renderHome() {
		
		$data_arr = cache_get('rabota_oblast_list');
		
		if ($data_arr == false OR admin_id > 0) {
			$data_arr = array(); 
			$src = ob_file_get_contents('http://api.hh.ru/areas/113');
			$src_arr = CM::$init->Json->json2array($src);
			$data_arr['oblast_list'] = array();
			foreach ($src_arr['areas'] as $k => $val) {
				$data_arr['oblast_list'][$val['id']] = array('id' => $val['id'], 'name' => $val['name'], 'areas' => $val['areas']);
			}
			$data_arr['oblast_column'] = array_chunk($data_arr['oblast_list'], ceil(count($data_arr['oblast_list']) / 2), true);
			$data_arr['oblast_column'][1][] = array_shift($data_arr['oblast_column'][0]);
			cache_set('rabota_oblast_list', $data_arr, '1day');
		}
		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->setTpl('content', "render_home");
		$shab->title = "Каталог медицинских вакансий";
		$shab->renderTpl();
	}

	/**
	 * возвращает url в виде массива. например, obl_1946 будет как [obl]=>1946. [getparam] => всё, что после ?
	 * @return array 
	 */	
	public function arrUrl() {
		$uri = $_SERVER['REQUEST_URI'];
		$getmass = explode("?", $uri);
		if ($getmass['0']) {
			$uri = $getmass['0'];
		}
		$urimass = explode("/", $uri);
		for ($i=0; $i < count($urimass); $i++) { 
			$ch = explode("_", $urimass[$i]);
			$mass[$ch['0']] = $ch['1'];
		}
		$mass['getparam'] = $getmass['1'];
		$mass['uri'] = $uri;
		return $mass;
	}

	public function renderOblast($oblast_id) {
		$data_arr['citys_list'] = cache_get('rabota_oblast_citys_' . $oblast_id);
		if ($data_arr['citys_list'] == false OR admin_id > 0) {
			$src = ob_file_get_contents('http://api.hh.ru/areas/' . $oblast_id);
			$src_arr = CM::$init->Json->json2array($src);
			$data_arr['citys_list'] = $src_arr;
			if (empty($data_arr['citys_list']['areas'])) {
				redirect("/rabota/obl_$oblast_id/city_$oblast_id");
			}
			cache_set('rabota_oblast_citys_' . $oblast['parent_id'], $data_arr['citys_list'], '1day');
		}

		$arr[] = array('url' => '/rabota', 'name' => 'Вакансии');
		$arr[] = array('name' => $data_arr['citys_list']['name']);
		$data_arr['breadcrumbs'] = $arr;

		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->setTpl('content', "render_oblast");
		$shab->setTpl('breadcrumb_place', "breadcrumb");
		$shab->title = "Медицинские вакансии - " . $data_arr['citys_list']['name'];
		$shab->renderTpl();
	}

	/**
	 * Если передан id вакансии, то будет возвращена информация об этой вакансии. Иначе, будет возвращена
	 * информация обо всех вакансиях в данном регионе/городе
	 * @param int $obl_id id области/региона
	 * @param int $city_id id города
	 * @param int $id_vac id вакансии
	 * @return array массив, содержащий информацию о вакансии/вакансиях
	 */	
	public function getArrVip($obl_id = 0, $city_id = 0, $id_vac = 0) {
		$addz = "";
		if ($id_vac) {
			$idv = explode("?", $id_vac)['0'];
			$addz = "AND id='".$idv."'";
		}
		//даёт возможность редактирования закрытой вакансии (если известен её id)
		if (admin_id < 1 && !$id_vac) {
			$st = "AND status=1";
		}
		if ($city_id) {
			$qq =  "((`obl_id` = '".$obl_id."' AND `city_id` = '0') OR city_id='".$city_id."')";
		} else {
			$qq = "(`obl_id` = '".$obl_id."' AND `city_id` = '0')";
		}
		$query = q("Select * from `dr_job` where $qq $addz $st");
		while ($job = fa($query)){
			$temp_arr['id'] = $job['id'];
			$temp_arr['salary']['to'] = $job['salary_to'];
			$temp_arr['salary']['from'] = $job['salary_from'];
			$temp_arr['name'] = $job['name'];
			$temp_arr['area']['id'] = $job['city_id'];
			$temp_arr['area']['name'] = $job['area'];
			$temp_arr['employer']['name'] = $job['company_name'];
			if ($id_vac){
				$temp_arr['employer']['logo'] = $job['img_logo'];				
				$temp_arr['exp'] = $job['exp'];
				$temp_arr['shedule'] = $job['shedule'];
				$temp_arr['employer']['phone'] = $job['phone'];
				$temp_arr['employer']['mail'] = $job['mail'];
				$temp_arr['date_s'] = $job['date_start'];
				$temp_arr['date_e'] = $job['date_end'];
				$temp_arr['responsibility'] = $job['respon'];
				$temp_arr['requirements'] = $job['requir'];
				$temp_arr['terms'] = $job['terms'];
				$temp_arr['desc'] = $job['desc'];
			}
			$temp_arr['status'] = $job['status'];;
			$temp_arr['vip'] = 1;			
			$arrVip['vaclist'][] = $temp_arr;
		}
		$arrVip['allvv'] = nr($query);
		return $arrVip;
	}

	/**
	 * Возвращает форматированный список (<ul><li></li></ul>) для строк, которые начинаются с *
	 * @param string $text текст, который необходимо отформатировать
	 * @return array 
	 */
	public function getParseList($text) {
		$ul_1 = 1;
		$mass = explode("\n", $text);
		for ($i=0; $i < count($mass); $i++) {
			$tr = "";
			if ($mass[$i]{0}=="*") {
				$temp_row = trim(mb_substr($mass[$i], 1));
				if ($ul_1 == 1) {
					$tr = "<ul class='vacan'>";
					$ul_1 = 0;
				}
				$tr.="<li>". $temp_row . "</li>";
			} else {
				if ($ul_1 == 0) {
					$tr .= "</ul>";
					$ul_1 = 1;
				}
				$tr .= $mass[$i];
			}
			$result .= $tr;
		}
		if ($ul_1 == 0) {
			$result .= "</ul>";
		}
		return nl2br($result);
	}

	/**
	 * Сохранение добавленной или отредактированной вакансии
	 */
	public function saveVipVacancy(){
		$massUrl = $this->arrUrl();
		$c_id = $o_id = 0;
		$url = explode("admin/", $massUrl['uri'])[0];
		$id = $massUrl['vacancy'];
		if (!$id) {
			if ($massUrl['city']){
				$c_id = $massUrl['city'];
			}
			if ($massUrl['obl']){
				$o_id = $massUrl['obl'];
			}
			// даты добавлены для возможности (в будущем) вывода вакансии с ограничением по времени, а не только исходя из статуса
			q("Insert into `dr_job` (`obl_id`, `city_id`, `date_add`, `date_start`, `date_end`) values ('".$o_id."','".$c_id."', ".time().", ".time().", ".strtotime("+2 year").")");
			$id = mysql_insert_id();
		}
		$mass = array_slice($_POST, 0, -1);
		if ($_POST['status'] == 'on'){
			$mass['status'] = 1;
		}
		if (isset($_FILES['img_logo'])) {
			$uploaddir = FILEPATH . 'files2/uploads/vipvacancy/';
			// $small = '240/';
			$mini = '90/';
			$array = array('jpg',
			               'gif',
			               'png',
			               'jpeg');
			$type = strtolower(end(explode(".", $_FILES['img_logo']['name'])));
			$newname = "logo_emp_" . md5(time() . rand(0, 1000)) . "." . $type;
			$uploadfile = $uploaddir . $newname;
			
			if (in_array($type, $array)) {
				if (move_uploaded_file($_FILES['img_logo']['tmp_name'], $uploadfile)) {
					$image_size = getimagesize($uploadfile);
						// if ($image_size[0] >= 240 || $image_size[1] >= 240) {
						// 	$img = CM::$init->Img;
						// 	$img
						// 		->open($uploadfile)
						// 		->size(240, 240, $uploaddir . $small . $newname)								
						// 		->destroy();
						// } else {
						// 	copy($uploadfile, $uploaddir . $small . $newname);
						// }
					if ($image_size[0] >= 90 || $image_size[1] >= 90) {
						$img = CM::$init->Img;
						$img
							->open($uploadfile)
							->size(90, 90, $uploaddir . $mini . $newname)								
							->destroy();
					} else {
						copy($uploadfile, $uploaddir . $mini . $newname);
					}
					q("update `dr_job` set `img_logo`='".$newname."' where `id`='".$id."'");
				}
			}
		}
		sql_update($mass, 'dr_job', "`id`='".$id."'");
		redirect($url);
	}

	/**
	 * Генерация хлебной крошки
	 * @return array 
	 */
	public function getBreadCrumbs(){
		$massUrl = $this->arrUrl();
		if ($massUrl['obl']) {
			$o = "/obl_".$massUrl['obl'];
		}
		$arr['bc'][] = array('url' => '/rabota', 'name' => 'Вакансии');
		foreach ($massUrl as $key => $value) {
			if($value) {
				$temp = ob_file_get_contents("https://api.hh.ru/areas/" . $value);
				$temp_name = CM::$init->Json->json2array($temp)['name'];
				switch ($key) {
					case 'obl':
						$arr['bc'][] = array('url' => '/rabota/obl_'.$value, 'name' => $temp_name);
						$arr['n']['obl'] = $temp_name;
						break;
					case 'city':
						$arr['bc'][] = array('url' => '/rabota'.$o.'/city_'.$value, 'name' => $temp_name);
						$arr['n']['city'] = $temp_name;
						break;
				}				
			}
		}
		return $arr;
	}

	/**
	 * Рендер страницы с вип-вакансией. В зависимости от редактирования/добавления/просмотра подгружается определённый tpl
	 */
	public function renderVipVacancy() {
		$data_arr['massUrl'] = $this->arrUrl();
		if (isset($data_arr['massUrl']['vacancy'])) {
			$vacancy = $this->getArrVip($data_arr['massUrl']['obl'], $data_arr['massUrl']['city'], $data_arr['massUrl']['vacancy']);
				if ($vacancy['allvv'] && $data_arr['massUrl']['getparam'] != 'add'){
					$data_arr['vacancies']['items'] = $vacancy['vaclist'];
					if ($data_arr['massUrl']['getparam'] != 'edit'){
						$data_arr['vacancies']['items']['0']['responsibility'] = $this->getParseList($data_arr['vacancies']['items']['0']['responsibility']);
						$data_arr['vacancies']['items']['0']['requirements'] = $this->getParseList($data_arr['vacancies']['items']['0']['requirements']);
						$data_arr['vacancies']['items']['0']['terms'] = $this->getParseList($data_arr['vacancies']['items']['0']['terms']);
						$data_arr['vacancies']['items']['0']['desc'] = $this->getParseList($data_arr['vacancies']['items']['0']['desc']);
					}
				} else {
					redirect(explode("/vacan", $_SERVER['REQUEST_URI'])['0']);
				}
		} elseif ($data_arr['massUrl']['getparam'] != 'add') {
			redirect(explode("/admin", $_SERVER['REQUEST_URI'])['0']);
		}
		$param['url'] = $data_arr['massUrl']['uri'];
		$v_name = $data_arr['vacancies']['items']['0']['name'];
		$name_reg = $this->getBreadCrumbs()['n'];
		$arr = $this->getBreadCrumbs()['bc'];
		$arr[] = array('name' => $v_name);
		$data_arr['breadcrumbs'] = $arr;
		$data_arr+=$param;
		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		if (($data_arr['massUrl']['getparam'] == 'add' OR $data_arr['massUrl']['getparam'] == 'edit') AND admin_id > 0){
			$shab->setTpl('content', "render_city_vip_edit");
		} else {
			$shab->setTpl('content', "render_city_vip");
		}
		$shab->setTpl('breadcrumb_place', "breadcrumb");
		$shab->title = "Медицинские вакансии, " . $name_reg['city'] . (($data_arr['massUrl']['obl']) ? " (".$name_reg['obl'].")" : "") . ", ".$v_name;
		$shab->renderTpl();
	}

	public function renderCity($city_id) {		
		$param['url'] = "/{$this->params[0]}/{$this->params[1]}/{$this->params[2]}";		
		if (substr($this->params[1], 0, 3) != 'obl') {
			$param['url'] = "/{$this->params[0]}/{$this->params[1]}";
			$crutch = true;
		}
		if (($this->params[3] == 'page_0' OR $this->params[2] == 'page_0' OR $this->params[3] == 'page_1' OR $this->params[2] == 'page_1') OR (isset($this->params[4]) AND substr($this->params[4], 0, 6) != 'order_')) {
			redirect($param['url']);
		}
		$page = (substr($this->params[3], 0, 4) == 'page') ? end(explode("_", $this->params[3])) : ((substr($this->params[2], 0, 4) == 'page') ? end(explode("_", $this->params[2])) : 0);
		switch ($this->params[4]) {
			case "order_saldesc":
				$order = "salary_desc";
				break;
			case "order_salasc":
				$order = "salary_asc";
				break;
			case "order_date":
			default:
				$order = "publication_time";
		}
		$data_arr = cache_get('rabota:' . $_SERVER['REQUEST_URI']);
		
		if ($data_arr == false OR admin_id > 0) {
			
			$data_arr['massUrl'] = $this->arrUrl();	
			$src = ob_file_get_contents("https://api.hh.ru/vacancies?area=$city_id&specialization=13&vacancy_search_order=$order&page=" . $page . "&per_page=20");
			$src_arr = CM::$init->Json->json2array($src);
			$data_arr['vacancies'] = $src_arr;
			$vv = $this->getArrVip($data_arr['massUrl']['obl'],$data_arr['massUrl']['city']);
			if ($vv['allvv']) {
				$data_arr['vacancies']['items'] = array_merge($vv['vaclist'], $data_arr['vacancies']['items']);
			}
			$src = ob_file_get_contents("https://api.hh.ru/areas/$city_id");
			$data_arr['city'] = CM::$init->Json->json2array($src);
			cache_set('rabota:' . $_SERVER['REQUEST_URI'], $data_arr, '1day');
		}
		if ($page != 0 AND empty($data_arr['vacancies']['items'])) {
			redirect($param['url']);
		}
		$arr[] = array('url' => '/rabota', 'name' => 'Вакансии');
		if ($crutch != true) {
			$temp = ob_file_get_contents("https://api.hh.ru/areas/" . $data_arr['city']['parent_id']);
			$obl_name = CM::$init->Json->json2array($temp)['name'];
			$data_arr['obl_name'] = $obl_name;
			$arr[] = array('url' => "/{$this->params[0]}/{$this->params[1]}", 'name' => $obl_name);
		}
		$arr[] = array('name' => $data_arr['city']['name']);
		$data_arr['breadcrumbs'] = $arr;
		$data_arr+=$param;
		$this->setShabPar();
		$shab = CM::$init->Shab;
		$shab->setParams($data_arr);
		$shab->setTpl('content', "render_city");
		$shab->setTpl('breadcrumb_place', "breadcrumb");
		$shab->title = "Медицинские вакансии, " . $data_arr['city']['name'] . (($crutch != true) ? " ({$obl_name})" : "") . (($page > 1) ? " - страница $page" : "");
		$shab->renderTpl();
	}

}


