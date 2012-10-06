<?php
/**
 * Удалить
 * Отображает диалоговое окно для подтверждения удаления и осуществляет удаление (пермещение в корзину)
 * @version 1.0
 */
namespace Library\admin_widgets\Delete;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class Delete extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->required(),
                        'call' => Rule::string()->default('')->required(),
                    )
                )
            )
        );
    }


    public function work($v = array())
    {
        // Удаление
        if ($this->_input['REQUEST']['call'] == 'delete'){
            $this->_input['REQUEST']['object']['is_delete'] = true;
            if ($this->_input['REQUEST']['object']->save()){
                $v['result'] = true;
            }else{
                $v['result'] = false;
            }
            return $v;
        }
        // Отображение
        else{
            $v['title'] = $this->title->getValue();
            if (!$v['object']['title'] = $this->_input['REQUEST']['object']->title->getValue()){
                $v['object']['title'] = $this->_input['REQUEST']['object']->getName();
            }
            $v['object']['uri'] = $this->_input['REQUEST']['object']['uri'];
            return parent::work($v);
        }
    }
}
