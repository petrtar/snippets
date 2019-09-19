<? //Кэширование
$obCache = new CPHPCache();
$cache_time = 3600 * 24 * 14;
$cacheID = "calc";
$cachePath = '/calc/';

if ($obCache->InitCache($cache_time, $cacheID, $cachePath))// Если кэш валиден
{
    $vars = $obCache->GetVars();
    $arResult = $vars['result'];
} elseif ($obCache->StartDataCache()) {
    // логика работы. получаем необходимые значения итд
    $arResult = "необходимые значения";

    if (!empty($arResult)) {
        $obCache->EndDataCache(// Сохраняем переменные в кэш.
            array('result' => $arResult)
        );
    }
}