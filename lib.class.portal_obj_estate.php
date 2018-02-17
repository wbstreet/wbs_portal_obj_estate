<?php

$path_core = __DIR__.'/../wbs_portal/lib.class.portal.php';
if (file_exists($path_core )) include($path_core );
else echo "<script>console.log('Модуль wbs_portal_obj_estate требует модуль wbs_portal')</script>";

// используется только в данном файле. Пока неизвестно, включать её в sql_tools.php или нет.
if (!function_exists('guess_operator')) {
function guess_operator($value, $inverse=false) {
        if ($value === 'NULL') {
                if ($inverse) return ' is not ';
                else {return ' is ';}
        } else {
                if ($inverse) return '!=';
                else {return '=';}
        }
}
}

if (!class_exists('ModPortalObjEstate')) {
class ModPortalObjEstate extends ModPortalObj {

    function __construct($page_id, $section_id) {
        parent::__construct('estate', 'Недвижимость', $page_id, $section_id);
        $this->tbl_apartment = "`".TABLE_PREFIX."mod_{$this->prefix}estate_apartment`";
        $this->tbl_category = "`".TABLE_PREFIX."mod_{$this->prefix}estate_category`";
        $this->tbl_partner = "`".TABLE_PREFIX."mod_{$this->prefix}estate_partner`";
        $this->tbl_image = "`".TABLE_PREFIX."mod_{$this->prefix}estate_image`";
        $this->clsStorageImg = new WbsStorageImg();
    }

    function uninstall() {
        global $database;
        
        // проверяем наличие объектов

        $r = select_row($this->tbl_apartment, 'COUNT(`obj_id`) as ocount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['ocount'] > 0) return "Существуют объекты!";
        
        // проверяем, наличие партнёров

        $r = select_row($this->tbl_partner, 'COUNT(`partner_id`) as pcount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "Существуют партнёры!";

        // проверяем, наличие категорий

        $r = select_row($this->tbl_category, 'COUNT(`category_id`) as ccount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['ccount'] > 0) return "Существуют категории!";

        // удаляем модуль

        $arr = ["DROP TABLE ".$this->tbl_apartment,
                "DROP TABLE ".$this->tbl_category,
                "DROP TABLE ".$this->tbl_partner,
        ];

        $r = parent::uninstall($arr);
        if ($r === false) return "Неизвестная ошибка!";
        if ($r !== true) return $r;
        
        return true;
        
    }
    
    function install() {
        return parent::install();
    }
    
    function get_category($sets=[]) {
        global $database;

        if (!isset($sets['order_by'])) $sets['order_by'] = ['category_name'];
        if (!isset($sets['order_by_dir'])) $sets['order_by_dir'] = null;

        $where = ['1=1'];

        if (isset($sets['category_id']) && $sets['category_id'] !== null) $where[] = "{$this->tbl_category}.`category_id`=".process_value($sets['category_id']);
        if (isset($sets['is_active']) && $sets['is_active'] !== null) $where[] = "{$this->tbl_category}.`is_active`=".process_value($sets['is_active']);

        $where = implode(' AND ', $where);
                
        $sql = "
        SELECT
            *
            FROM
            {$this->tbl_category}
            WHERE
            $where
            ".build_order($sets['order_by'], $sets['order_by_dir']);

            return check_select($sql);
    }

    function split_arrays(&$fields) {
        $_fields = [];
        $f= "obj_id,page_id,section_id,obj_type_id,user_owner_id,is_active,is_deleted, moder_status,moder_comment,date_created,date_end_activity,substrate_color,substrate_opacity,substrate_border_color,substrate_border_left,substrate_border_right,bg_image";
        $common_fields = explode(',', $f);
        foreach ($common_fields as $k => $v) {
            if (!in_array($v, array_keys($fields))) continue;
            $_fields[$v] = $fields[$v];
            unset($fields[$v]);
        }
        return $_fields;
    }

    function add_apartment($fields) {
                global $database;


        $_fields = $this->split_arrays($fields);

                $r = insert_row($this->tbl_obj_settings, $_fields);
                if ($r !== true) return "Неизвестная ошибка";

                $apartment_id = $database->getLastInsertId();

        $fields['obj_id'] = $apartment_id;
                $r = insert_row($this->tbl_apartment, $fields);
                if ($r !== true) return "Неизвестная ошибка";


                return $apartment_id;
        }

function get_obj($sets=[], $only_count=false) {
        global $sql_builder, $database;

        $is_deleted = isset($sets['is_deleted']) ? $database->escapeString($sets['is_deleted']) : null;
        $is_moder = isset($sets['is_moder']) ? $sets['is_moder'] : null;

        if (isset($sets['limit_offset'])) $limit_offset = (integer)($sets['limit_offset']); else $limit_offset = null;
        if (isset($sets['limit_count'])) $limit_count = (integer)($sets['limit_count']); else $limit_count = null;
        if (isset($sets['find_str'])) $find_str = $database->escapeString($sets['find_str']); else $find_str = null;

        $order_by = isset($sets['order_by']) ? glue_keys($sets['order_by']) : null;
        $order_dir = isset($sets['order_dir']) ? $database->escapeString($sets['order_dir']) : null;

        $where = [];

        //$sql_builder->add_raw_where('1=1');
        if (isset($sets['obj_id'])) $where[] = "{$this->tbl_apartment}.`obj_id`=".process_value($sets['obj_id']);
        //if (isset($sets['settlement_id']) && $sets['settlement_id'] !== null) $where[] = '`settlement_id`='.process_value($sets['settlement_id']);
        if (isset($sets['category_id']) && $sets['category_id'] !== null) $where[] = "{$this->tbl_apartment}.`category_id`=".process_value($sets['category_id']);
        if (isset($sets['external_id']) && $sets['external_id'] !== null) $where[] = "{$this->tbl_apartment}.`external_id`=".process_value($sets['external_id']);
        if (isset($sets['is_active']) && $sets['is_active'] !== null) $where[] = "{$this->tbl_obj_settings}.`is_active`=".process_value($sets['is_active']);
        if (isset($sets['is_moder']) && $sets['is_moder'] !== null) $where[] = "{$this->tbl_obj_settings}.`moder_status`=".process_value($sets['is_moder']);
        if (isset($sets['is_deleted']) && $sets['is_deleted'] !== null) $where[] = "{$this->tbl_obj_settings}.`is_deleted`=".process_value($sets['is_deleted']);

        //if (isset($sets['owner_id'])) $where[] = "{$this->tbl_apartment}.`owner_id`=".process_value($sets['owner_id']);
        //if (isset($sets['partner_id'])) $where[] = "{$this->tbl_apartment}.`partner_id`=";
                
        if (isset($sets['owner_id'])) {
                $w = "{$this->tbl_obj_settings}.`user_owner_id`";
                $value = process_value($sets['owner_id']);
                //if ($value === 'NULL') $where[] = $w.' is '.$value;
                //else $where[] = $w.'='.$value;
                $where[] = $w.guess_operator($value).$value;
        }
        if (isset($sets['partner_id'])) {
                $w = "{$this->tbl_apartment}.`partner_id`";
                $value = process_value($sets['partner_id']);
                //if ($value === 'NULL') $where[] = $w.' is '.$value;
                //else $where[] = $w.'='.$value;
                $where[] = $w.guess_operator($value).$value;
        }

        if ( $find_str !== null ) {
                $find_str = str_replace('%', '\%', $find_str);
                $find_like = "{$this->tbl_apartment}.`name` LIKE '%$find_str%'";
        }

        // данные о категории и расширенные данные  всё равно получаем
                $where[] = "{$this->tbl_apartment}.`category_id`={$this->tbl_category}.`category_id`";
                //$where[] = "{$this->tbl_apartment}.`obj_id`={$this->tbl_obj_settings}.`obj_id` AND {$this->tbl_obj_settings}.`obj_type_id`={$this->tbl_obj_type}.`obj_type_id` AND {$this->tbl_obj_type}.`obj_type_latname`=".process_value($this->obj_type_latname);
        $where[] = "{$this->tbl_apartment}.`obj_id`={$this->tbl_obj_settings}.`obj_id` AND {$this->tbl_obj_settings}.`obj_type_id`=".process_value($this->obj_type_id);
                if ( $find_str !== null ) $where[] = "($find_like)";

        $where = implode(' AND ', $where);

        $select = $only_count ? "COUNT({$this->tbl_apartment}.obj_id) AS count" : "*";/*"        {$this->tbl_apartment}.`apartment_id`,
        {$this->tbl_apartment}.`partner_id`,
        {$this->tbl_apartment}.`settlement_id`,
        {$this->tbl_apartment}.`category_id`,
        {$this->tbl_apartment}.`name`,
        {$this->tbl_apartment}.`price`,
        {$this->tbl_apartment}.`floor`,
        {$this->tbl_apartment}.`floor_total`,
        {$this->tbl_apartment}.`square`,
        {$this->tbl_apartment}.`land_square`,
        {$this->tbl_apartment}.`lat`,
        {$this->tbl_apartment}.`lng`,
        {$this->tbl_apartment}.`external_url`,
        {$this->tbl_apartment}.`rooms`,
        {$this->tbl_apartment}.`external_id`,
        {$this->tbl_category}.`category_name`";*/

        if ( $order_by !== null ) {
                $order = " ORDER BY $order_by ";
            if ( $order_dir !== null ) $order .= " $order_dir ";
        } else $order = '';

        $limit = build_limit($limit_offset, $limit_count);

        $sql = "SELECT
        $select
        FROM {$this->tbl_apartment}, {$this->tbl_category}, {$this->tbl_obj_settings} WHERE $where $order $limit";

        //echo "<script>console.log(`".htmlentities($sql)."`);</script>";

        $r = $database->query($sql);
        if ($database->is_error()) return $database->get_error();

        if ($only_count) {
                        $count = $r->fetchRow()['count'];
                        return (integer)$count;
        } else {
                if ($r->numRows() === 0) return null;
                return $r;
        }
    }
        
    function update_apartment($apartment_id, $fields) {
        global $database;

        $_fields = $this->split_arrays($fields);

        $r = $this->get_obj(['obj_id'=>$apartment_id]);
        if (gettype($r) === 'string') return $r;
        if ($r === null) return 'Объявление не найдено (id: '.$database->escapeString($apartment_id).')';


        if ($_fields) {
                $r = update_row($this->tbl_obj_settings, $_fields, glue_fields(['obj_id'=>$apartment_id], 'AND'));
                if ($r !== true) return $r;
        }

        if ($fields) {
                $r = update_row($this->tbl_apartment, $fields, glue_fields(['obj_id'=>$apartment_id], 'AND'));
                if ($r !== true) return 'Неизвестная ошибка';
        }
        
        return true;
    }
        
    function add_image($apartment_id, $images) {
        if (count($images) == 0) return true;
        $sql = "INSERT INTO {$this->tbl_image} (`obj_id`, `image_storage_id`, `is_main`, `is_active`) VALUES ";
        $values = [];
        foreach ($images as $i => $image) {
            $values[] = '('.glue_values([$apartment_id, $image, 0, 1]).')';
        }
                
        return check_insert($sql.implode(',', $values));
    }
        
    function get_image($sets=[]) {
        $where = ["{$this->tbl_image}.`image_storage_id` = {$this->clsStorageImg->tbl_img}.`img_id`"];

        if (isset($sets['apartment_id']) && $sets['apartment_id'] !== null) $where[] = "{$this->tbl_image}.`obj_id`=".process_value($sets['apartment_id']);
        if (isset($sets['is_main']) && $sets['is_main'] !== null) $where[] = "{$this->tbl_image}.`is_main`=".process_value($sets['is_main']);
        if (isset($sets['is_active']) && $sets['is_active'] !== null) $where[] = "{$this->tbl_image}.`is_active`=".process_value($sets['is_active']);

        $sql = "SELECT * FROM {$this->tbl_image}, {$this->clsStorageImg->tbl_img} ";

        if ($where) $sql .= " WHERE ".implode(' AND ', $where);

        $sql .= "ORDER BY {$this->tbl_image}.`is_main` DESC";
            
        return check_select($sql);
    }
}
}
?>