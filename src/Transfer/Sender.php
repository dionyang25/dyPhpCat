<?php

/**
 * @author: ahuazhu@gmail.com
 * Date: 16/7/18  下午5:53
 */

namespace PhpCat\Transfer;


interface Sender
{
    function send($messageTree);
}