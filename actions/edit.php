<?php
if ($admin->is_authenticated()) {

$apartment_id = $modPortalArgs['obj_id'];

include(__DIR__.'/../lib.class.portal_obj_estate.php');
$clsModPortalObjEstate = new ModPortalObjEstate($page_id, $section_id);


$apartment = null;
if ($apartment_id !== null) {
    $r = $clsModPortalObjEstate->get_apartment(['obj_id'=>$apartment_id]);
    if (gettype($r) == 'string') $clsModPortalObjEstate->print_error($r);
	else if ($r === null) $clsModPortalObjEstate->print_error('Объявление не найдено');
    else $apartment = $r->fetchRow();
}
?>

<!--<input type='button' onclick="get_tab_content('estate', 'index')" value='<<< к списку' title='сделанные Вами изменеия НЕ сохраняься'>  &nbsp; &nbsp;-->

<?php echo $apartment_id == null ? '<h2 style="display: inline-block;">Добавление нового объявления</h2>' : "<h2>Редактирование объявления</h2>"; ?>

<br>

<form>
	<?php echo $apartment_id == null ? '' : "<input type='hidden' name='apartment_id' value='{$apartment_id}'>"; ?>
	
	<input type="hidden" name="section_id" value="<?=$section_id?>">
	<input type="hidden" name="page_id" value="<?=$page_id?>">
	
	<table class='adaptive_table'>
	
		<tr>
			<td>
				Категория:
			</td>
			<td><?php
				echo "<select name='category_id'>";
				$aprt_categories = $clsModPortalObjEstate->get_category(['is_active'=>1]);
				while ($aprt_categories !== null && $aprt_category = $aprt_categories->fetchRow()) {
					$selected = $apartment !== null && $apartment['category_id'] == $aprt_category['category_id'] ? "selected" : '';
					echo "<option value='{$aprt_category['category_id']}' $selected>{$aprt_category['category_name']}</option>";
				}
				echo "</select>";
			?></td>
		</tr>

		<!--<tr>
			<td>
				Населённый пункт:
			</td>
			<td>
				<div id='estate_settlement'></div>
				<script>
					new Settlement(<?php echo $apartment_id == null ? '1' : htmlentities($apartment['settlement_id']); ?>, {
						tag: document.getElementById("estate_settlement")
					});
				</script>

			</td>
		</tr>-->

		<tr>
			<td>
				Адрес:
			</td>
			<td colspan="2">
				<input type="text" name='address' value='<?php echo $apartment_id == null ? '' : htmlentities($apartment['address']); ?>' style='width:100%;'>
			</td>
		</tr>

		<tr>
			<td>
				Заголовок:
			</td>
			<td colspan="2">
				<input type="text" name='name' value='<?php echo $apartment_id == null ? '' : htmlentities($apartment['name']); ?>' style='width:100%;'>
			</td>
		</tr>


		<tr>
			<td>
				Этаж / всего этажей:
			</td>
			<td>
				<input type="number" name='floor' value='<?php echo $apartment_id == null ? '0' : htmlentities($apartment['floor']); ?>' style='width: 50px'>
				/
				<input type="number" name='floor_total' value='<?php echo $apartment_id == 0 ? '0' : htmlentities($apartment['floor_total']); ?>' style='width: 50px'>
			</td>
		</tr>

		<!--<tr>
			<td>
				Всего этажей в доме:
			</td>
			<td>
				<input type="text" name='floor_total' value='<?php echo $apartment_id == 0 ? '' : htmlentities($apartment['floor_total']); ?>' style='width: 50px'>
			</td>
		</tr>-->

		<tr>
			<td>
				Площадь / Площадь земли:
			</td>
			<td>
				<input type="text" name='square' value='<?php echo $apartment_id == null ? '0' : htmlentities($apartment['square']); ?>' style='width: 50px'>
                /
				<input type="text" name='land_square' value='<?php echo $apartment_id == null ? '0' : htmlentities($apartment['land_square']); ?>' style='width: 50px'> м<sup>2</sup>
			</td>
		</tr>

		<!--<tr>
			<td>
				Площадь участка (земли):
			</td>
			<td>
				<input type="text" name='land_square' value='<?php echo $apartment_id == null ? '0' : htmlentities($apartment['land_square']); ?>'>
			</td>
		</tr>-->

		<tr>
			<td>
				Кол-во комнат:
			</td>
			<td>
				<input type="text" name='rooms' value='<?php echo $apartment_id == null ? '0' : htmlentities($apartment['rooms']); ?>' style='width:50px;'>
			</td>
		</tr>

		<tr>
			<td>
				Стоимость:
			</td>
			<td>
				<input type="text" name='price' value='<?php echo $apartment_id == null ? '' : htmlentities($apartment['price']); ?>'> руб.
			</td>
		</tr>

		<tr>
			<td>
				<span title='Географические координаты объекта (участка или дома)'>Широта и долгота</span>:
			</td>
			<td>
				<input type="text" name='lat' value='<?php echo $apartment_id == null ? '' : htmlentities($apartment['lat']); ?>'>
				<input type="text" name='lng' value='<?php echo $apartment_id == null ? '' : htmlentities($apartment['lng']); ?>'>
			</td>
		</tr>

		<tr>
			<td>
				Описапние:
			</td>
			<td>
				<textarea name='description' style ="width:100%;height:100px;"><?php echo $apartment_id == null ? '' : htmlentities($apartment['description']); ?></textarea>
			</td>
		</tr>

		<tr>
			<td>
				Объявление активно:
			</td>
			<td>
				<input type="checkbox" name='is_active' <?php echo $apartment_id == null ? 'checked' : ($apartment['is_active'] == '1' ? 'checked' : ''); ?>>
			</td>
		</tr>

	</table>

    <br><?php //$clsRecaptcha->show('show', true); ?>
	
	<br>
	<input type="button" value='<?php echo $apartment_id == null ? 'Добавить объявление' : 'Сохранить изменения'; ?>' 
	    onclick="sendform(this, 'edit', {<?php /*if (!$apartment_id) echo "func_success: function() {get_tab_content('estate', 'index');},";*/ ?> url: WB_URL+'/modules/wbs_portal_obj_estate/api.php'})">
</form>

<? } ?>