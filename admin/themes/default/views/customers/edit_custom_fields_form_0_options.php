<?php 
$model_name = get_class($model);
$help_hint_path = '/customers/customers/additional_information/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
	<?php
	if (is_array($custom_fields) && sizeof($custom_fields)) {
		foreach ($custom_fields as $row) {
			switch ($row['type']) {
				// single checkbox
				case 0:	
	?>
    <div class="row">
        <?php 
        echo CHtml::activeCheckBox($model,'custom_fields['.$row['id'].'][value]',array('value'=>$row['id'], 'id'=>$container.'_custom_fields_'.$row['id'])); 
        ?>
        <label for="<?php echo $container.'_custom_fields_'.$row['id']; ?>" style="display:inline; text-align: left;"><strong><?php echo $row['name'].($row['required'] ? ' *':'');?></strong></label>
    </div>       
    <?php				
					break;
				// multiple checkbox
				case 1:
	?>
    <div class="row">
        <strong><?php echo $row['name'].($row['required'] ? ' *':'');?></strong><br />
        <div>
        <?php
		foreach ($row['options'] as $id_custom_fields_option => $row_option) {
	        echo CHtml::activeCheckBox($model,'custom_fields['.$row['id'].'][options]['.$row_option['id'].'][value]',array('value'=>$row_option['id'], 'id'=>$container.'_custom_fields_'.$row['id'].'_'.$row_option['id'])); 
			?>
			<label for="<?php echo $container.'_custom_fields_'.$row['id'].'_'.$row_option['id']; ?>" style="display:inline; text-align: left;"><strong><?php echo $row_option['name'];?></strong></label>
			<?php
			
			if ($row_option['add_extra']) {
				echo '&nbsp;&nbsp;'.CHtml::activeTextField($model,'custom_fields['.$row['id'].'][options]['.$row_option['id'].'][extra]',array('size' => 25, 'id'=>$container.'_custom_fields_'.$row['id'].'_'.$row_option['id'].'_extra'));
			}
			
			echo '<br />';
		}			
		?>
        <br /><span id="<?php echo $container; ?>_custom_fields[<?php echo $row['id']; ?>][value]_errorMsg" class="error"></span>
        </div>    
    </div>     
    <?php				
					break;
				// dropdown
				case 2:
	?>
    <div class="row">
        <strong><?php echo $row['name'].($row['required'] ? ' *':'');?></strong><br />
        <div>
        <?php
		//echo'<pre>'.print_r($row['add_extra_options'][$model->custom_fields[$row['id']]['value']],1).'</pre>';
		//echo $model->custom_fields[$row['id']]['value'] . ' - ' . $row['add_extra_options'][30]['class'];
			echo CHtml::activeDropDownList($model, 'custom_fields['.$row['id'].'][value]', CHtml::listData($row['options'], 'id', 'name'), array('prompt'=>'--','options'=>$row['add_extra_options'],'id'=>$container.'_custom_fields_'.$row['id']));
			if ($row['add_extra']) {
				echo '&nbsp;&nbsp;'.CHtml::activeTextField($model,'custom_fields['.$row['id'].'][extra]',array('size' => 25, 'id'=>$container.'_custom_fields_'.$row['id'].'_extra', 'style'=> (isset($row['add_extra_options'][$model->custom_fields[$row['id']]['value']])?'':'display:none;')));
			}				
		?>
        <br /><span id="<?php echo $container; ?>_custom_fields[<?php echo $row['id']; ?>][value]_errorMsg" class="error"></span>
        </div>    
    </div>     
    <?php				
					break;
				// textfield
				case 3:
	?>
    <div class="row">
        <strong><?php echo $row['name'].($row['required'] ? ' *':'');?></strong><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'custom_fields['.$row['id'].'][value]',array('style' => 'width: 250px;', 'id'=>$container.'_custom_fields_'.$row['id']));
        ?>
        <br /><span id="<?php echo $container; ?>_custom_fields[<?php echo $row['id']; ?>][value]_errorMsg" class="error"></span>
        </div>     
    </div>        
    <?php				
					break;
				// textarea
				case 4:	
	?>
    <div class="row">
        <strong><?php echo $row['name'].($row['required'] ? ' *':'');?></strong><br />
        <div>
        <?php
        echo CHtml::activeTextArea($model,'custom_fields['.$row['id'].'][value]',array('style' => 'width: 250px;', 'id'=>$container.'_custom_fields_'.$row['id'], 'rows'=>4));
        ?>
        <br /><span id="<?php echo $container; ?>_custom_fields[<?php echo $row['id']; ?>][value]_errorMsg" class="error"></span>
        </div>     
    </div>        
    <?php									
					break;
				// radio
				case 5:
	?>
    <div class="row">
        <strong><?php echo $row['name'].($row['required'] ? ' *':'');?></strong><br />
        <div>
        <?php
		foreach ($row['options'] as $id_custom_fields_option => $row_option) {
			//echo '<pre>'.print_r($model->custom_fields,1).$row['id'].'</pre>';
	        echo CHtml::radioButton($model_name.'[custom_fields]['.$row['id'].'][value]',$model->custom_fields[$row['id']]['value'] == $row_option['id'] ? 1:0,array('value'=>$row_option['id'], 'id'=>$container.'_custom_fields_radio_'.$row['id'].'_'.$row_option['id'])); 
			?>
			<label for="<?php echo $container.'_custom_fields_'.$row['id'].'_'.$row_option['id']; ?>" style="display:inline; text-align: left;"><strong><?php echo $row_option['name'];?></strong></label>
			<?php
			
			if ($row_option['add_extra']) {
				echo '&nbsp;&nbsp;'.CHtml::activeTextField($model,'custom_fields['.$row['id'].'][options]['.$row_option['id'].'][extra]',array('size' => 25, 'id'=>$container.'_custom_fields_radio_options_'.$row['id'].'_'.$row_option['id'],'disabled'=>($model->custom_fields[$row['id']]['options'][$row_option['id']]['extra']?'':'disabled')));
			}
			
			
			
			echo '<br />';
		}			
		?>
        <span id="<?php echo $container; ?>_custom_fields[<?php echo $row['id']; ?>][value]_errorMsg" class="error"></span>
        </div>    
    </div>     
    <?php					
					break;
			}
		}
	}
	?> 
</div>
</div>
<?php
$script = '';

//echo Html::script($script); 
?>