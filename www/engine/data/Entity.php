<?php
/**
 * Сущность
 * Базовая логика для объектов модели данных.
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use ArrayObject,
	Engine\Values,
	Engine\Error,
	Engine\Data,
	Engine\Trace,
	Engine\File,
	Exception;

class Entity extends Values implements ITrace{
	/** @var array Названия атрибутов */
	static private $atribs = array('uri', 'vers', 'lang', 'owner', 'value', 'order', 'is_file',	'is_delete', 'is_hidden', 'date', 'level', 'proto', 'logic', 'file');
	/** @var \Engine\Section Экземпляр секции, которой реализуется сохранение и выбор себя (атрибутов) */
	protected $_section_self;
	/** @var \Engine\Section Экземпляр секции, которой реализуется сохранение и выбор подчиенных (свойств) */
	protected $_section_children;
	/** @var \Engine\Entity Экземпляр прототипа */
	protected $_proto = false;
	/** @var \Engine\Entity Экземпляр родителя */
	protected $_parent = false;
	/** @var bool Принзнак, объект в процессе сохранения? */
	protected $_is_saved = false;
	/** @var bool Признак, виртуальный объект или нет. Если объект не сохранен в секции, то он виртуальный */
	protected $_virtual = true;
	/** @var string uri родителя */
	protected $_parent_uri = null;
	/** @var string Имя объекта, определенное по uri */
	protected $_name = null;
	/**
	 * Признак, требуется ли подобрать уникальное имя перед сохранением или нет?
	 * Также означает, что текущее имя (uri) объекта временное
	 * Если строка, то определяет базовое имя, к кторому будут побираться числа для уникальности
	 * @var bool|string */
	public $_rename = false;

	public function __construct($attribs = array(), $section = null){
		parent::__construct($attribs);
		$this->_section_self = $section;
	}

	/**
	 * Правило на атрибуты
	 */
	public function defineRule(){
		//@todo По умолчанию все элементы сделать типом Entity
		//@todo Информацию о файле хранить в value, для этого позволить опрелелять варианты правил
		//@todo Добавить тип правила "Файл", чтобы определять допустимые расширения и прочие условия на файл
		$this->_rule = Rule::ArrayList(array(
			// Правила на атрибуты
			'uri'		 => Rule::String()->max(255)->required(),
			'vers'		 => Rule::Int(),
			'lang'		 => Rule::Int(),
			'owner'		 => Rule::Int(),
			'value'	 	 => Rule::Any(
								Rule::Null(),
								Rule::BigText(),
								Rule::ArrayList(array(
									'tmp_name'	=> Rule::String()->more(0)->required(),
									'name'		=> Rule::String()->default('')->patterns('*.txt', '*.doc'),
									'error'		=> Rule::Any()->in(0)
								))
							),
			'order'		 => Rule::Any(Rule::Int(), Rule::Null()),
			'is_file'	 => Rule::Bool(),
			'is_delete'	 => Rule::Bool(),
			'is_hidden'	 => Rule::Bool(),
			'date'		 => Rule::Int(),
			'level'		 => Rule::Int(),
			'proto'		 => Rule::Any(Rule::String()->max(255), Rule::Null()),
			'logic'		 => Rule::Any(Rule::String()->max(255), Rule::Null())
			),
			// Правило на все остальные элементы
			Rule::Object('\Engine\Entity')
		);


			// Информация о загруженном файле
//			'file' 		 => Rule::ArrayList(array(
//				'tmp_name'	=> Rule::String()->more(0),
//				'name'		=> Rule::String()->can_empty()->set_default(''),
//				'error'		=> Rule::Int(8)->set_default(0)
//			))->exist(false)
	}

	/**
	 * Проверка, принадлежит ли имя атрибуту объекта?
	 * Если нет, то это имя подчиненного объекта
	 * @param string $name Проверяемое имя
	 * @return bool
	 */
	public function isAttrib($name){
		return in_array($name, self::$atribs);
	}

	/**
	 * Cекции объекта
	 * Секцией реализуется сохранение и выборка атрибутов объекта
	 * @return \Engine\Section|null
	 */
	public function sectionSelf(){
		if (empty($this->_section_self) && $parent = $this->parent()){
			$this->_section_self = $parent->sectionChildren();
		}
		return $this->_section_self;
	}

	/**
	 * Секция подчиненных объектов
	 * Секцией реализуется сохранение и выборка подчиненных объектов
	 * Секция подчиненных может совпадать с секцией самого объекта
	 * @return \Engine\Section
	 */
	public function sectionChildren(){
		if (empty($this->_section_children)){
			// Назначена ли секция для подчиненных?
			$this->_section_children = Data::Section($this['uri']);
			if (empty($this->_section_children)){
				// Используем свою секцию
				$this->_section_children = $this->sectionSelf();
			}
		}
		return $this->_section_children;
	}

	/**
	 * Установка значений атриубту или присваивание подчиенного объекта
	 * Если $name не является именем атрибута, то устанавливается подчиненный объект. При этом, если $value не
	 * является объектом, то выполняется установка этого значения атриубту 'value' подчиненному объекту с именем $name.
	 * Если такого подчиенного ещё нет, то будет создан новый.
	 * Если $value является объектом, но в котором уже опредлен uri отличяющийся от требуемого, то произойдет
	 * прототипирование $value.
	 * Если подчиенный с именем $name уже есть, он будет заменен устанавливаемым
	 * @example $object[$name] = $value;
	 * @param string $name Имя атрибута или подчиенного объекта
	 * @param mixed $value Значение или объект
	 */
	public function offsetSet($name, $value){
		if ($name == 'proto'){
			$this->_proto = null;
		}else
		if ($name == 'uri'){
			// обновление uri текущих подчиненных
			if ($this->offsetExists($name)){
				// Удаление себя из текущего родителя, так как родитель поменяется
				if ($this->_parent instanceof Entity){
					$this->_parent->offsetUnset($this->getName());
					$this->_parent = null;
				}
				$this->updateChildNames($value);
			}
			$this->_section_children = null;
			$this->_section_self = null;
			$this->_name = null;
			$this->_parent_uri = null;
		}
		// Установка подчиненных
		if (!$this->isAttrib($name)){
			if ($value instanceof Entity){
				// Если имя неопредленно, то нужно будет подобрать уникальное автоматически
				// Перед сохранением используется временное имя
				if (is_null($name)){
					$name = uniqid('rename');
					$rename = 'entity';
				}
				// Если у объект есть uri и он отличается от необходимого, то прототипируем объект
				if (isset($value['uri']) && $value['uri']!= $this['uri'].'/'.$name){
					// В качестве базового имени - имя прототипа.
					if (isset($rename)) $rename = $value->getName();
					$value = $value->birth();
				}
				// Установка uri для объекта, если есть свой uri
				if (isset($this['uri'])) $value['uri'] = $this['uri'].'/'.$name;
				if (isset($rename)) $value->_rename = $rename;
				$value->_parent = $this;
			}else{
				// Установка значения для подчиненного
				$this->__get($name)->offsetSet('value', $value);
				return;
			}
		}
		parent::offsetSet($name, $value);
	}

	/**
	 * Каскадное обновление URI подчиненных на основании своего uri
	 * Обновляются uri только выгруженных/присоединенных на данный момент подчиенных
	 * @param $uri Свой новый URI
	 */
	protected function updateChildNames($uri){
		parent::offsetSet('uri', $uri);
		foreach ((array)$this as $child_name => $child){
			if ($child instanceof Entity){
				$child->updateChildNames($uri.'/'.$child_name);
			}
		}
	}

	/**
	 * Получение атрибута или подчиненного объекта по имени
	 * Если объекта нет, то он НЕ выбирается из секции, а возвращается null
	 * @example $sub = $obj['sub'];
	 * @param mixed $name
	 * @return mixed
	 */
	public function offsetGet($name){
		if ($this->isAttrib($name)){
			$value = parent::offsetGet($name);
			if ($name == 'value' && $this['is_file'] && mb_substr($value, mb_strlen($value)-6) == '.value'){
				try{
					$this['value'] = $value = file_get_contents(DIR_SERVER_PROJECT.$this['uri'].'/'.$value);
				}catch(Exception $e){
					$this['value'] = $value = '';
				}
				$this['is_file'] = false;
			}
			return parent::offsetGet($name);
		}else{
			return parent::get($name, Rule::No());
		}
	}

	/**
	 * Получение атрибута или подчиненного объекта по имени
	 * Если объекта нет, то будет выгружен из секции или создан виртуальный
	 * @example $sub = $obj->sub;
	 * @param string $name
	 * @return array|Values|void
	 */
	public function __get($name){
		if (isset($name) && ($this->offsetExists($name)||$this->isAttrib($name))){
			// Если установлен или является атрибутом
			return parent::__get($name);
		}else{
			// Если имя неопредленно, то нужно будет подобрать уникальное автоматически
			// Перед сохранением используется временное имя
			if (is_null($name)){
				$name = uniqid('rename');
				$rename = 'entity';
			}
			$uri = $this['uri'].'/'.$name;
			// Если объекта нет в секции, то создается виртуальный
			if (!($s = $this->sectionChildren())||!($obj = $s->read($uri))){
				// Поиск прототипа для объекта
				// Прототип тоже может оказаться виртуальным!
				if ($proto = $this->proto()){
					$proto = $proto->{$name};
				}
				if ($proto){
					$class = get_class($proto);
					$obj = new $class(array('uri'=>$uri, 'proto'=>$proto['uri']));
				}else{
					$obj = new Entity(array('uri'=>$uri));
				}
			}
			$this->offsetSet($name, $obj);
			if (isset($rename)) $obj->_rename = $rename;
			return $obj;
		}
	}

	/**
	 * Замена всех элементов (атриубто и подчиенных объектов) новыми
	 * Если новые объекты имею uri не соответсвующий их новому родителю (this), то объекты прототипируются
	 * @param mixed $list Массив новых атрибутов и объектов
	 * @return array Массив текущих атрибутов и объектов
	 */
	public function exchangeArray($list){
		foreach ($list as $name => $item){
			if ($item instanceof Entity){
				if (isset($item['uri']) && $this['uri']!= $item->getParentUri()){
					$list[$name] = $item->birth();
				}
				// Установка uri для объекта, если есть свой uri
				if (isset($this['uri'])) $list[$name]['uri'] = $this['uri'].'/'.$name;
				$list[$name]->_parent = $this;
			}
		}
		// Сброс состояния и расчитанных значений
		$this->_section_children = null;
		$this->_section_self = null;
		$this->_parent = null;
		$this->_proto = null;
		$this->_name = null;
		$this->_parent_uri = null;
		$this->_rename = false;
		$this->_virtual = true;
		$this->_changed = false;
		$this->_filtered = false;
		return parent::exchangeArray($list);
	}

	/**
	 * При обращении к объекту как к скалярному значению (строке), возвращается значение атриубeта value
	 * @example
	 * print $object;
	 * $value = (string)$obgect;
	 * @return mixed
	 */
	public function __toString(){
		$value = $this->offsetGet('value');
		if (is_null($value) && ($proto = $this->proto())){
			$value = $proto->__toString();
		}
		return (string)$value;
	}

	/**
	 * Вызов несуществующего метода
	 * Если объект внешний, то вызов проиведет модуль секции объекта
	 * @param string $method
	 * @param array $args
	 * @return null|void
	 */
	public function __call($method, $args){
		if ($s = $this->sectionSelf()){
			$s->call($method, $args);
		}
	}

	/**
	 * URI родителя
	 * @return string|null Если родителя нет, то null. Пустая строка является корректным uri
	 */
	public function getParentUri(){
		if (!isset($this->_parent_uri)){
			if (isset($this['uri'])){
				$names = F::SplitRight('/',$this['uri']);
				$this->_parent_uri = $names[0];
				$this->_name = $names[1];
			}else
			if (isset($this->_parent)){
				$this->_parent_uri = $this->_parent['uri'];
			}else{
				$this->_parent_uri = null;
			}
		}
		return $this->_parent_uri;
	}

	/**
	 * Имя объекта
	 * @return string|null
	 */
	public function getName(){
		if (!isset($this->_parent_uri)){
			if (isset($this['uri'])){
				$names = F::SplitRight('/',$this['uri']);
				$this->_parent_uri = $names[0];
				$this->_name = $names[1];
			}
		}
		return $this->_name;
	}

	/**
	 * Родитель
	 * @return \Engine\Entity|null
	 */
	public function parent(){
		if ($this->_parent === false){
			$this->_parent = Data::Object($this->getParentUri());
		}
		return $this->_parent;
	}

	/**
	 * Прототип
	 * @return \Engine\Entity|null
	 */
	public function proto(){
		if ($this->_proto === false){
			if (isset($this['proto'])){
				$this->_proto = Data::Object($this['proto']);
			}else{
				$this->_proto = null;
			}
		}
		return $this->_proto;
	}

	public function checkArray($rule = null, &$errors = null){
		// @todo Инициализация некоторых атрибутов: parent, proto, date...
		// @todo Проверка файла?
		// @todo onRemoveChild() onChangeChild()
	}

	public function save($save_sub = true){
		// @todo Проверка с выбрасыванием исключений
		// @todo Сохранение родителя, прототипа и себя
		// @todo Обновление атрибутов после сохранения
		// @todo Сохранение подчиненных
		// @todo Смена признака виртуальности _virtual

	}

	/**
	 * Удаление объекта
	 * Объект помечается как удаленный
	 */
	public function delete(){
		// @todo Установка атрибута is_delete и сохранение в секции без проверок и прочих сохранений
		$this['is_delete'] = true;
	}

	/**
	 * Поиск подчиенных объектов
	 * @param array $cond Услвоие поиска
	 * $cond = array(
	 *        'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
	 *        'values' => array(), // Массив значений для вставки в условие вместо "?"
	 *        'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
	 *        'count' => 0, // Количество выбираемых объектов
	 *        'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
	 *    );
	 * @return array
	 */
	public function find($cond = array()){
		if ($s = $this->sectionChildren()){
			if (!empty($cond['where'])){
				$cond['where'].=' AND uri like ? AND level=?';
			}else{
				$cond['where'] = 'uri like ? AND level=?';
			}
			$cond['values'][] = $this['uri'].'/%';
			$cond['values'][] = $this['level']+1;
			return $s->select($cond);
		}
		return array();
	}

	/**
	 * Поиск подчиненных объектов с учетом унаследованных
	 * @todo Сортировка между своими и унаследованными подчиненными по любым атрибутам (сделано только по order)
	 * @todo Ограничение количества выборки..
	 * @param array $cond Услвоие поиска
	 * $cond = array(
	 *        'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
	 *        'values' => array(), // Массив значений для вставки в условие вместо "?"
	 *        'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
	 *        'count' => 0, // Количество выбираемых объектов
	 *        'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
	 *    );
	 * @return array
	 */
	public function findAll($cond = array()){
		$results = array();
        $list = array();
        $object = $this;
        $names = array();
		$proto = array();
		$prototype = false;
        do{
            // Поиск у себя и у всех наследуемых по цепочке объектов
			$sub = $object->find($cond);
			foreach ($sub as $child){
				$name = $child->getName();
				// Если не удален, не скрыт, существует, ещё не выбран с таким же именем и нет среди прототипов
				if (!isset($names[$name]) && !isset($proto[$child['uri']])){
                    if ($prototype){
						// Объект виртуальный для $this, поэтому прототипируем его
						$child = $child->birth();
						$child['uri'] = $this['uri'].'/'.$name;
					}
					// Добавляем с учетом порядкового номера
					$list[(int)$child['order']][] = $child;
                }
                $names[$name] = true;
				$proto[$child['proto']] = true;
			}
            $object = $object->proto();
			$prototype = true;
        }while($object);
		// Сортировка групп
		if (!empty($cond['order']) && preg_match('/`order`\s*(ASC|DESC)?/iu', $cond['order'], $math)){
			if (empty($math[1]) || strtolower($math[1])!='desc'){
				ksort($list, SORT_NUMERIC);
			}else{
				krsort($list, SORT_NUMERIC);
			}
		}
		// Слияние групп в один массив
        foreach ($list as $objects){
            for ($i=sizeof($objects)-1; $i>=0; --$i){
                $results[$objects[$i]->getName()] = $objects[$i];
            }
        }
        return $results;
	}

	/**
	 * Создание нового объекта прототипированием от себя
	 * @return \Engine\Entity
	 */
	public function birth(){
		$class = get_class($this);
		return new $class(array('proto'=>$this['uri']));
	}

	/**
     * Действия при изменении подчиенного в момент сохранения
     * Проверка подчиенного.
     * @param \Engine\Entity $child
     */
	protected function onChangeChild(Entity $child){

	}

    /**
     * Действия при удалении подчиненного. (Изменяется родитель у подчиенного на другой)
     * Проверка подчиенного.
     * @param \Engine\Entity $child
     */
	protected function onRemoveChild(Entity $child){

	}


	/**
	 * Признак, находится ли объект в процессе сохранения?
	 * @return bool
	 */
	public function isSaved(){
		return $this->_is_saved;
	}

	/**
	 * Сравнение объектов по uri
	 * @param \Engine\Entity $entity
	 * @return bool
	 */
	public function isEqual($entity){
		if (!$entity) return false;
		return ($this->offsetExists('uri') && $this->offsetGet('uri') == $entity->offsetGet('uri'));
	}

	/**
	 * Признак, виртуальный объект или нет. Если объект не сохранен в секции, то он виртуальный
	 * @return bool
	 */
	public function virtual(){
		return $this->_virtual;
	}


//	/**
//	 * Выбор подчиненного по его относительному пути
//	 * @example $child = $obj->getSubByPath('sub/sub2/child'); Равноценно: $child = $obj->sub->sub2->child;
//	 * @param string $path Относительный путь на объект
//	 * @param bool $exist Признак, возвращать только сущесвтующий объект. Если false и подчиненного нет, то будет возвращен новый объект
//	 * @return Entity|null
//	 */
//	public function getSubByPath($path, $exist = false){
//		if (($path = trim($path, '/ '))){
//			$path = explode('/', $path);
//			$cnt = sizeof($path);
//			$i = 0;
//			$obj = $this;
//			while ($i < $cnt && $obj){
//				$obj = $obj->{$path[$i]};
//				if ($obj && $exist && $obj->isNew()) return null;
//				$i++;
//			}
//			return $obj;
//		}
//		return null;
//	}
//
//	/**
//	 * Проверка объекта
//	 * Проверяются атрибуты, подчиненные объекты и информируется родитель об изменении или удалении его подчиненного.
//	 * Если родитель при проверке вызовет исключение, то этот объект будет считаться с ошибкой.
//	 * Проверяются только подгруженные подчиненные, а не все имеющеся у объекта в БД.
//	 * @param bool $exchange_attr Признак, заменить ли атрибуты экземпляра на отфильтрованные после проверки?
//	 * @return bool False, если есть ошибки в атрибутах
//	 */
//	public function check($exchange_attr = true){
//		// @todo
//		$attr = (array)$this;
//		// Ссылка на родителя
//		if (($parent = $this->parent())&&!$parent->isNew()) $attr['parent'] = $parent['section'].':'.$parent['id'];
//		// Ссылка на прототип
//		if (($proto = $this->proto())&&!$proto->isNew()) $attr['proto'] = $proto['section'].':'.$proto['id'];
//		// Секция по умолчанию
//		if (empty($attr['section']) && $parent)	$attr['section'] = $parent['section'];
//
//		if ($this->isNew()){
//			// Имя объекта по умолчанию
//			if (empty($attr['name'])) $attr['name'] = 'entity';
//			// Даты создания, если не установлена вручную и объект новый
//			if (empty($attr['date_create'])) $attr['date_create'] = time();
//		}
//		// Даты изменения, если не установлена вручную
//		if (empty($attr['date_edit'])) $attr['date_edit'] = time();
//
//		// Атрибуты помещем в контейнер для фильтра
//		$attr = new Values($attr);
//		if (!($this->_errors instanceof Error)) $this->_errors = new Error(__CLASS__);
//		$this->_verified = $attr->getArray($this->_rule, $this->_errors);
//
//		// Проверка файла
//		if (isset($this->_verified['file'])){
////			$this['file'] = $attr['file']->getArray($this->_rule_file, $this->_errors);
//			// Ошибка загрузки файла
//			if ($this->_verified['file']['error'] !== 0){
//				$this->_errors->add(File::UploadErrorMmessage($this->_verified['file']['error']));
//			}else
//			if (!$this->_errors->isExist()){
//				// Проверка типа файла
//				if (!$this->isAssociated($this->_verified['file']['tmp_name'])){
//					$this->_errors->add(array('Неподдерживаемый тип файла (%s)', $this->_verified['file']['tmp_name']));
//				}
//			}
//		}
//		// Замена текущих атрибутов на отфильтрованные
//		if ($exchange_attr) $this->updateArray($this->_verified);
//
//		// Есть ли ошибки в атрибутах?
//		$no_errors = !$this->_errors->isExist();
//
//		// Проверка подчиненных
//		foreach ($this->_children as $sub){
//			/** @var $sub \Engine\Entity */
//			$no_errors = $sub->check() && $no_errors;
//		}
//		try{
//			// Проверка объекта родителем
//			if (!$this->isNew()){
//				// Если родитель изменен, то информирование прошлого об удалении его подчиненноо
//				$current = Data::Get($this['section'], $this['id'], false, false);
//				$curr_parent = $current->parent();
//				if ($curr_parent){
//					if ($this['parent']!=$current['parent'] || (!$this->offsetIsEmpty('is_delete') && !$current['id_delete'])){
//						$curr_parent->onRemoveChild($this);
//					}
//				}
//			}
//			// Информирование родителя об изменении его подчиенного
//			if ($parent = $this->parent()){
//				$parent->onChangeChild($this);
//			}
//		}catch(Error $e){
//			$this->_errors->add($e);
//			$no_errors = false;
//		}
//		return $no_errors;
//	}
//
//	/**
//	 * Сохранение объекта
//	 * @param bool $save_sub Сохранять ли подчиенных?
//	 * @throws Error|null
//	 * @throws \Exception
//	 * @return void
//	 */
//	public function save($save_sub = true){
//		if (!$this->_is_on_save && $this->_can_save){
////			try{
//				$this->_is_on_save = true;
//				// Старт транзакции
////				Transaction::Begin();
//				// Сохранение прототипа, если новый
//				if (($proto = $this->proto()) && $proto->isNew()){
//					$proto->save();
//				}else
//				if (!$proto){
//					// @todo Определение прототипа от наследуемого класса
//				}
//				// Сохранение родителя, если новый
//				if (($parent = $this->parent()) && $parent->isNew()){
//					$parent->save();
//				}
//
//				// Проверка объекта
//				if (!$this->check()) throw $this->_errors;
//
//				// Сохранение, если объект новый
//				if ($this->isNew()){
//					$this->updateArray(Data::Add($this));
//				}else{
//					$this->updateArray(Data::Edit($this));
//				}
//				if ($save_sub){
//					// Сохранение подчиненных
//					foreach ($this->_sub as $sub){
//						/** @var $sub \Engine\Entity */
//						$sub->save();
//					}
//				}
//				$this->_is_on_save = false;
//				// Завершение тразакции
////				Transaction::Commit();
////			}catch(Exception $e){
////				Transaction::RollBack();
////				$this->_is_on_save = false;
////				throw $e;
////			}
//		}
//	}
//	/**
//	 * Проверка, является ли объект новым (не сохраненным в БД)
//	 * @return bool
//	 */
//	public function isNew(){
//		return $this->offsetIsEmpty('id');
//	}
//
//	/**
//	 * Признак, можно ли объект сохранять
//	 * @param bool|null $can_save Если будево значение, то выполняется установка признака в соответсвующее значение
//	 * @return bool
//	 */
//	public function canSave($can_save = null){
//		if (is_bool($can_save)){
//			$this->_can_save = $can_save;
//			if ($can_save && ($parent = $this->parent())){
//				$parent->canSave(true);
//			}
//		}
//		return $this->_can_save;
//	}
//

//
//	/**
//	 * Проверка, является ли объект подчиенным для указанного?
//	 * @param \Engine\Entity $parent
//	 * @return bool
//	 * @todo
//	 */
//	public function isChildOf($parent){
//		if (!$parent) return false;
//		if ($this['section'] == $parent['section']){
//			$key = '_id'.$parent['_level'];
//			if (isset($this[$key]) && $this[$key] == $parent['id']){
//				return true;
//			}
//		}
//		return false;
//	}
//
//	/**
//	 * Проверка, является ли объект родителем для указанного?
//	 * @param \Engine\Entity child
//	 * @return bool
//	 */
//	public function isParentOf($child){
//		if (!$child) return false;
//		return $child->isChildOf($this);
//	}
//
//	/**
//	 * Проверка, наследуется ли объект от указанного (является ли налседником)?
//	 * @param $proto
//	 * @return bool
//	 */
//	public function isHeirOf($proto){
//		if (!$proto) return false;
//		if ($this->isEqual($proto)) return true;
//		$cur = $this;
//		while ($cur){
//			if (($cur = $cur->proto()) == $proto) return true;
//		}
//		return false;
//	}
//
//	/**
//	 * Проверка, является ли объект прототипом для указанного?
//	 * @param \Engine\Entity $heir
//	 * @return mixed
//	 */
//	public function isProtoOf($heir){
//		if (!$heir) return false;
//		return $heir->isHeirOf($this);
//	}
//
//	/**
//	 * Запуск сущности
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих сущностях
//	 * @param bool $previous Признак, были ли объекты до текущего. Если false, то не были.
//	 * @param \Engine\Input $input Входящие данные
//	 * @return void|mixed Результат выполнения контроллера
//	 */
//	public function start($commands, $previous, $input){
//		$result = '';
//		//Проверка возможности работы
//		if ($this->canWork($commands, $previous, $input)){
//			$this->_start_args = array(
//				'commands' => $commands,
//				'prviews' => false,
//				'input' => $input->getShifted($this->_params_shift)
//			);
//			//Выполнение подчиненных
//			//$sub = $this->subStart($commands, $input);
//			ob_start();
//				// Выполненеие своей работы
//				$result = $this->work($commands, array(), $input);
//				$result = ob_get_contents().$result;
//			ob_end_clean();
//		}
//		return $result;
//	}
//
//	/**
//	 * Проверка возможности работы
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих объектах
//	 * @param bool $previous Признак, были ли объекты до текущего. Если false, то не были.
//	 * @param \Engine\Input $input Входящие данные
//	 * @param bool $check_input Признак, работать если нет ошибок во входящих данных.
//	 * @return bool Признак, может ли работать объект или нет
//	 */
//	public function canWork($commands, $previous, $input, $check_input = true){
//		$result = true;
//		if ($input instanceof Values){
//			$this->loadAllSub();
//			// Условие на URL
//			$pattern = (string)$this->url_pattern;
//			if ($pattern){
//				// Сверка с текущим URL и выборка необходимых данных из него
//				$result = $input->match($pattern, $this->_params_shift);
//			}
//			// Проверка входящих данных
//			$this->_input_filtered = $input->getArray($this->_input_rule, $this->_input_errors);
//			if ($check_input && $this->_input_errors->isExist()){
//				return false;
//			}
//		}
//		return $result;
//	}
//
//	/**
//	 * Запуск подчиненного по имени
//	 * @param $path Имя подчиненного или путь на него
//	 * @return null
//	 */
//	public function startSub($path){
//		if ($sub = $this->getSubByPath($path)){
//			$result = $sub->start($this->_start_args['commands'], $this->_start_args['prviews'], clone $this->_start_args['input']);
//			if ($result){
//				$this->_start_args['prviews'] = true;
//				return $result;
//			}
//		}
//		return null;
//	}
//
//	/**
//	 * Работа подчиненных объектов
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих объектах
//	 * @param \Engine\Input $input Входящие данные
//	 * @return array Результаты подчиненных объектов. Ключи массива - названия объектов.
//	 */
//	public function subStart($commands, $input){
//        $results = array();
//		$list = array();
//		$object = $this;
//		$names = array();
//		do{
//			$sub = $object->loadAllSub();
//			$cnt = sizeof($sub);
//			for ($i=0; $i<$cnt; ++$i){
//				if (!in_array($sub[$i]['name'], $this->_ignore_start) &&
//					!$sub[$i]['is_hidden'] && !$sub[$i]['is_delete'] && !$sub[$i]['is_temp'] &&
//					!$sub[$i]->isNew() && !isset($names[$sub[$i]['name']])){
//					$list[(int)$sub[$i]['order']][] = $sub[$i];
//				}
//				$names[$sub[$i]['name']] = true;
//			}
//			$object = $object->proto();
//		}while($object);
//
//		ksort($list, SORT_NUMERIC);
//		foreach ($list as $key => $objects){
//			// В обратном порядке, чтоб сначала исполнялись подчиенные прототипов, а уже потом свои
//			for ($i=sizeof($objects)-1; $i>=0; --$i){
//				$out = $objects[$i]->start($commands, !empty($results), $input->getShifted($this->_params_shift));
//				if ($out){
//					$results[$objects[$i]['name']] = $out;
//				}
//			}
//		}
//		return $results;
//	}
//
//	/**
//	 * Работа объекта. Обработка запроса и формирование вывода
//	 * Результат выводится функциями echo, print или другими. Или возвращается через return
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих объектах
//	 * @param array $v Результаты подчиненных объектов
//	 * @param \Engine\Input $input Входящие данные
//	 * @return string|void Результат работы. Вместо return можно использовать вывод строк (echo, print,...)
//	 */
//	public function work($commands, $v = array(), $input){
//		return $this['value'].implode('', $v);
//	}
//
//	/**
//	 * Путь на объект от корня сайта
//	 * Используется для определения пути на файл объекта для его чтения или создания
//	 * @param string $file_name
//	 * @param bool $find_existing
//	 * @return bool|string
//	 */
//	public function getPath($file_name = '', $find_existing = true){
//		if (!$find_existing && $this->_dir) return $this->_dir.$file_name;
//		$object = $this;
//		do{
//			if ($parent = $object->parent()){
//				$root_dir = $parent->getPath('', false);
//			}else{
//				$root_dir = $object['section'].'/';
//			}
//
////			$parent = $object;
////			$path = '';
////			while ($parent){
////				$path = $parent['name'].'/'.$path;
////				$parent = $parent->parent();
////			}
////			$path = $root_dir.$path.$file_name;
//			$path = $root_dir.$object['name'].'/'.$file_name;
//			if ($find_existing && !file_exists(DIR_PROJECT.$path)){
//				$path = false;
//				$object = $object->proto();
////				if (get_class($object) == 'Engine\\Entity'){
////					$object = null;
////				}
//			}else{
//				$object = null;
//			}
//		}while($object);
//		if ($path){
//			return $path;
//		}
//		return false;
//	}
//
//	/**
//	 * Проверка возможности ассоциировать файл с объектом
//	 * @param $file Путь на файл
//	 * @return bool
//	 */
//	public function isAssociated($file){
//		// Если указаны возможные расширения файла
//		if (!empty($this->_valid_extensions)){
//			$info = File::FileInfo($file);
//			return in_array($info['ext'], $this->_valid_extensions);
//		}
//		return true;
//	}
//
//	/**
//	 * Установка класса
//	 * Инициализация объекта, который будет ассоциирован с данным классом
//	 * @param \Engine\Values $input Данные, полученные от пользователя, если он запрашивались методом self::InstallPrepare()
//	 * @return void
//	 */
//	public function install($input){
//		$keys = $input->getKeys();
//		foreach ($keys as $key){
//			$this->{$key} = $input->get($key);
//		}
//	}
//
	/**
	 * Клонирование объекта
	 */
	public function __clone(){
		foreach ((array)$this as $key => $item){
			if (is_object($item)){
				parent::offsetSet($key, clone $item);
			}
		}
	}
	/**
	 * Значения внутренных свойств объекта для трасировки при отладки
	 * @return array
	 */
	public function trace(){
		//$trace['hash'] = spl_object_hash($this);
		$trace = (array)$this;
		//$trace['_is_saved'] = $this->_is_saved;
		$trace['_changed'] = $this->_changed;
		$trace['_filtered'] = $this->_filtered;
		$trace['_virtual'] = $this->_virtual;
		/*if ($this->_rename)	*/$trace['_rename'] = $this->_rename;

		//$trace['_proto'] = $this->_proto;
		//$trace['_parent'] = $this->_parent;
		return $trace;
	}
}
