<?php
/**
 * Корневой объеъект сайта
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
use Engine\Entity,
	Engine\Rule;

class site extends Entity
{
	/**
	 * Исполнение объекта
	 * Запуск подчиенного - интерфейс
	 * @return null|string|void
	 */
	public function work()
	{
		return $this->startChild('interfaces');
	}
}