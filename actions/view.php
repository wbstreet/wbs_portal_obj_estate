<?php

include(__DIR__.'/../lib.class.portal_obj_estate.php');
$clsModPortalObjEstate = new ModPortalObjEstate($page_id, $section_id);

if ($admin->is_authenticated()) {$is_auth = true;}
else { $is_auth = false; }

$modPortalArgs['obj_owner'] = $clsFilter->f2($_GET, 'obj_owner', [['variants', '', ['private', 'agency', 'all', 'my']]], 'default', 'all');

//if ($arrSettlement === null) {
//      echo "Для корректного отображения недвижимости, пожалуйста выберите населённый пункт<br>";
//} else {
//      echo "<h1>Недвижимость в {$arrSettlement['type_short_name']}. {$arrSettlement['settlement_name']}</h1>";
//}

// Отоюражение партнёра
/*if ($arrSettlement !== null) {
        $partner = $clsEstate->get_partner_by_rayon($arrSettlement['country_id'], $arrSettlement['region_id'], $arrSettlement['rayon_id']);
        if (gettype($partner) == 'string') echo $partner;
        else if ($partner === null) echo "Для данного населённого пункта отсутствует партнёр. Вы можете стать первыми.";
        else {
                $partner = $partner->fetchRow();
                $partner_link = $partner['partner_url'] ? "<a target='_blank' href='{$partner['business_url']}'>{$partner['business_name']}</a>" : $partner['business_name'];
            echo "<p>Наш партнёр в данном городе - {$partner_link} </p>";
        }
}*/

// ----- отображение категорий

$obj_cats = [];
$r = $clsModPortalObjEstate->get_category(['is_active'=>1]);
while ($r !== null && $cat = $r->fetchRow()) {
    $obj_cats[] = $cat;
}

// ----- Отображение недвижимости

$fields = [
    //'settlement_id'=>get_current_settlement(),
    'category_id'=>$modPortalArgs['category_id'],
    'is_active'=>1,
    //'is_moder'=>1,
    'is_deleted'=>0,
    'find_str'=>$modPortalArgs['s'],
    'find_in'=>$modPortalArgs['s_in'],
];
if ($modPortalArgs['obj_owner'] === 'private') $fields['partner_id'] = ['value'=>null];
else if ($modPortalArgs['obj_owner'] === 'agency') $fields['owner_id'] = ['value'=>null];
else if ($modPortalArgs['obj_owner'] === 'my') {
    $fields['owner_id'] = ['value'=>$admin->get_user_id()];
    unset($fields['is_active']);
}

$obj_total = $clsModPortalObjEstate->get_obj($fields, true);
if (gettype($obj_total) == 'string') $clsModPortalObjEstate->print_error($obj_total);

$divs = calc_paginator_and_limit($modPortalArgs, $fields, $obj_total);

// вынимаем страницы
$apartments = $clsModPortalObjEstate->get_obj($fields);
if (gettype($apartments) == 'string') $clsModPortalObjEstate->print_error($apartments);

$page_link = page_link($wb->link);
$objs = [];
$x = 0;
while ($apartments !== null && $apartment = $apartments->fetchRow(MYSQLI_ASSOC)) {
    $can_edit = $is_auth && $admin->get_user_id() === $apartment['user_owner_id'] ? true : false;

    /*if ($intSettlement === null) {
        $arrSettl = $clsSettlement->getSettlements($apartment['settlement_id']);
        if (gettype($arrSettl) == 'string') {echo $arrSettl;}
            $arrSettl = $arrSettl->fetchRow(MYSQLI_ASSOC);
        $settl = "<div class='apartment_settlement'>Где: {$arrSettl['type_short_name']}. {$arrSettl['settlement_name']}</div>";
    } else*/

    $photos = $clsModPortalObjEstate->get_image(['apartment_id'=>$apartment['obj_id'], 'is_active'=>1]);
    if ($photos == null) {
        if ($modPortalArgs['obj_owner'] !== 'my') continue;
    } else if (gettype($photos) == 'string') {
        if ($modPortalArgs['obj_owner'] !== 'my') continue;
    } else {
        $image = $photos->fetchRow(MYSQLI_ASSOC);
        $apartment['orig_image'] = $clsStorageImg->get_without_db($image['md5'], $image['ext'], 'origin');
        $apartment['preview_image'] = $clsStorageImg->get_without_db($image['md5'], $image['ext'], '350x250');
    }

    $price = (integer)$apartment['price'];
    if ($price > 1000000000) $price = ($price/1000000000)." млрд";
    else if ($price > 1000000) $price = ($price/1000000)." млн";
    else if ($price > 1000) $price = ($price/1000)." тыс";
    $apartment['price'] = $price;

    $x += 1;
        
    $objs[] = $apartment;
}

/*if ($x == 0 || $apartments === null) {
        if ($find_str === null) echo "В данной категории отсутствует недвижимость";
        else echo "Недвижимость не найдена для запроса \"".htmlentities($find_str)."\". Попробуйте выбрать другую категорию или город.";
} else {
        if ($find_str !== null) echo "Найдена недвижимость по запросу \"".htmlentities($find_str)."\".";
}*/

$clsModPortalObjEstate->render('view_list.html', [
    'is_auth'=>$is_auth,
    'modPortalArgs'=>$modPortalArgs,
    'obj_cats'=>$obj_cats,
    'objs'=>$objs,
    'can_edit'=>$can_edit,
    'page_link'=>$page_link,
    'divs'=>$divs,
    ]);

?>