<?php
/**
 * @author: ahuazhu@gmail.com
 * Date: 16/7/20  下午2:37
 */

namespace PhpCat\Message\Impl;



class IndexProducer
{

    function  nextIndex()
    {
        list($micro, $_) = explode(" ", microtime());
        $index = date("is").substr($micro,3,2).mt_rand(0,99);
        return intval($index);
    }
}