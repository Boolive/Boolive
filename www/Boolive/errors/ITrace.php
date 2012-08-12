<?php
/**
 * Файл, содержащий интерфейс ITrace
 *
 * @author Azat Galiev <AzatGaliev@live.ru>
 * @version 2.0
 */

namespace Boolive\errors;

/**
 * Интерфейс получения от объекта значений для трассировки (вывода)
 */
interface ITrace{
    /**
     * @abstract
     * @return array
     */
    public function trace();
}
