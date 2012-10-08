<?php
/**
 * Изображение
 *
 * @version 1.0
 */
namespace Library\content_samples\Image;

use Boolive\data\Entity,
    Boolive\values\Rule;

class Image extends Entity
{
    public function defineRule()
    {
        parent::defineRule();
        // Ассоциация с файлами с расширением css
        $this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.png', '*.jpg', '*.gif')->required();
    }
}
