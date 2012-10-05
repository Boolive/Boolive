<?php
/**
 * Виджет навигации по страницам ("Следующая", "Предыдущая")
 *
 * @version 1.0
 * @author Azat Galiev <AzatXaker@gmail.com>
 */

namespace Library\content_widgets\NextPrevPage;

use Library\views\Widget\Widget,
    Boolive\values\Rule,
    Boolive\values\Check,
    Boolive\errors\Error;

class NextPrevPage extends Widget
{
	protected function initInput($input)
    {
        $this->_input = Check::filter($input, $this->getInputRule(), $this->_input_error);
        if (!$this->_input['REQUEST']['object']->is('/Library/content_samples/Page')) {
            // Объект не является страницей
            if (!isset($this->_input_error)) {
                $this->_input_error = new Error('Объект не является страницей', 'page');
            }
        }
    }

    public function work($v = array())
    {
        $object = $this->_input['REQUEST']['object'];
        /*$next = array();
        $prev = array();

        $sub_page = $object->find(array(
            'where' => '`proto` = \'/Library/content_samples/Page\'',
            'count' => 1,
            'order' => '`order` ASC'
        ));

        if (count($sub_page) != 0) {
            $next = $sub_page[0];
        }

        if ($object->parent()->is('/Library/content_samples/Page')) {
            $first = $object->parent()->find(array(
                'where' => '`proto` = \'/Library/content_samples/Page\'',
                'count' => 1,
                'order' => '`order` ASC',
            ));

            if (count($first) != 0 && $first[0]['order'] == $object['order']) {
                $prev = $object->parent();
            }
        }*/

        //if (!isset($next)) {
            $next = $object->parent()->find(array(
                'where' => '`order` > ' . $object['order'] . ' and `proto` = \'/Library/content_samples/Page\'',
                'count' => 1,
                'order' => '`order` ASC',
            ));
        //}
        $prev = $object->parent()->find(array(
            'where' => '`order` < ' . $object['order'] . ' and `proto` = \'/Library/content_samples/Page\'',
            'count' => 1,
            'order' => '`order` DESC',
        ));

        if (!(count($next) == 0 && count($prev) == 0)) {
            if (count($next) == 0) {
                $v['next'] = null;
                $v['prev'] = $prev[0];

                if (substr($v['prev']['uri'], 0, 10) == '/Contents/') {
                    $v['prev_href'] = substr($v['prev']['uri'], 10);
                } else {
                    $v['prev_href'] = $v['prev']['uri'];
                }
            }
            if (count($prev) == 0) {
                $v['next'] = $next[0];
                $v['prev'] = null;

                if (substr($v['next']['uri'], 0, 10) == '/Contents/') {
                    $v['next_href'] = substr($v['next']['uri'], 10);
                } else {
                    $v['next_href'] = $v['next']['uri'];
                }
            }
            if (count($prev) != 0 && count($next) != 0) {
                $v['next'] = $next[0];
                $v['prev'] = $prev[0];

                if (substr($v['next']['uri'], 0, 10) == '/Contents/') {
                    $v['next_href'] = substr($v['next']['uri'], 10);
                } else {
                    $v['next_href'] = $v['next']['uri'];
                }
                if (substr($v['prev']['uri'], 0, 10) == '/Contents/') {
                    $v['prev_href'] = substr($v['prev']['uri'], 10);
                } else {
                    $v['prev_href'] = $v['prev']['uri'];
                }
            }

            return parent::work($v);
        }
    }
}
