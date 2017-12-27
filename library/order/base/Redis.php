<?php
/**
 * @name Dao_Redis_Base
 * @desc redis base
 * @author wanggang(wanggang01@iwaimai.baidu.com)
 */

class Order_Base_Redis
{
    /**
     * nscm redis connection name
     * @var string
     */
    const REDIS_NWMS = 'redis_nwms';

    /**
     * redis object
     * @var Redis
     */
    protected $objRedisConn;

    /**
     * init
     */
    public function __construct()
    {
        $this->objRedisConn = Wm_Service_RedisMgr::getInstanceByBns(self::REDIS_NWMS);
    }
}
