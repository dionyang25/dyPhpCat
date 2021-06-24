<?php

require __DIR__.'/../autoload.php';


class ExceptionTest
{
    function methodThrowsException()
    {
        throw new \Exception;
    }

    public function run()
    {
        try {
            $a = $this->methodThrowsException();
            echo $a;
            echo "hello, world!!!\n";
        } catch (\Exception $e) {
            \PhpCat\PhpCat::logError('Error', get_class($e), $e);
        }
    }
}


$test = new ExceptionTest();
$test->run();
exit;

class TransactionTest
{
    public function run()
    {
        $transaction = PhpCat::newTransaction("URL", "/Test");

        {
            $t1 = PhpCat::newTransaction('Invoke', 'method1()');
            sleep(2);
            $t1->setStatus(Message::SUCCESS);
            $t1->addData("Hello", "world");
            $t1->complete();
        }

        {
            $t2 = PhpCat::newTransaction('Invoke', 'method2()');
            sleep(2);
            $t2->setStatus(Message::SUCCESS);
            $t2->complete();
        }

        {
            $t3 = PhpCat::newTransaction('Invoke', 'method3()');
            sleep(1);
            {
                $t4 = PhpCat::newTransaction('Invoke', 'method4()');
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
    }
}

class MetricDemo
{
    public function run()
    {
        PhpCat::logMetricForCount("支付笔数");
        PhpCat::logMetricForSum("支付总额", 100.5);

    }
}

$metricTest = new MetricDemo();

$metricTest->run();


$test = new TransactionTest();
$test->run();
