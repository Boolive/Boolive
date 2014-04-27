<?php
/**
 * Действие с прогрессом выполнения
 * Действие над объектами с подтверждением и прогрессом выполнения.
 * @version 1.0
 */
namespace site\library\admin\widgets\FindUpdates;

use boolive\data\Data;
use site\library\admin\widgets\ProgressAction\ProgressAction,
    boolive\session\Session;

class FindUpdates extends ProgressAction
{
    /**
     * Подготовка к прогрессу
     * @param $objects
     * @return array
     */
    protected function progressStart($objects)
    {
        $info = array(
            'id' =>  uniqid('findUpdates'),
            'jobs' => array(),
            'jobs_step' => 0,
            'jobs_count' => count($objects)
        );
        foreach($objects as $obj){
            $info['jobs'][] = $obj->key();
        }
        Session::set($info['id'], $info);
        return array('progress_id' => $info['id']);
    }
    /**
     * Выполнение шага экпортирования
     * @param string $id Идентификатор задачи экспортирования
     * @return array
     */
    protected function progress($id)
    {
        // Если есть сессия, задачи и текущая
        if (($info = Session::get($id)) && !empty($info['jobs']) && $info['jobs_step'] < $info['jobs_count']){
            $obj = Data::read($info['jobs'][$info['jobs_step']]);
            Data::findUpdates($obj, 50, 1, true);
            $info['jobs_step']++;
            $info['progress'] = $info['jobs_step'] / $info['jobs_count'] * 100;
            Session::set($info['id'], $info);
            return $info;
        }else{
            return array(
                'complete' => true,
                'progress' => 100,
                'message' => 'Completed'
            );
        }
    }
}