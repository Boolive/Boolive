<?php
/**
 * Установщик системы
 *
 * @version 1.0
 * @date 28.03.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\installer;

use Boolive\Boolive;
use Boolive\data\Entity;
use Boolive\errors\Error;
use Boolive\file\File;
use Boolive\functions\F;
use Boolive\session\Session;

class Installer
{
    static function start()
    {
        if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'){
            // Подготовка к установки системы
            $errors = array();
            try{
                Boolive::activate('\Boolive\functions\F');
                Boolive::activate('\Boolive\file\File');
                Boolive::activate('\Boolive\develop\Trace');
                $modules_list = array(
                    'engine' => self::sortClasses(self::scanClasses(DIR_SERVER_ENGINE)),
                    'project' => self::scanInfo(DIR_SERVER_PROJECT)
                );
                $errors = self::checkClasses($modules_list['engine']);
            }catch (\Exception $e){
                $errors[] = $e->getMessage();
            }
            if ($errors){
                echo self::show('requirements', array('errors'=> $errors));
            }else{
//                trace($modules_list['project']);
                Session::activate();
                Session::set('install', array(
                    'modules' => array_merge($modules_list['engine']['sorted'], $modules_list['project']),
                    'step' => 0
                ));
                echo self::show('install');
            }
        }else{
            Boolive::init();
            // Шаг установки
            $result = self::installStep();
            if ($result['complete']){
                self::installComplete();
            }
            echo json_encode($result);
        }
    }

    /**
	 * Шаблонизация страниц установки
	 * @param string $template Имя шаблона. По имени определяется файл шаблона из Boolive/installer/tpl
	 * @param array $v Значаения, передаваемые в шаблон
	 * @return string Результат шаблонизации
	 */
	private static function show($template, $v = array())
    {
		ob_start();
			$file = DIR_SERVER_ENGINE.'installer/tpl/'.$template.'.php';
			if (file_exists($file)){
				include $file;
			}else{
				echo "TEMPLATE FILE NOT FOUND: $file";
			}
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

    /**
     * Поиск классов в указанной директории
     * @param string $dir Директория поиска
     * @return array Массив с информацией о найденных классах ($modules)
     */
    static function scanClasses($dir = '')
    {
        $dirs = array_diff(scandir($dir), array('.', '..'));
        $modules = array();
		foreach ($dirs as $d){
            if (is_dir($dir.$d)){
                $modules = array_merge($modules, self::scanClasses($dir.$d.'/', $modules));
            }else{
                if ($file_name = pathinfo($d, PATHINFO_FILENAME)){
                    // Если php файл распознан как класс
                    if (substr($d, strlen($d)-4)=='.php' && ($lines = self::parsePhpFile($dir.$d))){
                        // Парсинг php файла.
                        array_unshift($modules, array_merge($lines));
                    }
                }
            }
        }
        return $modules;
    }

    /**
     * Поиск файлов .info с информацией об объектах в указанной директории
     * @param string $dir Директория поиска
     * @return array Массив с информацией о найденных объектах
     * @throws \Exception
     */
    static function scanInfo($dir)
    {
        $dirs = array_diff(scandir($dir), array('.', '..'));
        $objects = array();
		foreach ($dirs as $d){
            if (is_dir($dir.$d)){
                $objects = array_merge($objects, self::scanInfo($dir.$d.'/'));
            }else{
                if (substr($d, strlen($d)-5)=='.info'){
                    // Все сведения об объекте в формате json (если нет класса объекта)
                    $f = file_get_contents($dir.$d);
                    $info = json_decode($f, true);
                    if ($error = json_last_error()){
                        throw new \Exception('Ошибка в "'.$dir.$d.'"');
                    }
                    $info['uri'] = trim(preg_replace('#\\\\#u','/', mb_substr($dir, mb_strlen(DIR_SERVER_PROJECT))), '/\\');
                    if (!empty($info['uri'])) $info['uri'] = '/'.$info['uri'];
                    array_unshift($objects, $info);
                }
            }
        }
        return $objects;
    }

	/**
	 * Парсер PHP файла с описанием класса
	 * @param $file
	 * @return array Информация о классе
	 */
	private static function parsePhpFile($file)
    {
		if ($f = @fopen($file, 'r')){
			$index = 'label';
			$state = 0;
			$bad_comment_names = array('namespace', 'name', 'extends', 'use');
			$lines = array(
				'namespace'=>'',
				'name'=>'',
				'extends'=>'',
				'use'=>array(),
				'label'=>'',
				'description'=>'',
			);
			$i = 0;
			$curr = '';
			$comment_cnt = 0;
			while(!is_bool($state) && ($line=fgets($f))){
				$line = trim($line);
				if ($line){
					if ($i==0){
						$state = ($line=='<?php')?1:false; // файл начинается с <?php
					}else
					if ($i==1){
						$state = ($line=='/**')?2:false; // вторая строка начало phpDoc класса
					}else{
						$match = array();
						if ($state==2){ // коммент
							if ($line=='*/'){ // конец комента
								$state = 3;
							}else
							if ($comment_cnt==0){ //тело первого коммента
								// Обработка строки коммента
								if (preg_match('/^\*(\s@([a-zA_Z0-9_-]+)?)?(\s.*)?/u', $line, $match)){
									$index = empty($match[2])?$index:$match[2];
									if (isset($match[3])&&!in_array($index, $bad_comment_names)){
										// Первая строка - название модуля
										// Последующие - описание модуля
										if ($index=='label'&&!empty($lines[$index])){
											$index = 'description';
										}
										// Строки с параметром @ - дополнительные описания модуля
										if (empty($lines[$index])){
											$lines[$index] = '';
										}else{
											$lines[$index].= ' ';
										}
										$lines[$index].= trim($match[3]);
									}
								}
							}
							$curr = 'comments';
						}else
						if ($line=='/**'){
							$state = 2;
							$comment_cnt++;
						}else
						if ($state==3){
							// Вырезание комментариев
							$line = preg_replace('#(^\s*\/\/[\s\S]*$)?((\/\*)[\s\S]*(\*\/))?#u','',$line);
							$line = trim($line);
							if ($line){
								// Пространство имён класса
								if (preg_match('/^\s*namespace\s+([\\\\a-zA-Z0-9_]+)\s*[;{]?$/u',$line, $match)){
									$lines['namespace'] = $match[1];
									$curr = 'namespace';
								}else
								// Используемые пространства имен
								if (preg_match('/^\s*use\s+([\\\\a-zA-Z0-9,\s_]+)(;|,)/u',$line, $match)){
									// вырезаем алиасы и разрезаем на массив
									$uses = preg_replace('#\s*as\s+[\\\\a-zA-Z0-9_]+\s*#u','', explode(',',rtrim($match[1],', ')));
									foreach ($uses as $use){
										$use = trim($use, ' \\');
										if (!empty($use)){
											$lines['use'][] = $use;
										}
									}
									$curr = $match[2] == ',' ? 'use' : false;
								}else
								// Название класса и название наследуемого класса
								if (preg_match('/^\s*(class|abstract class|interface|trait|final class)\s+([a-zA-Z0-9_]+)\s*(extends\s*([\\\\a-zA-Z0-9_]+))?/u',$line, $match)){
									$lines['kind'] = $match[1];
									if ($lines['kind'] == 'final class'){
										$lines['kind'] = 'class';
									}else
									if ($lines['kind'] == 'abstract class'){
										$lines['kind'] = 'abstract';
									}
									$lines['name'] = $match[2];
									$lines['extends'] = isset($match[4])?$match[4]:'';
									if ($lines['extends']){
										// Определение полного названия с пространством имен
										$find = false;
										$u = count($lines['use'])-1;
										while ($u>=0){
											if (preg_match('#'.preg_quote('\\'.$lines['extends']).'$#u', '\\'.$lines['use'][$u])){
												$lines['extends'] = $lines['use'][$u];
												$u = 0;
												$find = true;
											}
											$u--;
										}
										if (!$find) $lines['extends'] = $lines['namespace'].'\\'.$lines['extends'];
									}
									$curr = 'class';
									// Обработка файла завершена
									$state = true;
								}else
								// Используемые пространства имен записанные на новой строке после use
								if ($curr == 'use' && preg_match('#^\s*[\\\\a-zA-Z0-9,\s_]+(;|,)#u',$line, $match)){
									// вырезаем алиасы и разрезаем на массив
									//$uses = preg_replace('/\s*\/\/[\s\S]*$/u','', $uses);
									$uses = preg_replace('#\s*as\s+[\\\\a-zA-Z0-9_]+\s*#u','', explode(',',rtrim($match[0],',; ')));
									foreach ($uses as $use){
										$use = trim($use, ' \\');
										if (!empty($use)){
											$lines['use'][] = $use;
										}
									}
									$curr = $match[1] == ',' ? 'use' : false;
								}else
                                if (preg_match('#^\s*[{}]\s*$#u',$line, $match)){

                                }else{
									// Если не сработало ни одно условие, то файл не содержит класс
									$state = false;
								}
							}
						}
					}
					$i++;
				}
			}
			if ($state){
				if (!empty($lines['use'])){
					//удаление пробелов
					array_walk($lines['use'], function(&$n){$n = trim($n);});
				}
				$lines['file'] = $file;
				$lines['fullname'] = $lines['namespace'].'\\'.$lines['name'];
				return $lines;
			}
		}
		return array();
	}

    /**
     * Сортировка классов в соответсвии с их отношениями (наследование, использование)
     * @param array $classes Массив с информацией о классах
     * @return array Многомерный массив со списком отсортированных классов, не отсортированных и отсутсвующих классов
     */
    static function sortClasses($classes)
    {
        $result = array(
			'sorted' => array(), // Отсортированные классы в соответсвии с их использованием
			'not_sorted' => array(), // Неотсортированные классы из-за отсутствия необходимого или взаимного использования
            'missing' => array() // Отсутсвующие классы
        );
        $last_missing = array();
        $is_change = true;
        while($is_change && $classes){
            $is_change = false;
            // Информация о недостояющих классах сбрасыватеся при каждом проходе
            $result['missing'] = array();

            // Список модулей для повторного прохода
            $next_classes = array();
            foreach ($classes as $class){
                $can_work = true;
                $cnt_j = count($class['use']);
                $j = 0;
                while ($can_work && $j<$cnt_j){
                    if (!((isset($result['sorted'][$class['use'][$j]]) || class_exists($class['use'][$j], false) || interface_exists($class['use'][$j], false)))){
                        if (!isset($last_missing[$class['fullname']][$class['use'][$j]])){
                            $can_work = false;
                            $result['missing'][$class['use'][$j]][$class['fullname']] = true;
                        }
                    }
                    $j++;
                }
                if ($can_work){
                    $result['sorted'][$class['fullname']] = $class;
                    if (isset($result['not_sorted'][$class['fullname']])) unset($result['not_sorted'][$class['fullname']]);
                    $is_change = true;
                }else{
                    $result['not_sorted'][$class['fullname']] = true;
                    $next_classes[] = $class;
                }
            }
            $last_missing = $result['missing'];
            $classes = $next_classes;
        }
        $result['sorted'] = array_values($result['sorted']);
        return $result;
    }

    /**
	 * Проверка системных требованеий классов
	 * @param array $classes Список классов с информацией о них, полученной методом self::sortClasses()
	 * @return array Массив сообщений с требованиями
	 */
	static function checkClasses($classes)
    {
		$errors = array();
		// Недостающие модули
		if ($classes['missing']){
			$many = count($classes['missing'])>1;
			$e = 'В установочном пакете Boolive отсутству'.($many?'ю':'е').'т класс'.($many?'ы':'').': <ul>';
			foreach ($classes['missing'] as $class => $for){
				$e.= '<li><strong>'.$class.'</strong> <small>(требуется для '.implode(', ',array_keys($for)).')</small></li>';
			}
			$errors[] = $e.'</ul>';
		}else
		if ($classes['not_sorted']){
			$e = 'В установочном пакете не определен порядок установки между классами: <ul>';
			foreach ($classes['not_sorted'] as $for){
				$e.= '<li>'.$for.'</li>';
			}
			$errors[] = $e.'</ul>';
		}
		// Проверка системных модулей
		$list = $classes['sorted'];
        foreach ($list as $info){
            include_once $info['file'];
        }
		foreach ($list as $info){
            $name = $info['fullname'];
            if (method_exists($name, 'systemRequirements')){
                $result = call_user_func(array($name, 'systemRequirements'));
                if (!empty($result) && $result!==true){
                    if (is_scalar($result)) $result = array($result);
                    if (is_array($result)) $errors = array_merge($errors, $result);
                }
            }
		}
		return $errors;
	}

    /**
     * Шаг установки
     * За один шаг устаналвивается один класс ядра или создаётся объект проекта из файлов
     * @return array Состояние процесса установки
     */
    static function installStep()
    {
        $install = Session::get('install');
        $result = array(
            'complete' => false,
            'percent' => 0,
            'step_cnt' => count($install['modules'])
        );
        $steps_at_a_time = 3;
        while (0 < $steps_at_a_time-- && $install['step'] < $result['step_cnt']){
            // Устанока класса/объекта
            if (!empty($install['modules'][$install['step']])){
                $m = $install['modules'][$install['step']];
                if (!empty($m['kind']) && $m['kind'] == 'class'){
                    // Устанока класса ядра
                    $class_name = $m['fullname'];
                    // Подготовка к установке класса. Класс может запрашивать ввод данных
                    if (method_exists($class_name, 'installPrepare')){
                        $info = call_user_func(array($class_name, 'installPrepare'));
                    }else{
                        $info = false;
                    }
                    // Обработка запрошенных данных
                    if (\Boolive\input\Input::REQUEST()->install_request->string() == 'submit' || !$info){
                        if (method_exists($class_name, 'install')){
                            try{
                                call_user_func(array($class_name, 'install'), \Boolive\input\Input::ALL());
                                $install['step']++;
                            }catch(\Boolive\errors\Error $e){
                                if ($info){
                                    $input = \Boolive\input\Input::getSource();
                                    $result['html'] = self::MakeForm($info, $input['REQUEST'], $e);
                                }else{
                                    $result['error'] = '<b>Ошибка при установке модуля "'.$m['path'].'"</b><pre>'.\Boolive\develop\Trace::format($e).'</pre>';//->getUserMessage(true);
                                }
                            }
                        }else{
                            $install['step']++;
                        }
                    }else
                    if (is_array($info)){
                        // Формируем форму
                        $result['html'] = self::makeForm($info);
                    }else{
                        $result['html'] = 'Ошибка в модуле "'.$class_name.'"::installPrepare()';
                    }
                }else
                if (empty($m['kind'])){
                    // Устанока объекта проекта
                    $entity = new Entity();
                    $entity->import($m);
                    $entity->save(false, true, false);
                    $install['step']++;
                }else{
                    $install['step']++;
                }
            }else{
                $result['step_cnt'] = 0;
            }
        }
        // Состояние процесса установки
        if ($result['step_cnt'] == 0 || $install['step'] >= $result['step_cnt']){
            $result['percent'] = 100;
            $result['complete'] = true;
        }else{
            $result['percent'] = round($install['step'] / $result['step_cnt'] * 100);
        }
        Session::set('install', $install);
        return $result;
    }

    /**
	 * Создание HTML формы
	 * @param array $info Информация о полях формы
	 * @param array $values Значения для полей формы
	 * @param Error $e Ошибки заполнения формы
	 * @return string HTML
	 */
	static function makeForm($info, $values = null, $e = null){
		// Установка значений
		if ($values){
			foreach ($values as $name => $val){
				// Вводимое значение в поле
				if (isset($info['fields'][$name])){
					$info['fields'][$name]['value'] = $val;
				}
			}
		}
		// Обработка ошибки
		if ($e){
			$errors = $e->getAll();
			foreach ($errors as $name => $error){
				/** @var Error $error */
                if (isset($info['fields'][$name])){
					$info['fields'][$name]['error'] = $error->getUserMessage(true);
					$e->delete($name);
				}
			}
			// Все оставшиеся ошибки
			$info['error'] = $e->getUserMessage(true);
		}
		// Поля формы
		if (isset($info['fields'])){
			foreach ($info['fields'] as $name => $f){
				if (!isset($f['label'])) $f['label'] = '';
				if (!isset($f['style'])) $f['style'] = '';
				if (!isset($f['value'])){
					$f['value'] = '';
				}else{
					$f['value'] = htmlentities($f['value'], ENT_COMPAT, 'UTF-8');
				}
				if (empty($f['input'])) $f['input'] = 'text';
				$f['name'] = $name;
				$info['fields'][] = self::show('form/input-'.$f['input'], $f);
				unset($info['fields'][$name]);
			}
		}
		// Форма
		return self::show('form/form', $info);
	}

    /**
	 * Завершение установки
	 */
	private static function installComplete(){
		$file = DIR_SERVER.'config.php';
		if (is_writable($file)){
			$content = file_get_contents($file);
			$content = preg_replace('/["\']IS_INSTALL[\'"],[^)]+/u', "'IS_INSTALL', true", $content);
			$fp = fopen($file, 'w');
			fwrite($fp, $content);
			fclose($fp);
		}
		Session::remove('install');
	}
}