#!php/bin/php
<?php
/**
 * @name CreateFrozenOrderBySystem
 * @desc auto create frozen order
 * @author ziliang.zhang02@ele.me
 */

Bd_Init::init();

try {
    $objWork = new CreateFrozenOrderBySystem();
    $objWork->work();
} catch (Exception $e) {
    Bd_Log::warning(sprintf('exec %s error. code[%d], msg[%s]',
        __FILE__, $e->getCode(), $e->getMessage()));
    exit(-1);
}

class CreateFrozenOrderBySystem
{

    /**
     * page service
     * @var Service_Page_Frozen_CreateOrderBySystem
     */
    protected $objPage;

    public function __construct()
    {
        $this->objPage = new Service_Page_Frozen_CreateOrderBySystem();
    }

    /**
     * work
     * @throws Exception
     * @throws Nscm_Exception_Error
     * @throws Order_BusinessError
     */
    public function work()
    {
        $this->objPage->execute();
    }
}