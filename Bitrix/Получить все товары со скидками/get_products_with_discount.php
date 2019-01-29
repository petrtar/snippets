<?use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class AllProductDiscount
{
/**
* @return XML_ID|array
* @throws SystemException
* @throws \Bitrix\Main\LoaderException
*/
public static function getFull($arrFilter = array(), $arSelect = array())
{
if (!Loader::includeModule('sale')) throw new SystemException('Не подключен модуль Sale');

//Все товары со скидкой!!!
// Группы пользователей
global $USER;
$arUserGroups = $USER->GetUserGroupArray();
if (!is_array($arUserGroups)) $arUserGroups = array($arUserGroups);
// Достаем старым методом только ID скидок привязанных к группам пользователей по ограничениям
$actionsNotTemp = \CSaleDiscount::GetList(array("ID" => "ASC"), array("USER_GROUPS" => $arUserGroups), false, false, array("ID"));
while ($actionNot = $actionsNotTemp->fetch()) {
$actionIds[] = $actionNot['ID'];
}
$actionIds = array_unique($actionIds);
sort($actionIds);
// Подготавливаем необходимые переменные для разборчивости кода
global $DB;
$conditionLogic = array('Equal' => '=', 'Not' => '!', 'Great' => '>', 'Less' => '<', 'EqGr' => '>=', 'EqLs' => '<=');
$arSelect = array_merge(array("ID", "IBLOCK_ID", "XML_ID"), $arSelect);
$city = 'MSK';
// Теперь достаем новым методом скидки с условиями. P.S. Старым методом этого делать не нужно из-за очень высокой нагрузки (уже тестировал)
$actions = \Bitrix\Sale\Internals\DiscountTable::getList(array(
'select' => array("ID", "ACTIONS_LIST"),
'filter' => array("ACTIVE" => "Y", "USE_COUPONS" => "N", "DISCOUNT_TYPE" => "P", "LID" => SITE_ID,
"ID" => $actionIds,
array(
"LOGIC" => "OR",
array(
"<=ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL")),
">=ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL"))
),
array(
"=ACTIVE_FROM" => false,
">=ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL"))
),
array(
"<=ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL")),
"=ACTIVE_TO" => false
),
array(
"=ACTIVE_FROM" => false,
"=ACTIVE_TO" => false
),
))
));
// Перебираем каждую скидку и подготавливаем условия фильтрации для CIBlockElement::GetList
while ($arrAction = $actions->fetch()) {
$arrActions[$arrAction['ID']] = $arrAction;
}
foreach ($arrActions as $actionId => $action) {
$arPredFilter = array_merge(array("ACTIVE_DATE" => "Y", "CAN_BUY" => "Y"), $arrFilter); //Набор предустановленных параметров
$arFilter = $arPredFilter; //Основной фильтр
$dopArFilter = $arPredFilter; //Фильтр для доп. запроса
$dopArFilter["=XML_ID"] = array(); //Пустое значения для первой отработки array_merge
//Магия генерации фильтра
foreach ($action['ACTIONS_LIST']['CHILDREN'] as $condition) {
foreach ($condition['CHILDREN'] as $keyConditionSub => $conditionSub) {
$cs = $conditionSub['DATA']['value']; //Значение условия
$cls = $conditionLogic[$conditionSub['DATA']['logic']]; //Оператор условия
//$arFilter["LOGIC"]=$conditionSub['DATA']['All']?:'AND';
$CLASS_ID = explode(':', $conditionSub['CLASS_ID']);

if ($CLASS_ID[0] == 'ActSaleSubGrp') {
foreach ($conditionSub['CHILDREN'] as $keyConditionSubElem => $conditionSubElem) {
$cse = $conditionSubElem['DATA']['value']; //Значение условия
$clse = $conditionLogic[$conditionSubElem['DATA']['logic']]; //Оператор условия
//$arFilter["LOGIC"]=$conditionSubElem['DATA']['All']?:'AND';
$CLASS_ID_EL = explode(':', $conditionSubElem['CLASS_ID']);

if ($CLASS_ID_EL[0] == 'CondIBProp') {
$arFilter["IBLOCK_ID"] = $CLASS_ID_EL[1];
$arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]] = array_merge((array)$arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]], (array)$cse);
$arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]] = array_unique($arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]]);
} elseif ($CLASS_ID_EL[0] == 'CondIBName') {
$arFilter[$clse . "NAME"] = array_merge((array)$arFilter[$clse . "NAME"], (array)$cse);
$arFilter[$clse . "NAME"] = array_unique($arFilter[$clse . "NAME"]);
} elseif ($CLASS_ID_EL[0] == 'CondIBElement') {
$arFilter[$clse . "ID"] = array_merge((array)$arFilter[$clse . "ID"], (array)$cse);
$arFilter[$clse . "ID"] = array_unique($arFilter[$clse . "ID"]);
} elseif ($CLASS_ID_EL[0] == 'CondIBTags') {
$arFilter[$clse . "TAGS"] = array_merge((array)$arFilter[$clse . "TAGS"], (array)$cse);
$arFilter[$clse . "TAGS"] = array_unique($arFilter[$clse . "TAGS"]);
} elseif ($CLASS_ID_EL[0] == 'CondIBSection') {
$arFilter[$clse . "SECTION_ID"] = array_merge((array)$arFilter[$clse . "SECTION_ID"], (array)$cse);
$arFilter[$clse . "SECTION_ID"] = array_unique($arFilter[$clse . "SECTION_ID"]);
} elseif ($CLASS_ID_EL[0] == 'CondIBXmlID') {
$arFilter[$clse . "XML_ID"] = array_merge((array)$arFilter[$clse . "XML_ID"], (array)$cse);
$arFilter[$clse . "XML_ID"] = array_unique($arFilter[$clse . "XML_ID"]);
} elseif ($CLASS_ID_EL[0] == 'CondBsktAppliedDiscount') { //Условие: Были применены скидки (Y/N)
foreach ($arrActions as $tempAction) {
if (($tempAction['SORT'] < $action['SORT'] && $tempAction['PRIORITY'] > $action['PRIORITY'] && $cse == 'N') || ($tempAction['SORT'] > $action['SORT'] && $tempAction['PRIORITY'] < $action['PRIORITY'] && $cse == 'Y')) {
$arFilter = false;
break 4;
}
}
}
}
} elseif ($CLASS_ID[0] == 'CondIBProp') {
$arFilter["IBLOCK_ID"] = $CLASS_ID[1];
$arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]] = array_merge((array)$arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]], (array)$cs);
$arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]] = array_unique($arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]]);
} elseif ($CLASS_ID[0] == 'CondIBName') {
$arFilter[$cls . "NAME"] = array_merge((array)$arFilter[$cls . "NAME"], (array)$cs);
$arFilter[$cls . "NAME"] = array_unique($arFilter[$cls . "NAME"]);
} elseif ($CLASS_ID[0] == 'CondIBElement') {
$arFilter[$cls . "ID"] = array_merge((array)$arFilter[$cls . "ID"], (array)$cs);
$arFilter[$cls . "ID"] = array_unique($arFilter[$cls . "ID"]);
} elseif ($CLASS_ID[0] == 'CondIBTags') {
$arFilter[$cls . "TAGS"] = array_merge((array)$arFilter[$cls . "TAGS"], (array)$cs);
$arFilter[$cls . "TAGS"] = array_unique($arFilter[$cls . "TAGS"]);
} elseif ($CLASS_ID[0] == 'CondIBSection') {
$arFilter[$cls . "SECTION_ID"] = array_merge((array)$arFilter[$cls . "SECTION_ID"], (array)$cs);
$arFilter[$cls . "SECTION_ID"] = array_unique($arFilter[$cls . "SECTION_ID"]);
} elseif ($CLASS_ID[0] == 'CondIBXmlID') {
$arFilter[$cls . "XML_ID"] = array_merge((array)$arFilter[$cls . "XML_ID"], (array)$cs);
$arFilter[$cls . "XML_ID"] = array_unique($arFilter[$cls . "XML_ID"]);
} elseif ($CLASS_ID[0] == 'CondBsktAppliedDiscount') { //Условие: Были применены скидки (Y/N)
foreach ($arrActions as $tempAction) {
if (($tempAction['SORT'] < $action['SORT'] && $tempAction['PRIORITY'] > $action['PRIORITY'] && $cs == 'N') || ($tempAction['SORT'] > $action['SORT'] && $tempAction['PRIORITY'] < $action['PRIORITY'] && $cs == 'Y')) {
$arFilter = false;
break 3;
}
}
}
}
}
if ($arFilter !== false && $arFilter != $arPredFilter) {
if (!isset($arFilter['=XML_ID'])) {
//Делаем запрос по каждому из фильтров, т.к. один фильтр не получится сделать из-за противоречий условий каждой скидки
$res = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
while ($ob = $res->GetNextElement()) {
$arFields = $ob->GetFields();
$productsArray['IDS'][] = $arFields["ID"];
}
} elseif (!empty($arFilter['=XML_ID'])) {
//Подготавливаем массив для отдельного запроса
$dopArFilter['=XML_ID'] = array_unique(array_merge($arFilter['=XML_ID'], $dopArFilter['=XML_ID']));
}
}
}

if (isset($dopArFilter) && !empty($dopArFilter['=XML_ID'])) {
//Делаем отдельный запрос по конкретным XML_ID
$res = \CIBlockElement::GetList(array(), $dopArFilter, false, array("nTopCount" => count($dopArFilter['=XML_ID'])), $arSelect);
while ($ob = $res->GetNextElement()) {
$arFields = $ob->GetFields();
$productsArray['IDS'][] = $arFields["ID"];

}
}
//$productsArray['ids']=array_unique($productsArray['ids']);

return $productsArray;
}
}
?>