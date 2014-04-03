<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));  
echo CHtml::activeHiddenField($model,'id_tax_rule',array('id'=>$container.'_id_tax_rule')); 

$help_hint_path = '/settings/taxes/tax-rules/exceptions/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;"><span id="<?php echo $container; ?>_id_tax_group_errorMsg" class="error"></span>
        <div class="row">    
            <strong><?php echo Yii::t('views/taxrules/edit_exceptions','LABEL_CUSTOMER_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'customer-type'); ?><br />
            <div>
            <?php
            $sql = 'SELECT 
            id,
            name
            FROM 
            customer_type 
            ORDER BY 
            name ASC';	
            $command=$connection->createCommand($sql);			
            
            echo CHtml::activeDropDownList($model,'id_customer_type',CHtml::listData($command->queryAll(true),'id','name'),array( 'id'=>$container.'_id_customer_type','prompt'=>'All'));
            ?>
            </div>                
        </div>     
        <div class="row">    
            <strong><?php echo Yii::t('global','LABEL_TAX_GROUP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'tax-group'); ?><br />
            <div>
            <?php
            $sql = 'SELECT 
            id,
            name
            FROM 
            tax_group 
            ORDER BY 
            name ASC';	
            $command=$connection->createCommand($sql);			
            
            echo CHtml::activeDropDownList($model,'id_tax_group',CHtml::listData($command->queryAll(true),'id','name'),array( 'id'=>$container.'_id_tax_group','prompt'=>'All'));
            ?>
            
            </div>                
        </div>
        
          <?php
		  
		$params=array(':id_tax_rule'=>$model->id_tax_rule, ':language_code'=>Yii::app()->language,':id'=>$model->id);
		  
        //create query 
		$sql = "SELECT 
			tax_rule_rate.id AS id_tax_rule_rate,
			tax_rule_exception_rate.rate,
			tax_description.name AS tax_name,
			tax_rule_rate.id_tax_rule,
			tax_rule_rate.rate AS current_rate		
			FROM 
			tax_rule_rate 
			LEFT JOIN 
			tax_rule_exception_rate
			ON
			(tax_rule_rate.id = tax_rule_exception_rate.id_tax_rule_rate AND tax_rule_exception_rate.id_tax_rule_exception = :id)
			LEFT JOIN 
			tax_rule_exception
			ON
			(tax_rule_exception_rate.id_tax_rule_exception = tax_rule_exception.id)
			LEFT JOIN 
			tax
			ON
			(tax_rule_rate.id_tax = tax.id)
			LEFT JOIN 
			tax_description
			ON
			(tax.id = tax_description.id_tax AND tax_description.language_code = :language_code)
			WHERE tax_rule_rate.id_tax_rule = :id_tax_rule
			ORDER BY tax_rule_rate.sort_order ASC
			";
			
		
		
		$command=$connection->createCommand($sql);
		$x = 0;
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {?>      
            <div style="float:left;<?php echo($x?'margin-left:8px;':'');?>">
                <strong><?php echo $row['tax_name'];?></strong><em>(<?php echo $row['current_rate'];?>%)</em>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'rate'); ?>
                <div>
                <?php
                echo CHtml::activeTextField($model,'tax_rate['.$row['id_tax_rule_rate'].'][rate]',array('size'=>5, 'maxlength'=>5, 'id'=>$container.'_tax_rate['.$row['id_tax_rule_rate'].'][rate]','onkeyup'=>'rewrite_number($(this).attr("id"));'));
				echo CHtml::activeHiddenField($model,'tax_rate['.$row['id_tax_rule_rate'].'][id_tax_rule_rate]',array('id'=>$container.'_tax_rate['.$row['id_tax_rule_rate'].'][id_tax_rule_rate]')); 
                ?>
                <br /><span id="<?php echo $container; ?>_tax_rate[<?php echo $row['id_tax_rule_rate'];?>][rate]_errorMsg" class="error"></span>
                </div>                
            </div>
        <?php
			$x=1;
		}
		?>
	    <div style="clear:both;"></div>    
        
</div>     
</div>