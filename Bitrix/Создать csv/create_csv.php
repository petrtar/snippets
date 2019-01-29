<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");
$csvFile = new CCSVData();
$fields_type = 'R';
$delimiter = ";";
$csvFile->SetFieldsType($fields_type);
$csvFile->SetDelimiter($delimiter);

//заголовки столбцов
$arrHeaderCSV = array("NAME","CODE","SORT","GROUP1","GROUP2","PREVIEW_TEXT",
    "DETAIL_TEXT","SMALL_IMG","BIG_IMG","PRICE", "CURRENCY",
    "CATALOG_QUANTITY","CATALOG_QUANTITY_TRACE",
    "ARTICUL","NEW","MOREPHOTO","ACTIVE",
    "EX_ID","CODG_1", "CODG_2", "G_ACT1",
    "G_ACT2","G_SORT1","G_SORT2","XML_ID", "S_TYPE","B_TYPE");


//сохранение строки
$csvFile->SaveFile("astra.csv", $arrHeaderCSV);

/*
 * Каждая строка - массив. Формируем и добавляем через SaveFile();
 */
?>