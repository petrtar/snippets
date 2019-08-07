<?
// можно использовать, например, в result_modifier
// Задаем количество элементов на странице
$countOnPage = 20;
// Исходный массив данных для списка
$elements =$arResult["USERS"];
// Получаем номер текущей страницы из реквеста
$page = intval($_GET['PAGEN_1']);
if(empty($page)) $page=1;
// Отбираем элементы текущей страницы
$elementsPage = array_slice($elements, ($page-1) * $countOnPage, $countOnPage);
$arResult["ELEMENTS"] = $elementsPage;
// Подготовка параметров для пагинатора
$navResult = new CDBResult();
$navResult->NavPageCount = ceil(count($elements) / $countOnPage);
$navResult->NavPageNomer = $page;
$navResult->NavNum = 1;
$navResult->NavPageSize = $countOnPage;
$navResult->NavRecordCount = count($elements);

// вывод шаблона
$APPLICATION->IncludeComponent('bitrix:system.pagenavigation', '', array(
    'NAV_RESULT' =>$navResult
));