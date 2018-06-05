<?php
/**
 * Class BarcodeUtil
 * Barcode Generator Util
 * USAGE:
 * $objBarcodeUtil = new BarcodeUtil();
 * $pngdata = $objBarcodeUtil::generateBarcode('SIO1234567890123', 80);
 * echo $pngdata;
 */

class Order_Util_BarcodeUtil
{
    /**
     * @param $strCodeText
     * @param $intMinLineHeight
     * @param int $intMinLineWidth
     * @param bool $boolDisplayVertical, the display direction of bars
     * @param int $intCodeType
     *              1 - 128A, 2 - 128B, 3 - Code39, 4 - Code25, 5 - Coda Bar, default is 128B
     * @param bool $boolShowTextCode - whether print Code Text on output image
     * @return string
     */
    public static function generateBarcodeImg(
        $strCodeText,
        $intMinLineHeight = Order_Define_Barcode::BARCODE_DEFAULT_LINE_HEIGHT,
        $intMinLineWidth = Order_Define_Barcode::BARCODE_DEFAULT_LINE_WIDTH,
        $boolDisplayVertical = false,
        $intCodeType = Order_Define_Barcode::BARCODE_TYPE_DEFAULT,
        $boolShowTextCode = false)
    {
        $strEncodedString = '';
        switch ($intCodeType) {
            case Order_Define_Barcode::BARCODE_TYPE_CODE_128A:
                $strEncodedString = self::getCode128A($strCodeText);
                break;
            case Order_Define_Barcode::BARCODE_TYPE_CODE_128B:
                $strEncodedString = self::getCode128B($strCodeText);
                break;
            case Order_Define_Barcode::BARCODE_TYPE_CODE_39:
                $strEncodedString = self::getCode39($strCodeText);
                break;
            case Order_Define_Barcode::BARCODE_TYPE_CODE_25:
                $strEncodedString = self::getCode25($strCodeText);
                break;
            case Order_Define_Barcode::BARCODE_TYPE_CODE_CODA_BAR:
                $strEncodedString = self::getCodaBar($strCodeText);
                break;

            default:
                return null;
        }

        // Pad the edges of the barcode
        $intCodeLength = 20;
        if (true == $boolShowTextCode) {
            $intTextHeight = 30;
        } else {
            $intTextHeight = 0;
        }

        for ($i = 1; $i <= strlen($strEncodedString); $i++) {
            $intCodeLength = $intCodeLength + (integer)(substr($strEncodedString, ($i - 1), 1));
        }

        if (true == $boolDisplayVertical) {
            $intImgWidth = $intMinLineHeight;
            $intImgHeight = $intCodeLength * $intMinLineWidth;
        } else {
            $intImgWidth = $intCodeLength * $intMinLineWidth;
            $intImgHeight = $intMinLineHeight;
        }

        $image = imagecreate($intImgWidth, $intImgHeight + $intTextHeight);
        $colorBlack = imagecolorallocate($image, 0, 0, 0);
        $colorWhite = imagecolorallocate($image, 255, 255, 255);

        imagefill($image, 0, 0, $colorWhite);
        if ($boolShowTextCode) {
            imagestring($image, 5, 31, $intImgHeight, $strCodeText, $colorBlack);
        }

        $intLocation = 10;
        for ($intPosition = 1; $intPosition <= strlen($strEncodedString); $intPosition++) {
            $intCurSize = $intLocation + (substr($strEncodedString, ($intPosition - 1), 1));

            if (true == $boolDisplayVertical) {
                imagefilledrectangle(
                    $image,
                    0,
                    $intLocation * $intMinLineWidth,
                    $intImgWidth,
                    $intCurSize * $intMinLineWidth,
                    ($intPosition % 2 == 0 ? $colorWhite : $colorBlack));
            } else {
                imagefilledrectangle(
                    $image,
                    $intLocation * $intMinLineWidth,
                    0,
                    $intCurSize * $intMinLineWidth,
                    $intImgHeight,
                    ($intPosition % 2 == 0 ? $colorWhite : $colorBlack));
            }
            $intLocation = $intCurSize;
        }

        // export barcode on output stream
        ob_start();
        imagepng($image);
        $blob = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        return $blob;
    }

    /**
     * generate Code128A encoded input text string
     * support upper case, standard numbers, control symbol, special ASCII chars
     * @param $strInputText
     * @return string
     */
    private static function getCode128A($strInputText)
    {
        $intCheckSum = 103;
        $strInputText = strtoupper($strInputText); // Code 128A doesn't support lower case
        $arrCodeArray = [
            ' ' => '212222',
            '!' => '222122',
            '\'' => '222221',
            '#' => '121223',
            '$' => '121322',
            '%' => '131222',
            '&' => '122213',
            '\"' => '122312',
            '(' => '132212',
            ')' => '221213',
            '*' => '221312',
            '+' => '231212',
            ',' => '112232',
            '-' => '122132',
            '.' => '122231',
            '/' => '113222',
            '0' => '123122',
            '1' => '123221',
            '2' => '223211',
            '3' => '221132',
            '4' => '221231',
            '5' => '213212',
            '6' => '223112',
            '7' => '312131',
            '8' => '311222',
            '9' => '321122',
            ':' => '321221',
            ';' => '312212',
            '<' => '322112',
            '=' => '322211',
            '>' => '212123',
            '?' => '212321',
            '@' => '232121',
            'A' => '111323',
            'B' => '131123',
            'C' => '131321',
            'D' => '112313',
            'E' => '132113',
            'F' => '132311',
            'G' => '211313',
            'H' => '231113',
            'I' => '231311',
            'J' => '112133',
            'K' => '112331',
            'L' => '132131',
            'M' => '113123',
            'N' => '113321',
            'O' => '133121',
            'P' => '313121',
            'Q' => '211331',
            'R' => '231131',
            'S' => '213113',
            'T' => '213311',
            'U' => '213131',
            'V' => '311123',
            'W' => '311321',
            'X' => '331121',
            'Y' => '312113',
            'Z' => '312311',
            '[' => '332111',
            '\\' => '314111',
            ']' => '221411',
            '^' => '431111',
            '_' => '111224',
            'NUL' => '111422',
            'SOH' => '121124',
            'STX' => '121421',
            'ETX' => '141122',
            'EOT' => '141221',
            'ENQ' => '112214',
            'ACK' => '112412',
            'BEL' => '122114',
            'BS' => '122411',
            'HT' => '142112',
            'LF' => '142211',
            'VT' => '241211',
            'FF' => '221114',
            'CR' => '413111',
            'SO' => '241112',
            'SI' => '134111',
            'DLE' => '111242',
            'DC1' => '121142',
            'DC2' => '121241',
            'DC3' => '114212',
            'DC4' => '124112',
            'NAK' => '124211',
            'SYN' => '411212',
            'ETB' => '421112',
            'CAN' => '421211',
            'EM' => '212141',
            'SUB' => '214121',
            'ESC' => '412121',
            'FS' => '111143',
            'GS' => '111341',
            'RS' => '131141',
            'US' => '114113',
            'FNC 3' => '114311',
            'FNC 2' => '411113',
            'SHIFT' => '411311',
            'CODE C' => '113141',
            'CODE B' => '114131',
            'FNC 4' => '311141',
            'FNC 1' => '411131',
            'Start A' => '211412',
            'Start B' => '211214',
            'Start C' => '211232',
            'Stop' => '2331112',
        ];

        $strCodeString = '';

        $arrCodeKeys = array_keys($arrCodeArray);
        $arrCodeValues = array_flip($arrCodeKeys);
        for ($X = 1; $X <= strlen($strInputText); $X++) {
            $strActiveKey = substr($strInputText, ($X - 1), 1);
            $strCodeString .= $arrCodeArray[$strActiveKey];
            $intCheckSum = ($intCheckSum + ($arrCodeValues[$strActiveKey] * $X));
        }
        $strCodeString .= $arrCodeArray[$arrCodeKeys[($intCheckSum - (intval($intCheckSum / 103) * 103))]];
        $strCodeString = '211412' . $strCodeString . '2331112';

        return $strCodeString;
    }

    /**
     * generate Code128B encoded input text string
     * support upper case, lower case, special ASCII char
     * @param $strInputText
     * @return string
     */
    private static function getCode128B($strInputText)
    {
        $intCheckSum = 104;

        $arrCodeArray = [
            ' ' => '212222',
            '!' => '222122',
            '\'' => '222221',
            '#' => '121223',
            '$' => '121322',
            '%' => '131222',
            '&' => '122213',
            '\"' => '122312',
            '(' => '132212',
            ')' => '221213',
            '*' => '221312',
            '+' => '231212',
            ',' => '112232',
            '-' => '122132',
            '.' => '122231',
            '/' => '113222',
            '0' => '123122',
            '1' => '123221',
            '2' => '223211',
            '3' => '221132',
            '4' => '221231',
            '5' => '213212',
            '6' => '223112',
            '7' => '312131',
            '8' => '311222',
            '9' => '321122',
            ':' => '321221',
            ';' => '312212',
            '<' => '322112',
            '=' => '322211',
            '>' => '212123',
            '?' => '212321',
            '@' => '232121',
            'A' => '111323',
            'B' => '131123',
            'C' => '131321',
            'D' => '112313',
            'E' => '132113',
            'F' => '132311',
            'G' => '211313',
            'H' => '231113',
            'I' => '231311',
            'J' => '112133',
            'K' => '112331',
            'L' => '132131',
            'M' => '113123',
            'N' => '113321',
            'O' => '133121',
            'P' => '313121',
            'Q' => '211331',
            'R' => '231131',
            'S' => '213113',
            'T' => '213311',
            'U' => '213131',
            'V' => '311123',
            'W' => '311321',
            'X' => '331121',
            'Y' => '312113',
            'Z' => '312311',
            '[' => '332111',
            '\\' => '314111',
            ']' => '221411',
            '^' => '431111',
            '_' => '111224',
            '\`' => '111422',
            'a' => '121124',
            'b' => '121421',
            'c' => '141122',
            'd' => '141221',
            'e' => '112214',
            'f' => '112412',
            'g' => '122114',
            'h' => '122411',
            'i' => '142112',
            'j' => '142211',
            'k' => '241211',
            'l' => '221114',
            'm' => '413111',
            'n' => '241112',
            'o' => '134111',
            'p' => '111242',
            'q' => '121142',
            'r' => '121241',
            's' => '114212',
            't' => '124112',
            'u' => '124211',
            'v' => '411212',
            'w' => '421112',
            'x' => '421211',
            'y' => '212141',
            'z' => '214121',
            '{' => '412121',
            '|' => '111143',
            '}' => '111341',
            '~' => '131141',
            'DEL' => '114113',
            'FNC 3' => '114311',
            'FNC 2' => '411113',
            'SHIFT' => '411311',
            'CODE C' => '113141',
            'FNC 4' => '114131',
            'CODE A' => '311141',
            'FNC 1' => '411131',
            'Start A' => '211412',
            'Start B' => '211214',
            'Start C' => '211232',
            'Stop' => '2331112',
        ];

        $strCodeString = '';
        $arrCodeKeys = array_keys($arrCodeArray);
        $arrCodeValues = array_flip($arrCodeKeys);
        for ($X = 1; $X <= strlen($strInputText); $X++) {
            $strActiveKey = substr($strInputText, ($X - 1), 1);
            $strCodeString .= $arrCodeArray[$strActiveKey];
            $intCheckSum = ($intCheckSum + ($arrCodeValues[$strActiveKey] * $X));
        }
        $strCodeString .= $arrCodeArray[$arrCodeKeys[($intCheckSum - (intval($intCheckSum / 103) * 103))]];

        $strCodeString = '211214' . $strCodeString . '2331112';

        return $strCodeString;
    }

    /**
     * generate Code39 encoded input text string
     * @param $strInputText
     * @return string
     */
    private static function getCode39($strInputText)
    {
        $arrCodeArray = [
            '0' => '111221211',
            '1' => '211211112',
            '2' => '112211112',
            '3' => '212211111',
            '4' => '111221112',
            '5' => '211221111',
            '6' => '112221111',
            '7' => '111211212',
            '8' => '211211211',
            '9' => '112211211',
            'A' => '211112112',
            'B' => '112112112',
            'C' => '212112111',
            'D' => '111122112',
            'E' => '211122111',
            'F' => '112122111',
            'G' => '111112212',
            'H' => '211112211',
            'I' => '112112211',
            'J' => '111122211',
            'K' => '211111122',
            'L' => '112111122',
            'M' => '212111121',
            'N' => '111121122',
            'O' => '211121121',
            'P' => '112121121',
            'Q' => '111111222',
            'R' => '211111221',
            'S' => '112111221',
            'T' => '111121221',
            'U' => '221111112',
            'V' => '122111112',
            'W' => '222111111',
            'X' => '121121112',
            'Y' => '221121111',
            'Z' => '122121111',
            '-' => '121111212',
            '.' => '221111211',
            ' ' => '122111211',
            '$' => '121212111',
            '/' => '121211121',
            '+' => '121112121',
            '%' => '111212121',
            '*' => '121121211',
        ];

        $strCodeString = '';

        // Convert to uppercase
        $strInputText = strtoupper($strInputText);

        for ($X = 1; $X <= strlen($strInputText); $X++) {
            $strCodeString .= $arrCodeArray[substr($strInputText, ($X - 1), 1)] . '1';
        }

        $strCodeString = '1211212111' . $strCodeString . '121121211';

        return $strCodeString;
    }

    /**
     * generate Code25 encoded input text string
     * @param $strInputText
     * @return string
     */
    private static function getCode25($strInputText)
    {
        $arrCodeArray1 = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $arrCodeArray2 = [
            '3-1-1-1-3',
            '1-3-1-1-3',
            '3-3-1-1-1',
            '1-1-3-1-3',
            '3-1-3-1-1',
            '1-3-3-1-1',
            '1-1-1-3-3',
            '3-1-1-3-1',
            '1-3-1-3-1',
            '1-1-3-3-1',
        ];

        for ($X = 1; $X <= strlen($strInputText); $X++) {
            for ($Y = 0; $Y < count($arrCodeArray1); $Y++) {
                if (substr($strInputText, ($X - 1), 1) == $arrCodeArray1[$Y])
                    $temp[$X] = $arrCodeArray2[$Y];
            }
        }

        $strCodeString = '';

        for ($X = 1; $X <= strlen($strInputText); $X += 2) {
            if (isset($temp[$X]) && isset($temp[($X + 1)])) {
                $temp1 = explode('-', $temp[$X]);
                $temp2 = explode('-', $temp[($X + 1)]);
                for ($Y = 0; $Y < count($temp1); $Y++)
                    $strCodeString .= $temp1[$Y] . $temp2[$Y];
            }
        }

        $strCodeString = '1111' . $strCodeString . '311';

        return $strCodeString;
    }

    /**
     * generate CodaBar encoded input text string
     * @param $strInputText
     * @return string
     */
    private static function getCodaBar($strInputText)
    {
        $arrCodeArray1 = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '0',
            '-',
            '$',
            ':',
            '/',
            '.',
            '+',
            'A',
            'B',
            'C',
            'D',
        ];


        $arrCodeArray2 = [
            '1111221',
            '1112112',
            '2211111',
            '1121121',
            '2111121',
            '1211112',
            '1211211',
            '1221111',
            '2112111',
            '1111122',
            '1112211',
            '1122111',
            '2111212',
            '2121112',
            '2121211',
            '1121212',
            '1122121',
            '1212112',
            '1112122',
            '1112221',
        ];

        $strCodeString = '';

        // Convert to uppercase
        $strInputText = strtoupper($strInputText);

        for ($X = 1; $X <= strlen($strInputText); $X++) {
            for ($Y = 0; $Y < count($arrCodeArray1); $Y++) {
                if (substr($strInputText, ($X - 1), 1) == $arrCodeArray1[$Y])
                    $strCodeString .= $arrCodeArray2[$Y] . '1';
            }
        }
        $strCodeString = '11221211' . $strCodeString . '1122121';

        return $strCodeString;
    }
}


