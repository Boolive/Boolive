<?php
/**
 * Секция в MySQL
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine\Sections;

use Engine\DB,
	Engine\Section,
	Engine\Data;

class MySQLSection extends Section{
	/** @var \Engine\DB */
	private $db;
	private $table;

	public function __construct($config){
		parent::__construct($config);
		if (isset($config['connect'])){
			$this->db = DB::Connect($config['connect']);
		}
		if (isset($config['table'])){
			$this->table = $config['table'];
		}
		if (empty($this->db) || empty($this->table)){
			throw new \Engine\Error('MySQLSection: Incorrect configuration');
		}
	}

	/**
	 * Выбор объекта по его uri
	 * @param $uri
	 * @return \Engine\Entity|null
	 * @todo Учесть языка и владельца
	 */
	public function read($uri){
		$q = $this->db->prepare('SELECT * FROM `'.$this->table.'` WHERE uri=? AND vers=0 LIMIT 0,1');
		$q->execute(array($uri));
		if ($row = $q->fetch(DB::FETCH_ASSOC)){
			$obj = Data::MakeObject($row);
			$obj->_filtered = true;
			$obj->_virtual = false;
			return $obj;
		}
		return null;
	}

	/**
	 * Обновление объекта или добавление, если объект не существует
	 * Идентификация объекта выполняется по uri
	 * @param $entity
	 * @return bool|void
	 */
	public function put($entity){
//		echo $this->table;
	}

	/**
	 * Выбор объектов по условию
	 * @param array $cond Услвоие поиска
	 * $cond = array(
	 *        'where' => '', // Условие как в SQL на колонки таблицы. Условие на подчиненные объекты
	 *        'values' => array(), // Массив значений для вставки в условие вместо "?"
	 *        'order' => '', // Способ сортировки. Задается как в SQL, например: `name` DESC, `value` ASC
	 *        'count' => 0, // Количество выбираемых объектов (строк)
	 *        'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
	 *    );
	 * @return array|void
	 */
	public function select($cond){
		// Услвоие, сортировка и ограничение количества
		$cond = array_replace(array('where' => '', 'values' => array(), 'order' => '', 'count' => 0, 'start' => 0), $cond);
		$filter = '';
		if ($cond['where']) $filter.= ' WHERE '.$cond['where'];
		if ($cond['order']) $filter.= ' ORDER BY '.$cond['order'];
		if ($cond['count']||$cond['start']) $filter.= ' LIMIT '.intval($cond['start']).($cond['count']?','.intval($cond['count']):'');
		// Подготовка и исполнение запроса
		$q = $this->db->prepare("SELECT * FROM {$this->table} {$filter}");
		$cnt = sizeof($cond['values']);
		for ($i = 0; $i < $cnt; $i++){
			$q->bindValue($i+1, $cond['values'][$i]);
		}
		$q->execute();
		// Создание экземпляров
		$result = array();
		while ($attribs = $q->fetch(DB::FETCH_ASSOC)){
			$obj = Data::MakeObject($attribs);
			$obj->_filtered = true;
			$obj->_virtual = false;
			$result[] = $obj;
		}
		return $result;
	}

	public function install(){
		$this->db->exec('
			CREATE TABLE `'.$this->table.'` (
			  `vers` INT(11) NOT NULL COMMENT "Номер версии в обратном порядке",
			  `lang` INT(11) NOT NULL DEFAULT "0" COMMENT "Язык",
			  `owner` INT(11) NOT NULL DEFAULT "0" COMMENT "Владелец",
			  `order` INT(11) NOT NULL DEFAULT "1" COMMENT "Порядковый номер",
			  `is_file` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Является ли объект файлом",
			  `is_delete` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Признак, удален объект или нет",
			  `is_hidden` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Признак, скрытый объект или нет",
			  `date` INT(11) NOT NULL COMMENT "Дата создания объекта",
			  `level` INT(11) NOT NULL DEFAULT "1" COMMENT "Уровень вложенности относительно корня",
			  `uri` VARCHAR(255) NOT NULL DEFAULT "" COMMENT "Унифицированный идентификатор (путь на объект)",
			  `value` VARCHAR(255) DEFAULT NULL COMMENT "Значение",
			  `proto` VARCHAR(255) DEFAULT NULL COMMENT "uri прототипа",
			  `logic` VARCHAR(255) NOT NULL DEFAULT "" COMMENT "Имя класса",
			  PRIMARY KEY  (`uri`,`vers`,`lang`,`owner`,`level`),
			  KEY `orders` (`order`)
			) ENGINE=INNODB DEFAULT CHARSET=utf8'
		);
	}
}
