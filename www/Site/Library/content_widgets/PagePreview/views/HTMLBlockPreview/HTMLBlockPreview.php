<?php
/**
 * Виджет HTML блока
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\content_widgets\PagePreview\views\HTMLBlockPreview;

use Library\views\Widget\Widget;
use Library\views\HtmlBlock\HtmlBlock;

class HTMLBlockPreview extends HtmlBlock
{

    function show($v = array(), $commands, $input)
    {
        $v['object'] = $this->_input['REQUEST']['object']->value();

        $text = $v['object'];
        $len = strlen($text);

        $size = (strpos($text,'<a class="more"> </a>') ? strpos($text,'<a class="more"> </a>')-3 : $len);

        if ($len <= $size)
            return $text;

        $textLen = 0;
        $position  = -1;
        $tagNameStartPos = 0;
        $tagNameEndPos = 0;
        $openTagList = array();

        // Stateful machine status
        // 0 - scanning text
        // 1 - scanning tag name
        // 2 - scanning tag content
        // 3 - scanning tag attribute value
        // 4 - waiting for tag close mark
        $state = 0;

        // 0 - no quotes active
        // 1 - single quotes active
        // 2 - double quotes active
        $quoteType = 0;

        // Flag if 'tag close symbol' is used
        $closeFlag = 0;

        while ((($position+1) < $len) && ($textLen < $size)) {
            $position++;
            $char = $text{$position};
            //	printf("%03u[%u][%03u][%02u] %s\n", $position, $state, $textLen, count($openTagList), $char);

            switch ($state) {
                // Scanning text
                case 0:
                    // '<' - way to starting tag
                    if ($char == '<') {
                        $state = 1;
                        $tagNameStartPos = $position+1;
                        continue;
                    }
                    $textLen++;

                    break;
                case 1:
                    // If this is a space/tab - tag name is finished
                    if (($char == ' ')||($char == "\t")) {
                        $tagNameLen = $position - $tagNameStartPos;
                        $state = 2;
                        continue;
                    }

                    // Activity on tag close flag
                    if ($char == '/') {
                        if ($tagNameStartPos == $position)
                            continue;

                        $tagNameLen = $position - $tagNameStartPos + 1;
                        $state = 4;
                        continue;
                    }

                    // Action on tag closing
                    if ($char == '>') {
                        $tagNameLen = $position - $tagNameStartPos;
                        $tagName = substr($text, $tagNameStartPos, $tagNameLen);
                        //		print "openTag[1]: $tagName\n";

                        // Closing tag
                        if ($tagName{0} == '/') {
                            if ((count($openTagList)) && ($openTagList[count($openTagList)-1] == substr($tagName, 1)))
                                array_pop($openTagList);
                        } else {
                            // Opening tag
                            if (substr($tagName, -1, 1) != '/') {
                                // And not closed at the same time
                                array_push($openTagList, $tagName);
                            }
                        }
                        $state = 0;
                        continue;
                    }

                    // Tag name may contain only english letters
                    if (!((($char >= 'A') && ($char <= 'Z')) || (($char >= 'a') && ($char <= 'z')))) {
                        $state = 0;
                        continue;
                    }
                    break;
                case 2:
                    // Activity on tag close flag
                    if ($char == '/') {
                        $state = 4;
                        continue;
                    }

                    // Action on tag closing
                    if ($char == '>') {
                        $tagName = substr($text, $tagNameStartPos, $tagNameLen);
                        //		print "openTag: $tagName\n";

                        // Closing tag
                        if ((count($openTagList)) && ($openTagList[count($openTagList)-1] == substr($tagName, 1))) {
                            if ($openTagList[count($openTagList)] == substr($tagName, 1))
                                array_pop($openTagList);
                        } else {
                            // Opening tag
                            if (substr($tagName, -1, 1) != '/') {
                                // And not closed at the same time
                                array_push($openTagList, $tagName);
                            }
                        }
                        $state = 0;
                        continue;
                    }

                    // Action on quote
                    if (($char == '"')||($char == "'")) {
                        $quoteType = ($char == '"')?2:1;
                        $state = 3;
                        continue;
                    }
                    break;
                case 3:
                    // Act only on quote
                    if ((($char == '"') && ($quoteType == 2)) || (($char == "'") && ($quoteType == 1))) {
                        $state = 2;
                        continue;
                    }
                    break;
                case 4:
                    // Only spaces or tag close mark is accepted
                    if (($char == ' ') || ($char == "\t")) {
                        continue;
                    }

                    if ($char == '>') {
                        $tagName = substr($text, $tagNameStartPos, $tagNameLen);
                        //			print "openTag: $tagName\n";

                        // Closing tag
                        if ($tagName{0} != '/') {
                            if ((count($openTagList)) && ($openTagList[count($openTagList)-1] == substr($tagName, 1)))
                                array_pop($openTagList);
                        } else {
                            // Opening tag
                            if (substr($tagName, -1, 1) != '/') {
                                // And not closed at the same time
                                array_push($openTagList, $tagName);
                            }
                        }
                        $state = 0;
                        continue;
                    }

                    // Wrong symbol [ this is wholy text ]
                    $state = 0;
                    break;
            }
        }

        $output = substr($text, 0, $position+1);

        // Check if we have opened tags
        while ($tag = array_pop($openTagList))
            $output .= "</".$tag.">";

        $v['object'] = $output;

        return Widget::show($v, $commands, $input);
    }
}