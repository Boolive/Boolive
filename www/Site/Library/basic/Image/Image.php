<?php
/**
 * Изображение
 * С функциями трансформации
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @todo При смене значения/файла удалять сохраненные трансформации изображения.
 */
namespace Library\basic\Image;

use Boolive\develop\Trace,
    Boolive\functions\F,
    Library\basic\File\File;

class Image extends File
{
    /** @const Тип масштабирования. */
    const FIT_INSIDE = 1; // Ширина или высота входят в указанную область, но могут быть меньше её.
    const FIT_OUTSIDE_LEFT_TOP = 2; // Ширина или высота может выходить за указанную область, но будут отсечены
    const FIT_OUTSIDE_LEFT_BOTTOM = 4;
    const FIT_OUTSIDE_RIGHT_TOP = 8;
    const FIT_OUTSIDE_RIGHT_BOTTOM = 16;
    const FIT_OUTSIDE_CENTER = 32;
    const FIT_FILL = 64; // Непропорциональное масштабирование - полное соответсвие указанной области

    /** @const Направление масштабирования*/
    const SCALE_ANY = 0; // Уменьшать или увеличивать автоматически
    const SCALE_DOWN = 1; // Только уменьшать
    const SCALE_UP = 2; // Только увеличивать

    /** @const Способы размазывания */
    const BLUR_SELECTIVE = 1;
    const BLUR_GAUSSIAN = 2;

    /** @const Направления отражения */
    const FLIP_X = 1;
    const FLIP_Y = 2;

    /** @var null Ресурс изображения для функций GD */
    private $_handler = null;
    /** @var array Информация о текущем ресурсе. Размеры, расширение */
    private $_info = array();
    /** @var array Массив заданных трансформаций. Выполняются в момент запроса результата трансфрмации */
    private $_transforms = array();
    /** @var array Заданные трансформации в строковом формате */
    private $_transforms_str = '';
    /** @var string Расширение, в котором сохранить */
    private $_convert;

    function __destruct()
    {
        $this->reset();
    }

    /**
     * Изменение размера
     * @param int $width Требуемая ширена изображения
     * @param int $height Требуемая высота изображения
     * @param int $fit Тип масштабирования. Указывается константами Image::FIT_*
     * @param int $scale Направление масштабирования. Указывается константами Image::SCALE_*
     * @param bool $do Признак, выполнять трансформацию (true) или отложить до результата (пути на файл)
     * @return $this
     */
    function resize($width, $height, $fit = Image::FIT_OUTSIDE_LEFT_TOP, $scale = Image::SCALE_ANY, $do = false)
    {
        $width = max(0, min($width, 1500));
        $height = max(0, min($height, 1500));
        $fit = intval($fit);
        $scale = intval($scale);
        if (!$do){
            $this->_transforms[] = array('resize', array($width, $height, $fit, $scale, true));
            $this->_transforms_str.= 'resize('.$width.'x'.$height.'x'.$fit.'x'.$scale.')';
        }else{
            if ($handler = $this->handler()){
                // Выполение масштабирования
                $src = array('x' => 0, 'y' => 0, 'w' => $this->width(), 'h' => $this->height());
                $new = array('x' => 0, 'y' => 0, 'w' => $width, 'h' => $height);
                //
                $do_scale = false;
                $dw = $src['w'] - $new['w'];
                $dh = $src['h'] - $new['h'];
                // Коррекция масштабирования
                $can_scale = function($d) use($scale){
                    // Только увеличивать
                    if ($scale == Image::SCALE_UP){
                        return min($d, 0);
                    }else
                    // Только уменьшать
                    if ($scale == Image::SCALE_DOWN){
                        return max($d, 0);
                    }
                    return $d;
                };
                if ($new['w'] != 0 && $new['h'] != 0 || $new['h']!=$new['w']){
                    // Автоматически ширена или высота
                    if (($new['w'] == 0 || $new['h'] == 0) && ($do_scale = $can_scale($dw))){
                        $ratio = $src['w'] / $src['h'];
                        if ($new['w'] == 0){
                            $new['w'] = round($new['h'] * $ratio);
                        }else{
                            $new['h'] = round($new['w'] / $ratio);
                        }
                    }else
                    // Максимальное изменение
                    if ($fit === self::FIT_INSIDE){
                        $ratio = $src['w'] / $src['h'];
                        if ($dw > $dh && ($do_scale = $can_scale($dw))){
                            $new['h'] = round($new['w'] / $ratio);
                        }else
                        if ($dw < $dh && ($do_scale = $can_scale($dh))){
                            $new['w'] = round($new['h'] * $ratio);
                        }else
                        if ($dw == $dh){
                            $do_scale = $can_scale($dw);
                        }
                    }else
                    // Минимальное изменение
                    if ($fit & (self::FIT_OUTSIDE_LEFT_TOP | self::FIT_OUTSIDE_LEFT_BOTTOM | self::FIT_OUTSIDE_RIGHT_TOP | self::FIT_OUTSIDE_RIGHT_BOTTOM | self::FIT_OUTSIDE_CENTER)){
                        $ratio = $new['w'] / $new['h'];
                        if ($dw < $dh && ($do_scale = $can_scale($dw))){
                            $last = $src['h'];
                            $src['h'] = round($src['w'] / $ratio);
                            if ($fit & (self::FIT_OUTSIDE_LEFT_BOTTOM | self::FIT_OUTSIDE_RIGHT_BOTTOM)){
                                $src['y'] = $last - $src['h'];
                            }else
                            if ($fit == self::FIT_OUTSIDE_CENTER){
                                $src['y'] = round(($last - $src['h']) / 2);
                            }
                        }else
                        if ($dw > $dh && ($do_scale = $can_scale($dh))){
                            $last = $src['w'];
                            $src['w'] = round($src['h'] * $ratio);
                            if ($fit & (self::FIT_OUTSIDE_RIGHT_TOP | self::FIT_OUTSIDE_RIGHT_BOTTOM)){
                                $src['x'] = $last - $src['w'];
                            }else
                            if ($fit & self::FIT_OUTSIDE_CENTER){
                                $src['x'] = round(($last - $src['w']) / 2);
                            }
                        }else
                        if ($dw == $dh){
                            $do_scale = $can_scale($dw);
                        }
                    }
                    if ($do_scale){
                        $img = imagecreatetruecolor($new['w'], $new['h']);
                        imagealphablending($img, false);
                        imagesavealpha($img, true);
                        imagecopyresampled($img, $handler, $new['x'], $new['y'], $src['x'], $src['y'], $new['w'], $new['h'], $src['w'], $src['h']);
                        imagedestroy($this->_handler);
                        $this->_handler = $img;
                        $this->_info['width'] = $new['w'];
                        $this->_info['height'] = $new['h'];
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Обрезание изображения
     * @param int $left Левая граница
     * @param int $top Верхняя граница
     * @param int $right Правая граница
     * @param int $bottom Нижняя граница
     * @param bool $do Признак, выполнять трансформацию (true) или отложить до результата (пути на файл)
     * @return $this
     */
    function crop($left, $top, $right, $bottom, $do = false)
    {
        $left = intval($left);
        $top = intval($top);
        $right = intval($right);
        $bottom = intval($bottom);
        if (!$do){
            $this->_transforms[] = array('crop', array($left, $top, $right, $bottom, true));
            $this->_transforms_str.='crop('.$left.'x'.$top.'x'.$right.'x'.$bottom.')';
        }else{
            // Выполение обрезания
            if ($right < $left) {
                list($left, $right) = array($right, $left);
            }
            if ($bottom < $top) {
                list($top, $bottom) = array($bottom, $top);
            }
            $crop_width = $right - $left;
            $crop_height = $bottom - $top;
            $new = imagecreatetruecolor($crop_width, $crop_height);
            imagealphablending($new, false);
            imagesavealpha($new, true);
            imagecopyresampled($new, $this->handler(), 0, 0, $left, $top, $crop_width, $crop_height, $crop_width, $crop_height);
            $this->_info['width'] = $crop_width;
            $this->_info['height'] = $crop_height;
            imagedestroy($this->_handler);
            $this->_handler = $new;
        }
		return $this;
    }

    /**
     * Поворот изображения
     * @param float $angle Угол поворота от -360 до 360
     * @param bool $do Признак, выполнять трансформацию (true) или отложить до результата (пути на файл)
     * @return $this
     */
    function rotate($angle, $do = false) {
		$angle = min(max(floatval($angle), -360), 360);
        if (!$do){
            $this->_transforms[] = array('rotate', array($angle, true));
            $this->_transforms_str.='rotate('.$angle.')';
        }else{
            $rgba = array(255,255,255,0);
            $handler = $this->handler();
            $bg_color = imagecolorallocatealpha($handler, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
            $new = imagerotate($handler, $angle, $bg_color);
            imagesavealpha($new, true);
            imagealphablending($new, true);
            $this->_info['width'] = imagesx($new);
            $this->_info['height'] = imagesy($new);
            imagedestroy($this->_handler);
            $this->_handler = $new;
        }
		return $this;
	}

    /**
     * Отражение изображения
     * @param int $dir Направление отражения. Задаётся константами Image::FLIP_*
     * @param bool $do Признак, выполнять трансформацию (true) или отложить до результата (пути на файл)
     * @return $this
     */
    function flip($dir = self::FLIP_X, $do = false) {
		$dir = intval($dir);
        if (!$do){
            $this->_transforms[] = array('flip', array($dir, true));
            $this->_transforms_str.='flip('.$dir.')';
        }else{
            $new = imagecreatetruecolor($w = $this->width(), $h = $this->height());
            $src = $this->handler();
            imagealphablending($new, false);
            imagesavealpha($new, true);
            switch ($dir) {
                case self::FLIP_Y:
                    for ($i = 0; $i < $h; $i++) imagecopy($new, $src, 0, $i, 0, $h - $i - 1, $w, 1);
                    break;
                default:
                    for ($i = 0; $i < $w; $i++) imagecopy($new, $src, $i, 0, $w - $i - 1, 0, 1, $h);
            }
            imagedestroy($this->_handler);
            $this->_handler = $new;
        }
		return $this;
	}

    /**
     * Преобразование в серые тона
     * @param bool $do Признак, выполнять трансформацию (true) или отложить до результата (пути на файл)
     * @return $this
     */
    function gray($do = false)
    {
		if (!$do){
            $this->_transforms[] = array('gray', array(true));
            $this->_transforms_str.='gray()';
        }else{
            imagefilter($this->handler(), IMG_FILTER_GRAYSCALE);
        }
		return $this;
	}

    /**
     * Качество изображения для jpg и png
     * @param int $percent от 0 до 100
     * @return $this
     */
    function quality($percent)
    {
        $this->info();
        $this->_info['quality'] = intval($percent);
        if ($percent!=100){
            $this->_transforms_str.='quality('.$this->_info['quality'].')';
        }
        return $this;
    }

    /**
     * Смена расширения
     * @param string $type Новое расширение (gif, png, jpg)
     * @return $this
     */
    function convert($type)
    {
        if (in_array($type, array('gif','png','jpg'))){
            $this->_transforms_str.='convert('.$type.')';
            $this->_convert = $type;
        }
        return $this;
    }

    /**
     * Сброс трансформаций
     * @return $this
     */
    function reset()
    {
        $this->_transforms = array();
        $this->_transforms_str = '';
        $this->_info = null;
        if ($this->_handler) imagedestroy($this->_handler);
        $this->_handler = null;
        return $this;
    }

    /**
     * Файл, ассоциированный с объектом.
     * Если были выполнены трансформации, то возвращается путь на трансформированное изображение
     */
    function file($new_file = null, $root = false, $cache_remote = true, $transformed = true)
    {
        $file = parent::file($new_file, $root, $cache_remote);
        if ($transformed && !empty($this->_transforms_str)){
            $names = F::splitRight('.',$file);
            if (empty($this->_convert)) $this->_convert = $names[1];
            $file = $names[0].'.'.$this->_transforms_str.'.'.$this->_convert;
            $root_file = $root?$file:DIR_SERVER.ltrim($file, '/\\');
            if (!is_file($root_file)){
                foreach ($this->_transforms as $trans){
                    call_user_func_array(array($this, $trans[0]), $trans[1]);
                }
                $this->_info['width'] = imagesx($this->_handler);
                $this->_info['height'] = imagesy($this->_handler);
                imageinterlace($this->_handler, true);
                switch ($this->_convert) {
                    case 'gif':
                        $result = @imagegif($this->_handler, $root_file);
                        break;
                    case 'jpg':
                        $result = @imagejpeg($this->_handler, $root_file, round($this->_info['quality']));
                        break;
                    case 'png':
                        $result = @imagepng($this->_handler, $root_file, round(9 * $this->_info['quality'] / 100));
                        break;
                    default:
                        throw new \Exception('Не поддерживаем тип файла-изображения');
                }
                if (!$result) {
                    throw new \Exception('Не удалось сохранить изображение: '.$root_file);
                }
            }
        }
        return $file;
    }

    /**
     * Ресурс изображения для функций GD
     * @return resource
     * @throws \Exception
     */
    function handler()
    {
        if (!isset($this->_handler)){
            $file = $this->file(null, true, true, false);
            switch ($this->ext()){
                case 'gif':
                    $this->_handler = @imagecreatefromgif($file);
                    break;
                case 'png':
                    $this->_handler = @imagecreatefrompng($file);
                    break;
                case 'jpg':
                    $this->_handler = @imagecreatefromjpeg($file);
                    break;
                default:
                    throw new \Exception('Не поддерживаем тип файла-изображения');
            }
        }
        return $this->_handler;
    }

    /**
     * Информация об изображении
     * @return array
     */
    function info()
    {
        if (empty($this->_info)){
            $file = $this->file(null, true, true, false);
            if (is_file($file) && ($info = getimagesize($file))){
                $ext = array(1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd', 6 => 'bmp', 7 => 'tiff', 8 => 'tiff',
                       9 => 'jpc', 10 => 'jp2', 11 => 'jpx', 12 => 'jb2', 13 => 'swc', 14 => 'iff', 15 => 'wbmp', 16 => 'xbmp'
                );
                $this->_info = array(
                    'width' => $info[0],
                    'height' => $info[1],
                    'ext' => $ext[$info[2]],
                    'quality' => 100
                );
                if (empty($this->_convert)) $this->_convert = $ext[$info[2]];
            }
        }
        return $this->_info;
    }

    /**
     * Ширина
     * @return int|bool
     */
    function width()
    {
        if ($info = $this->info()){
            return $info['width'];
        }else{
            return false;
        }
    }

    /**
     * Высота
     * @return int|bool
     */
    function height()
    {
        if ($info = $this->info()){
            return $info['height'];
        }else{
            return false;
        }
    }

    /**
     * Расширение (тип) изображения
     * @return string|bool
     */
    function ext()
    {
        if ($info = $this->info()){
            return $info['ext'];
        }else{
            return false;
        }
    }

    /**
     * Системные требования
     * @return array
     * @todo На данный момент не используется установщиком, так как проверяются только классы ядра
     */
    static function systemRequirements()
    {
        $requirements = array();
        if (!extension_loaded('gd')){
            $requirements[] = 'Требуется расширения GD версии 2 или новее для работы с изображениями';
        }
        $info = gd_info();
        if (empty($info['PNG Support'])){
            $requirements[] = 'Не поддерживается работа с изображениями формата PNG';
        }
        if (empty($info['GIF Read Support']) || empty($info['GIF Create Support'])){
            $requirements[] = 'Не поддерживается работа с изображениями формата GIF';
        }
        if (empty($info['JPEG Support']) && empty($info['JPG Support'])){
            $requirements[] = 'Не поддерживается работа с изображениями формата JPEG';
        }
        return $requirements;
    }
}