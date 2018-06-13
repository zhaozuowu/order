<?php
/**
 * Class Model_Orm_LogDetail
 * 操作日志详情
 */

class Model_Orm_LogDetail extends Wm_Orm_ShardingActiveRecord implements Wm_Orm_ShardingTableRangeIf
{
    use Order_Trait_ShardingOrder;

    public static $tableName = 'log_detail';
    public static $shardingTableName = '';
    public static $dbName = 'nscm_base';
    public static $clusterName = 'nscmbase_db_cluster';
    public static $shardingKey = 'create_time';
}
