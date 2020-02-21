<?php


namespace MGroups\MGcrud\lib;

use Illuminate\Support\Str;

class MGNames
{
    /**
     * CashReceiptTable to cash_receipt_table
     * @param String $name
     * @return string
     */
    public static function getTableName(String $name)
    {
        return Str::lower(trim(preg_replace('/(?<=\\w)(?=[A-Z])/',"_$1", $name)));
    }

    /**
     * CashReceiptTable to Cash Receipt Table
     * @param String $name
     * @return string
     */
    public static function getNormalCallWord(String $name)
    {
        return trim(preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $name));
    }

    /**
     * CashReceiptTable to cashReceiptTable
     * @param String $name
     * @return string
     */
    public static function getVarName(String $name)
    {
        return Str::camel($name);
    }

}
