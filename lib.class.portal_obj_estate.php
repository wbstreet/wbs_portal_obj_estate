<?php

$path_core = __DIR__.'/../wbs_portal/lib.class.portal.php';
if (file_exists($path_core )) include($path_core );
else echo "<script>console.log('Модуль wbs_portal_obj_estate требует модуль wbs_portal')</script>";

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

    /*function get_obj($sets=[], $only_count=false) {
        global $database;

        $where = [
            "{$this->tbl_apartment}.`category_id`={$this->tbl_category}.`category_id`", // данные о категории и расширенные данные  всё равно получаем
            "{$this->tbl_apartment}.`obj_id`={$this->tbl_obj_settings}.`obj_id`",
            "{$this->tbl_obj_settings}.`obj_type_id`=".process_value($this->obj_type_id),
        ];
        $this->_getobj_where($sets, $where);

        if (isset($sets['category_id'])) $where[] = "{$this->tbl_apartment}.`category_id`=".process_value($sets['category_id']);
        if (isset($sets['external_id'])) $where[] = "{$this->tbl_apartment}.`external_id`=".process_value($sets['external_id']);
        if (isset($sets['partner_id'])) $where[] = "{$this->tbl_apartment}.`partner_id`=".process_value($sets['partner_id']);

        $find_keys = ['name'=>"{$this->tbl_apartment}.`name`", 'description'=>"{$this->tbl_apartment}.`description`"];
        $where_find = getobj_search($sets, $find_keys);
        if ($where_find) $where[] = $where_find;

        $where = implode(' AND ', $where);
        $select = $only_count ? "COUNT({$this->tbl_apartment}.obj_id) AS count" : "*";
        $order_limit = getobj_order_limit($sets);

        $sql = "SELECT
        $select
        FROM {$this->tbl_apartment}, {$this->tbl_category}, {$this->tbl_obj_settings} WHERE $where $order_limit";

        //echo "<script>console.log(`".htmlentities($sql)."`);</script>";

        return getobj_return($sql, $only_count);
    }*/

    function get_obj($sets=[], $only_count=false) {

        $tables = [$this->tbl_apartment, $this->tbl_category, $this->tbl_obj_settings];

        $where = [
            "{$this->tbl_apartment}.`category_id`={$this->tbl_category}.`category_id`", // данные о категории и расширенные данные  всё равно получаем
            "{$this->tbl_apartment}.`obj_id`={$this->tbl_obj_settings}.`obj_id`",
            "{$this->tbl_obj_settings}.`obj_type_id`=".process_value($this->obj_type_id),
        ];
        $this->_getobj_where($sets, $where);
        
        $where_opts = [
                'category_id'=>"{$this->tbl_apartment}.`category_id`",
                'external_id'=>"{$this->tbl_apartment}.`external_id`",
                'partner_id'=>"{$this->tbl_apartment}.`partner_id`",
        ];
        
        $where_find = ['name'=>"{$this->tbl_apartment}.`name`", 'description'=>"{$this->tbl_apartment}.`description`"];
        
        return get_obj($tables, $where, $where_opts, $where_find, $sets, $only_count);
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