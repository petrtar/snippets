<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->createFrame()->begin("Загрузка навигации");
?>

<? if ($arResult["NavPageCount"] > 1): ?>

    <? if ($arResult["NavPageNomer"] + 1 <= $arResult["nEndPage"]): ?>
        <?
        $plus = $arResult["NavPageNomer"] + 1;
        $url = $arResult["sUrlPathParams"] . "PAGEN_2=" . $plus
        ?>

        <div class="load_more full tac l m35b" data-url="<?= $url ?>">
            <span class="ma ib fwb ffhcr m25t tdn">Показать еще</span>
        </div>


    <? else: ?>

        <div class="load_more full tac l m35tb">
            Загружено все
        </div>

    <? endif ?>

<? endif ?>
