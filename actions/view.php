<?php

include(__DIR__.'/../lib.class.portal_obj_estate.php');
$clsModPortalObjEstate = new ModPortalObjEstate($page_id, $section_id);

if ($admin->is_authenticated()) {$is_auth = true;}
else { $is_auth = false; }

$modPortalArgs['apart_from'] = $clsFilter->f2($_GET, 'apart_from', [['variants', '', ['private', 'agency', 'all']]], 'default', 'all');

?>

<?php if ($is_auth) { ?>
    <a href="?action=edit"><input type="button" value="Добавить объявление"></a>
<?php } ?>

<style>
	input[disabled] {
		background: rgb(222, 222, 222);
	}
</style>

<?php

//if ($arrSettlement === null) {
//	echo "Для корректного отображения недвижимости, пожалуйста выберите населённый пункт<br>";
//} else {
//	echo "<h1>Недвижимость в {$arrSettlement['type_short_name']}. {$arrSettlement['settlement_name']}</h1>";
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

echo "<div style='text-align:center;'>";
echo "<select onchange=\"set_params({'category_id':this.value, page_num:1})\">";
echo "<option "; if ($modPortalArgs['category_id'] === null) {echo "selected ";} echo "value=''>Все категории</option>";
$aprt_categories = $clsModPortalObjEstate->get_category(['is_active'=>1]);
while ($aprt_categories !== null && $aprt_category = $aprt_categories->fetchRow()) {
	if ($modPortalArgs['category_id'] == $aprt_category['category_id']) $selected = "selected"; else {$selected = '';}
	echo "<option value='{$aprt_category['category_id']}' $selected>{$aprt_category['category_name']}</option>";
}
echo "</select> ";

echo " <div class='btn-group'>";
    echo "<input type='button' value='частные лица' onclick=\"set_params({apart_from:'private', page_num:1})\"".($modPortalArgs['apart_from'] == 'private' ? ' disabled' : '').">";
    echo "<input type='button' value='агентства' onclick=\"set_params({apart_from:'agency', page_num:1})\"".($modPortalArgs['apart_from'] == 'agency' ? ' disabled' : '').">";
    echo "<input type='button' value='все' onclick=\"set_params({apart_from:'all', page_num:1})\"".($modPortalArgs['apart_from'] == 'all' ? ' disabled' : '').">";
echo "</div>";

echo "</div>";

// ----- Отображение недвижимости

$objects_per_page = 20;
$common_opts = [
	//'settlement_id'=>get_current_settlement(),
	'category_id'=>$modPortalArgs['category_id'],
	'is_active'=>1,
	//'is_moder'=>1,
	'is_deleted'=>0,
	];
if ($modPortalArgs['apart_from'] === 'private') $common_opts['partner_id'] = ['value'=>null];
//else if ($modPortalArgs['apart_from'] === 'agency') $common_opts['user_owner_id'] = ['value'=>null];
else if ($modPortalArgs['apart_from'] === 'agency') $common_opts['partner_id'] = ['value'=>null];

// вынимаем страницы
$opts = array_merge($common_opts, [
	'find_str'=>$modPortalArgs['s'],
	'limit_count'=>$modPortalArgs['obj_per_page'],
	'limit_offset'=>$modPortalArgs['obj_per_page'] * ($modPortalArgs['page_num']-1),
	]);
$apartments = $clsModPortalObjEstate->get_apartment($opts);
if (gettype($apartments) == 'string') $clsModPortalObjEstate->print_error($apartments);

// подсчитываем количество страниц
$opts = array_merge($common_opts, [
	'find_str'=>$modPortalArgs['s'],
	]);
$count_pages = $clsModPortalObjEstate->get_apartment($opts, true) / $modPortalArgs['obj_per_page'];
if (strpos((string)$count_pages, '.') !== false) $count_pages = (integer)$count_pages + 1; 


$text = "<div style='text-align:center;'>";
$x = 0;
while ($apartments !== null && $apartment = $apartments->fetchRow(MYSQLI_ASSOC)) {
    if ($modPortalArgs['category_id'] === null) $category = "<div class='apartment_category'>{$apartment['category_name']}</div><br>";
    else $category = '';
    /*if ($intSettlement === null) {
    	$arrSettl = $clsSettlement->getSettlements($apartment['settlement_id']);
    	if (gettype($arrSettl) == 'string') {echo $arrSettl;}
	    $arrSettl = $arrSettl->fetchRow(MYSQLI_ASSOC);
    	$settl = "<div class='apartment_settlement'>Где: {$arrSettl['type_short_name']}. {$arrSettl['settlement_name']}</div>";
    } else*/ $settl = '';

    //$photos = $clsEstate->get_image(['apartment_id'=>$apartment['apartment_id'], 'is_main'=>1, 'is_active'=>1]);
    //if ($photos == null) {$image_url = WB_URL.$clsEstate->default_image_dir;}
    //else {
    //    if (gettype($photos) == 'string') {continue;}
    //    $photos = $photos->fetchRow(MYSQLI_ASSOC);
    //    $image_url = WB_URL.$clsEstate->get_image_path($apartment['apartment_id'], $photos['image_name'], false);
    //    $preview_url = WB_URL.$clsEstate->get_image_path($apartment['apartment_id'], $photos['image_name'], true);
    //}
    /*$res = $clsEstate->select_main_image($apartment['apartment_id'], WB_URL, true);
    if ($res === null) continue;
    list($image_url, $preview_url) = $res;*/ list($image_url, $preview_url) = ['no', 'no'];

    $price = (integer)$apartment['price'];
    if ($price > 1000000000) $price = ($price/1000000000)." млрд";
    else if ($price > 1000000) $price = ($price/1000000)." млн";
    else if ($price > 1000) $price = ($price/1000)." тыс";

    $panel_edit = "<div class='apartment_panel_edit'>
    <span onclick=\"set_params({action:'edit', obj_id:'{$apartment['obj_id']}'})\">Изменить</span>
    </div>
    ";

	$text .= "
	<div class='apartment'>
        <a href='{$image_url}' class='fm'>
            <img class='apartment_image' src='{$preview_url}' align='left'>
        </a>
        <div class='block_info'>
		    <a class='apartment_name' target='_blank' href='{$apartment['external_url']}'>{$apartment['name']}</a>
		    <br><br>
	   	    <div class='apartment_name' style='font-weight:500;'>Цена: {$price}</div>
		    <span style='font-size:10pt;'>
			    <div class='apartment_floor'>Площадь: {$apartment['square']} м<sup>2</sup></div>
	     	    $settl
			</span>
			<br>
		    $category
	    </div>
	    $panel_edit
	</div>
	";
	$x += 1;
}
$text .= "</div>";

/*if ($x == 0 || $apartments === null) {
	if ($find_str === null) echo "В данной категории отсутствует недвижимость";
	else echo "Недвижимость не найдена для запроса \"".htmlentities($find_str)."\". Попробуйте выбрать другую категорию или город.";
} else {
	if ($find_str !== null) echo "Найдена недвижимость по запросу \"".htmlentities($find_str)."\".";
}*/

echo $text;

?>

<br>
<div class='tab_headers' style="text-align: center; /*background:#d49092*/;" id='bottom'>
</div>

<script>
    /*show_pager(<?=$num_page?>, <?=$count_pages?>, function(page_num){
    	if (location.search.indexOf('page=') != -1) var search = location.search.replace(/page=[0-9]+/, 'page='+page_num);
        else if (location.search == '') var search = '?page='+page_num;
    	else var search = location.search + '&page='+page_num;
        return location.origin + location.pathname + search;//'?page='+page_num;
    });*/
</script>

<style>
    .apartment {
    	width: calc(100% - 10px);
    	max-width:490px;
    	font-size:12pt;
    	border: 1px solid #FFC68E;
    	background: #FFF8D4;
    	overflow:auto;
   	    margin-top: 10px;
   	    display: inline-block;
   	    vertical-align: top;
   	    text-align:left;
   	    padding:5px;
   	    position:relative;
    }

    .apartment .fm {
    	width:40%;
    	max-height: 150px;
    	overflow:hidden;
   	    display: inline-block;
   	    border-radius:5px;
    }

    .apartment .fm .apartment_image {
    	width: 100%;
    	margin:0;
    }
    
    .apartment .block_info {
    	display:inline-block;
    	vertical-align:top;
    	width: 47%;
    }
    
    .apartment .apartment_panel_edit {
        position:absolute;
        bottom: 0;
        right: 0;
        background-color: #aaaaaaaa;
    }
    .apartment .apartment_panel_edit span {
        display:inline-block;
        cursor:pointer;
    }
    .apartment .apartment_panel_edit span:hover {
        background-color: #bbbbbbaa;
    }

    @media screen and (max-width: 420px) {
        .apartment .fm {
        	width:100%;
        }
	    .apartment .block_info {
	    	width: 100%;
	    }
    }
</style>