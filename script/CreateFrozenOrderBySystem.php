#!php/bin/php
<?php
/**
 * @name CreateFrozenOrderBySystem
 * @desc auto create frozen order
 * @author ziliang.zhang02@ele.me
 */

Bd_Init::init();

echo "[create_frozen_order_by_system]work\n\n";

try {
    $objWork = new CreateFrozenOrderBySystem();
    $objWork->work();
} catch (Exception $e) {
    echo sprintf("\n[create_frozen_order_by_system]error, code[%d], msg[%s]", $e->getCode(), $e->getMessage());
    Bd_Log::warning(sprintf('[create_frozen_order_by_system]error, code[%d], msg[%s]', $e->getCode(), $e->getMessage()));
    exit(-1);
}

echo "\n[create_frozen_order_by_system]success";

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