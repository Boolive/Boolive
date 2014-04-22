<?php
/**
 * Robokassa
 * Интерфейс online платежей через robokassa.ru
 * @version 1.0
 */
namespace Site\library\views\Robakassa;

use Boolive\values\Rule;
use Site\library\views\View\View;

class Robakassa extends View
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'result' => Rule::string()->in('result', 'success', 'fail')->default('result')->required(),
                'OutSum' => Rule::double()->required(),
                'InvId' => Rule::int()->required(),
                'SignatureValue' => Rule::string()->lowercase()->default('')->required(), // Отсутствует при отказе от оплаты (fail)
                'Culture' => Rule::string()->default('ru')->required()
            )),
            'previous' => Rule::not(true)
        ));
    }

    function work()
    {
        switch ($this->_input['REQUEST']['result']){
            case 'result': return $this->result($this->_commands, $this->_input);
                break;
            case 'success': return $this->success($this->_commands, $this->_input);
                break;
            case 'fail': return $this->fail($this->_commands, $this->_input);
                break;
        }
        return false;
    }

    function result($commands, $input)
    {
        // Ответ робокассе о зачислении оплаты
        return "bad sign\n";
        return "OK\n";
    }

    function success($commands, $input)
    {
        // Редирект на страницу успешного результата оплаты
        return 'OK';
    }

    function fail($commands, $input)
    {
        // Редирект на отмены оплаты
        return 'FAIL';
    }

    /**
     * Параметры оплаты
     * Для формирования ссылки или пользовательской формы
     * @return array
     */
    function getParams($params = array('OutSum' => 0, 'InvId' => 1), $secondary_params = array())
    {
        $params = array_replace(array(
            'MrchLogin' => $this->login->inner()->value(),
            'OutSum' => 0, // сумма заказа
            'InvId' => 1, // номер заказа
            'SignatureValue' => '',
            'Desc' => 'Оплата заказа', // описание платежа
            //'IncCurrLabel' => "Яндекс.Деньги", // предлагаемая валюта платежа
            'Culture' => 'ru',	// язык
        ), $params, $secondary_params);
        // формирование подписи
        $crc = "{$params['MrchLogin']}:{$params['OutSum']}:{$params['InvId']}:{$this->pass1->inner()->value()}";
        ksort($secondary_params);
        foreach ($secondary_params as $key => $value){
            $crc.=':'.$key.'='.$value;
        }
        $params['SignatureValue'] = md5($crc);
        $url = $this->url->inner()->value().'?';
        foreach ($params as $key => $value){
            $url.=$key.'='.$value.'&';
        }
        $params['url'] = rtrim($url,'&');
        return $params;
    }
}