<?php
/**
 * @author: ahuazhu@gmail.com
 * Date: 16/7/19  下午4:47
 */

namespace PhpCat\Message;


interface MessageContext
{
    public function add($message);

    public function end($messageManger, $transaction);

    public function start($transaction);

    public function getTree();

    public function setTree($messageTree);

    public function flush($tree);
}