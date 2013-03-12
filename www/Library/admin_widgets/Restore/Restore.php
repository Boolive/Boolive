<?php
/**
 * Восстановить из удаленных
 * Отображает диалоговое окно для подтверждения восстановления и осуществляет восстановление
 * @version 1.0
 */
        namespace Library\admin_widgets\Restore;

        use Library\views\Widget\Widget,
            Boolive\values\Rule;

class Restore extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity(array('attr','is_delete','=',1))),
                            Rule::entity(array('attr','is_delete','=',1))
                        )->required(),
                        'call' => Rule::string()->default('')->required(),
                    )
                )
            )
        );
    }

    public function work($v = array())
        {
            // Восстановление
            if ($this->_input['REQUEST']['call'] == 'restore'){
                $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
                foreach ($objects as $o){
                    /** @var \Boolive\data\Entity $o */
                    $o->isDelete(false);
                    $o->save();
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
                    $v['question'] = 'Вы действительно желаете восстановить эти объекты?';
                    $v['message'] = 'Восстановленные объекты могут изменить работу системы';
                }else{
                    $v['question'] = 'Вы действительно желаете восстановить этот объект?';
                    $v['message'] = 'Восстановленный объект может изменить работу системы';
                }
                $v['prev'] = '';
                return parent::work($v);
            }
        }

}
?>