<?php
/**
 * Class Order_Define_BarCode
 * 条形码基础参数定义
 */

class  Order_Define_Barcode
{
    /**
     * code 128A
     */
    const BARCODE_TYPE_CODE_128A = 1;

    /**
     * code 128B
     */
    const BARCODE_TYPE_CODE_128B = 2;

    /**
     * code 39
     */
    const BARCODE_TYPE_CODE_39 = 3;

    /**
     * code 25
     */
    const BARCODE_TYPE_CODE_25 = 4;

    /**
     * code coda bar
     */
    const BARCODE_TYPE_CODE_CODA_BAR = 5;

    /**
     * default barcode type
     */
    const BARCODE_TYPE_DEFAULT = self::BARCODE_TYPE_CODE_128B;

    /**
     * default line width
     */
    const BARCODE_DEFAULT_LINE_WIDTH = 1;

    /**
     * default line height
     */
    const BARCODE_DEFAULT_LINE_HEIGHT = 50;
}
