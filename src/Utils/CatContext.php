<?php


namespace PhpCat\Utils;


class CatContext{
    CONST CONTEXT_ROOT = "_catRootMessageId";

    CONST CONTEXT_PARENT = "_catParentMessageId";

    CONST CONTEXT_CHILD = "_catChildMessageId";

    private static $catContextHolder = [];

    private static $catContextInstance;

    /**
     * @return CatContext
     */
    public static function getInstance(){
        if(self::$catContextInstance!=null && self::$catContextInstance instanceof CatContext){
            return self::$catContextInstance;
        }
        self::$catContextInstance = new CatContext();
        return self::$catContextInstance;
    }

    public function addProperty($key,$value){
        self::$catContextHolder[$key] = $value;
    }

    public function getProperty($key){
        return isset(self::$catContextHolder[$key])?self::$catContextHolder[$key]:null;
    }
}