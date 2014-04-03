<div class="page-title">
    <h1><?php echo $step. ' - ' .language('global', 'STEP_'.$step)?></h1>
</div>
<!-- 
<div style="/*padding: 15px; background-color:#FFF; border-bottom: 1px solid #E5E5E5*/">
    <div style="margin: 0 auto;" id="step">
        <div style="float:left;" id="width_step">
        <div style="font-size:20px; color:#333; padding-top: 2px; margin-right:10px; float:left; font-weight:bold;"><?php echo language('global', 'STEP_START')?></div>
        <?php
        $number = 1;
		if(!$config_site['enable_shipping']){
			$step--; 
		}
		?>
        <div class="step<?php echo(( $step > $number - 1 )?'':' step_disabled');?>"><?php echo $number;?></div><div class="step_text<?php echo(( $step > $number - 1 )?' step_text_enabled':' step_text_disabled');?><?php echo(( $step == $number )?' step_text_in':'');?>"><?php echo language('global', 'STEP_1')?></div>
        <?php
        $number++;
		if($config_site['enable_shipping']){
		?>
        <div class="step<?php echo(( $step > $number - 1 )?'':' step_disabled');?>"><?php echo $number;?></div><div class="step_text<?php echo(( $step > $number - 1 )?' step_text_enabled':' step_text_disabled');?><?php echo(( $step == $number )?' step_text_in':'');?>"><?php echo language('global', 'STEP_2')?></div>
        <?php
        $number++;
		}
		?>
        <div class="step<?php echo(( $step > $number - 1 )?'':' step_disabled');?>"><?php echo $number;?></div><div class="step_text<?php echo(( $step > $number - 1 )?' step_text_enabled':' step_text_disabled');?><?php echo(( $step == $number )?' step_text_in':'');?>"><?php echo language('global', 'STEP_3')?></div>
        <?php
        $number++;
		?>
        <div class="step<?php echo(( $step > $number - 1 )?'':' step_disabled');?>"><?php echo $number;?></div><div class="step_text<?php echo(( $step > $number - 1 )?' step_text_enabled':' step_text_disabled');?><?php echo(( $step == $number )?' step_text_in':'');?>"><?php echo language('global', 'STEP_4')?></div>
        <?php
        $number++;
		?>
        <div class="step<?php echo(( $step > $number - 1 )?'':' step_disabled');?>"><?php echo $number;?></div><div class="step_text<?php echo(( $step > $number - 1 )?' step_text_enabled':' step_text_disabled');?><?php echo(( $step == $number )?' step_text_in':'');?>"><?php echo language('global', 'STEP_5')?></div>
        <div class="cb"></div>
        </div>
        <div class="cb"></div>
    </div>
</div>
<script type="text/javascript">
// To center the Steps
jQuery('#step').width((jQuery('#width_step').width())+10);
</script>
 -->