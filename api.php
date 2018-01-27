<?php

require_once(__DIR__.'/lib.class.portal_obj_estate.php');

$action = $_POST['action'];

$section_id = $clsFilter->f('section_id', [['integer', "Не указана секция!"]], 'fatal');
$page_id = $clsFilter->f('page_id', [['integer', "Не указана страница!"]], 'fatal');

require_once(WB_PATH."/framework/class.admin.php");
$admin = new admin('Start', '', false, false);
$clsModPortalObjEstate = new ModPortalObjEstate(null, null);

if ($action == 'edit') {
    
    check_auth(); //check_all_permission($page_id, ['pages_modify']);
    if ($apartment_id !== null) {
        // прповеряем доступ к объекту
    }
    
    $apartment_id = $clsFilter->f('apartment_id', [['integer', '']], 'default', null);

    if ($apartment_id !== null) {
    	/*$apartment = $clsEstate->get_apartment(['apartment_id'=>$apartment_id]);
    	if ($apartment === null) print_error('Такого объявления не существует!');
    	if (gettype($apartment) === 'string') print_error($apartment);
    	$apartment = $apartment->fetchRow();
    	
    	if ($apartment['owner_id'] !== $admin->get_user_id()) print_error('У вас нет доступа к данному объявлению!');*/
    }

    $category_id = $clsFilter->f('category_id', [['integer', 'Выберите категорию!']], 'append');
    $fields = [
    	'category_id'=>$category_id,
	    'name' => $clsFilter->f('name', [['1', 'Введите заголовок!']], 'append'),
	    'settlement_id' => 1,//$clsFilter->f('settlement_id', [['integer', 'Выберите город!']], 'append'),
	    'price' => $clsFilter->f('price', [['float', 'Укажите стоимость!']], 'append'),
	    'address' => $clsFilter->f('address', [['1', 'Укажите адрес!'], ['mb_strCount', 'Разрешённая длина - 255 символов', 0,255]], 'append'),
	    'description' => $clsFilter->f('description', [['1', 'Напишите описание!']], 'append'),

        'is_active' => $clsFilter->f('is_active', [['variants', '', [['true', 'false']]]], 'default', 'true'),

	    'floor' => $clsFilter->f('floor', [['1', 'Укажите этаж!'], ['float', 'Неверный формат этажа!']], in_array($category_id, [1, 5, 11, 13, 14, 15]) ? 'append' : 'default', 0),
	    'floor_total' => $clsFilter->f('floor_total', [['1', 'Укажите всего этажей!']], in_array($category_id, [3, 8, 1, 13, 14, 15, 4, 5, 6, 11, 7, 9]) ? 'append' : 'default', 0),
	    'square' => $clsFilter->f('square', [['1', 'Укажите площадь!'], ['float', 'Неверный формат площади!']], in_array($category_id, [1,3,4,5,6,7,8,9,10,11,12,13,14,15,16]) ? 'append' : 'default', 0),
	    'land_square' => $clsFilter->f('land_square', [['1', 'Укажите площадь земли'], ['float', 'Неверный формат площади земли!']], in_array($category_id, [2,12]) ? 'append' : 'default', 0),
	    'rooms' => $clsFilter->f('rooms', [['1', 'Укажите количество комнат']], in_array($category_id, [1,13,14,15,4,6,11,7]) ? 'append' : 'default', 0),

	    'lat' => $clsFilter->f('lat', [['float', 'Неверный формат широты!']], 'default', null),
	    'lng' => $clsFilter->f('lng', [['float', 'Неверный формат долготы!']], 'default', null),
	    'page_id'=>$page_id,
	    'section_id'=>$section_id,
	    'obj_type_id'=>$clsModPortalObjEstate->obj_type_id
    ];
    $fields['is_active'] = $fields['is_active'] == 'true' ? '1' : '0';

    if ($clsFilter->is_error()) $clsFilter->print_error();

    if ($apartment_id === null) {

        $fields['user_owner_id'] = $admin->get_user_id();
    	$apartment_id = $clsModPortalObjEstate->add_apartment($fields);
    	if (gettype($apartment_id) === 'string') print_error($apartment_id);

	    print_success('Объявление успешно добавлено!');

    } else {
    	
   	    /*$is_changed = $form_moder->match_and_moder([
    	'name' => [$apartment['name'], $fields['name'], 1],
    	'price' => [$apartment['price'], $fields['price'], 1],
    	'floor' => [$apartment['floor'], $fields['floor'], 1],
    	'floor_total' => [$apartment['floor_total'], $fields['floor_total'], 1],
    	'square' => [$apartment['square'], $fields['square'], 1],
    	'land_square' => [$apartment['land_square'], $fields['land_square'], 1],
    	'rooms' => [$apartment['rooms'], $fields['rooms'], 1],
    	'lat' => [$apartment['lat'], $fields['lat'], 1],
    	'lng' => [$apartment['lng'], $fields['lng'], 1],
    	'address' => [$apartment['address'], $fields['address'], 1],
    	'description' => [$apartment['description'], $fields['description'], 1],
    	], $apartment_id, $admin->get_user_id(), 'edit_estate_main', $clsEstate->service_id) | $is_changed;

        // обновляем некоторые поля без модерацции
        $direct = ['is_active', 'settlement_id', 'category_id'];
        $_fields = [];
        foreach ($direct as $i => $name) {
        	if (isset($fields[$name])) $_fields[$name] = $fields[$name];
        }
        if ($_fields) {
        	$r = $clsEstate->update_apartment($apartment_id, $_fields);
        	if (gettype($r) === 'string') print_error($r);
        } else print_is_changed($is_changed);*/
    }

} else { print_error('Неверный apin name!'); }

?>