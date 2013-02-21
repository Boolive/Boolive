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
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required(),
                        //'prev' => Rule::entity(),
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
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            foreach ($objects as $o){
                /** @var \Boolive\data\Entity $o */
//                $o->isDelete(true);
//                $o->save();
            }
            $v['result'] = true;
            return $v;
        }
        // Отображение
        else{
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            $v['data-o'] = array();
            $v['objects'] = array();
            foreach ($objects as $o){
                $item = array();
                if (!($item['title'] = $o->title->value())){
                    $item['title'] = $o->name();
                }
                $item['uri'] = $o->uri();
                $v['objects'][] = $item;
                $v['data-o'][]=$item['uri'];
            }
            $v['data-o'] = json_encode($v['data-o']);
            $v['title'] = $this->title->value();
            if (count($objects)>1){
                $v['message'] = 'Объекты будут перемещены в корзину, их можно будет восстановить.';
            }else{
                $v['message'] = 'Объект будет перемещён в корзину, его можно будет восстановить.';
            }
            $v['prev'] = '';//$this->_input['REQUEST']['prev']? $this->_input['REQUEST']['prev']->uri() : '';
            return parent::work($v);
        }
    }
}
