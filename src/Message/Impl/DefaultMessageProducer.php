<?php
/**
 * @author: ahuazhu@gmail.com
 * Date: 16/7/19  下午1:46
 */

namespace PhpCat\Message\Impl;


use PhpCat\Message\MessageProducer;

class DefaultMessageProducer implements MessageProducer
{

    private $m_messageManager;


    public function newEvent($type, $name)
    {
        $this->checkInit();
        $event = new DefaultEvent($type, $name, $this->m_messageManager);
        return $event;
    }

    public function newTransaction($type, $name)
    {
        $this->checkInit();
        $transaction = new DefaultTransaction($type, $name, $this->m_messageManager);
        $this->m_messageManager->start($transaction);
        return $transaction;
    }

    public function newMetric($type, $name)
    {
        $this->checkInit();
        $metric = new DefaultMetric(isset($type) ? $type : "", $name, $this->m_messageManager);
//        $this->m_messageManager->getThreadLocalMessageTree()->setSample(false);
        return $metric;
    }


    public function checkInit()
    {
        if ($this->m_messageManager == null) {
            $this->init();
        }
        if (!$this->m_messageManager->hasContext()) {
            $this->m_messageManager->setUp();
        }
    }

    public function init()
    {
        $this->m_messageManager = new SingleThreadMessageManager();
    }
}