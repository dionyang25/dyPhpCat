<?php

/*
 * laravel中间件类
 */
namespace PhpCat\Middleware;

use PhpCat\Message\Message;
use Closure;
use PhpCat\Utils\CatContext;
use PhpCat\Utils\PhpCatUtil;

/**
 * Class PhpCatHttpMiddleware
 * @package Dffl\Service\Common\Library\PhpCat\Middleware
 * http请求中间件
 */
class PhpCatHttpMiddleware
{
    public function handle($request, Closure $next)
    {
        $transaction = null;
        $phpCat = PhpCatUtil::getPhpCat();
        if (empty($phpCat)) {
            return $next($request);
        }
        try {
//            if(empty($request->header(CatContext::CONTEXT_ROOT))){
//                dd($request);
//            }
            $catContext = CatContext::getInstance();
            $catContext->addProperty(CatContext::CONTEXT_ROOT,$request->header(CatContext::CONTEXT_ROOT));
            $catContext->addProperty(CatContext::CONTEXT_PARENT,$request->header(CatContext::CONTEXT_PARENT));
            $catContext->addProperty(CatContext::CONTEXT_CHILD,$request->header(CatContext::CONTEXT_CHILD));
            $phpCat->logRemoteCallServer($catContext);
            $transaction = $phpCat->newTransaction("RemotingService", $request->path());
            $phpCat->logEvent("RemotingService.method", $request->method());
            $phpCat->logEvent("RemotingService.queryString", $request->getQueryString());
            $phpCat->logEvent("RemotingService.client", $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"]);
            $phpCat->logEvent("RemotingService.address", $_SERVER["SERVER_ADDR"] . ":" . $_SERVER["SERVER_PORT"]);
            if(function_exists("env")){
                $phpCat->logEvent("RemotingService.server", env("CAT_DOMAIN"));
            }
        } catch (\Exception $e) {

        }
        try {
            $result = $next($request);
            if(!empty($transaction)){
                $transaction->setStatus(Message::SUCCESS);
            }
            return $result;
        } catch (\Exception $e2) {
            if(!empty($phpCat) && !empty($transaction)){
                $phpCat->logException("Error",$e2->getMessage(),$e2);
                $transaction->setStatus($e2->getMessage());
            }
            throw $e2;
        } finally {
            PhpCatUtil::setTransaction($transaction);
        }
    }

    public function terminate($request, $response)
    {
        $transaction = PhpCatUtil::getTransaction();
        if(!empty($transaction)){
            PhpCatUtil::getTransaction()->complete();
            PhpCatUtil::setTransaction(null);
        }
    }
}