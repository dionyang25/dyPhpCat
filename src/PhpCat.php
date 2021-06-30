<?php
namespace PhpCat;
use PhpCat\Config\Config;
use PhpCat\Message\Impl\DefaultMessageIdFactory;
use PhpCat\Message\Impl\DefaultMessageProducer;
use PhpCat\Utils\CatContext;

/**
 * Class PhpCat
 */
class PhpCat
{
    private static $messageProducer;
    /**
     * 设置配置
     */
    function __construct($domain, $servers , $timeout = 700000){
        Config::$domain = $domain;
        Config::$servers = $servers;
        Config::$timeout = $timeout;
        self::$messageProducer = new DefaultMessageProducer();
        self::$messageProducer->init();
    }

    /**
     * 代码运行情况监控：运行时间统计、次数、错误次数等等
     * 大小写敏感的字符串. 常见的Transaction type有 "URL", "SQL", "Email", "Exec"等
    a).transaction适合记录跨越系统边界的程序访问行为，比如远程调用，数据库调用，也适合执行时间较长的业务逻辑监控
    b).某些运行期单元要花费一定时间完成工作, 内部需要其他处理逻辑协助, 我们定义为Transaction.
    c).Transaction可以嵌套(如http请求过程中嵌套了sql处理).
    d).大部分的Transaction可能会失败, 因此需要一个结果状态码.
    e).如果Transaction开始和结束之间没有其他消息产生, 那它就是Atomic Transaction(合并了起始标记).
     * 示例:
     * $phpcat = \Joy::$di->get('phpcat');
     * 或
     * $phpcat = new PhpCat("domain",[
     *      server1,
     *      server2,
     * ]);
     * $transaction = $phpcat->newTransaction("URL", "/Test");
    {
    $t1 = $phpcat->newTransaction('Invoke', 'method1()');
    sleep(2);
    $t1->setStatus(Message::SUCCESS);
    $t1->addData("Hello", "world");
    $t1->complete();
    }

    {
    $t2 = $phpcat->newTransaction('Invoke', 'method2()');
    sleep(2);
    $t2->setStatus(Message::SUCCESS);
    $t2->complete();
    }

    {
    $t3 = $phpcat->newTransaction('Invoke', 'method3()');
    sleep(1);
    {
    $t4 = $phpcat->newTransaction('Invoke', 'method4()');
    sleep(2);
    $t4->setStatus(Message::SUCCESS);
    $t4->complete();
    }

    $t3->setStatus(Message::SUCCESS);
    $t3->complete();
    }

    $transaction->setStatus(Message::SUCCESS);
    $transaction->addData("Hello, world!");
    $transaction->complete();
     *
     * 当 Transaction setStatus 为 失败时,将出现在Problem面板
     *
     * @param $type  类型  大小写敏感的字符串. 常见的Transaction type有 "URL", "SQL", "Email", "Exec", "Task", "Call"
     * @param $name  具体名称, 如 某个具体URL地址, 某个具体方法名
     * @return DefaultTransaction
     */
    public function newTransaction($type, $name)
    {
        return self::$messageProducer->newTransaction($type, $name);
    }


    /**
     *
     * 记录程序中一个事件记录了多少次，错误了多少次。相比于Transaction，Event没有运行时间统计。
     * 开销比transaction要小
     * 常见的Event type有 "Info", "Warn", "Error", "", 还有"Cat"用来表示Cat内部的消息
     * 当类型为 Error时，且 status 失败时,错误信息出现在 监护系统Problem面板
     *
     * @param $type  事件类型  Info, Warn, Error, Call,  SQL, Exception 等
     * @param $name  具体的方法, URL地址
     * @param null $key  消息详情的key
     * @param null $value
     * @param string $status  成功为 0 失败则
     */
    public function logEvent($type, $name, $dataKey=null, $dataValue = null,  $status = Message::SUCCESS)
    {
        $event = self::newEvent($type, $name);
        $event->setStatus($status);
        $event->addData($dataKey, $dataValue);
        $event->complete();
    }


    /**
     * 错误报警日志
     *
     * 记录程序中一个事件记录了多少次，错误了多少次。相比于Transaction，Event没有运行时间统计。
     * 开销比transaction要小
     * 常见的Event type有 "Info", "Warn", "Error", "", 还有"Cat"用来表示Cat内部的消息
     *
     * 当类型为 Error时，且 status 失败时,错误信息出现在 监护系统Problem面板
     *
     * @param $type  事件类型  Info, Warn, Error, Call,  SQL, Exception 等
     * @param $name  具体的方法, URL地址
     * @param null $key  消息详情的key
     * @param null $value
     * @param string $status  成功为 0 失败则
     */
    public function logError($name, $dataKey=null, $dataValue = null,  $status = Message::SUCCESS)
    {
        $event = self::newEvent('Error', $name);
        $event->setStatus($status);
        $event->addData($dataKey, $dataValue);
        $event->complete();
    }




    /**
     * 用来记录 Exception 错误追踪 类型 Event
     *
     * @param $type
     * @param $name
     * @param Exception $error
     */
    public function logException($type, $name, \Exception $error)
    {
        $event = self::newEvent($type, $name);
        $event->setStatus($error->getMessage() );
        $trace = "\n" . $error->getMessage() . "\n";
        $trace .= $error->getTraceAsString() . "\n";
        $event->addData('Trace', $trace);
        $event->complete();
    }




    /**
     * 业务统计: 次数统计
     * @param $name
     * @param int $quantity
     */
    public function logMetricForCount($name, $quantity = 1)
    {
        self::logMetricInternal($name, 'C', sprintf("%d", $quantity));
    }

    /**
     * 业务统计:总量统计
     * @param $name
     * @param float $value
     */
    public function logMetricForSum($name, $value = 1.0)
    {
        self::logMetricInternal($name, 'S', sprintf("%.2f", $value));
    }

    /**
     * DefaultMessageTree
     */
    public function logRemoteCallClient(CatContext $ctx){
        try {
            $messageTree = self::$messageProducer->getMessageManager()->getThreadLocalMessageTree();
            $messageId = $messageTree->getMessageId();
            if(empty($messageId)){
                $messageId = DefaultMessageIdFactory::getNextId();
                $messageTree->setMessageId($messageId);
            }
            $childId = DefaultMessageIdFactory::getNextId();
//            $this->logEvent("RemoteCall","","&",$childId);
            $this->logEvent("RemoteCall","",$childId,null);
            $root = $messageTree->getRootMessageId();
            if(empty($root)){
                $root = $messageId;
            }
            $ctx->addProperty(CatContext::CONTEXT_ROOT,$root);
            $ctx->addProperty(CatContext::CONTEXT_PARENT,$messageId);
            $ctx->addProperty(CatContext::CONTEXT_CHILD,$childId);
        }catch (\Exception $e){

        }


    }

    public function logRemoteCallServer(CatContext $ctx){
        try {
            $messageTree = self::$messageProducer->getMessageManager()->getThreadLocalMessageTree();
            $childId = $ctx->getProperty(CatContext::CONTEXT_CHILD);
            $rootId = $ctx->getProperty(CatContext::CONTEXT_ROOT);
            $parentId = $ctx->getProperty(CatContext::CONTEXT_PARENT);
            if(!is_null($parentId)){
                $messageTree->setParentMessageId($parentId);
            }
            if(!is_null($rootId)){
                $messageTree->setRootMessageId($rootId);
            }
            if(!is_null($childId)){
                $messageTree->setMessageId($childId);
            }
        }catch (\Exception $e){

        }

    }

    /**
     * 业务统计
     * @param $name
     * @param $status
     * @param $keyValuePairs
     */
    private static function logMetricInternal($name, $status, $keyValuePairs)
    {
        $type = '';
        $metric = self::$messageProducer->newMetric($type, $name);

        if (isset($keyValuePairs)) {
            $metric->addData($keyValuePairs);
        }

        $metric->setStatus($status);
        $metric->complete();
    }


    private static function newEvent($type, $name)
    {
        return self::$messageProducer->newEvent($type, $name);
    }
}