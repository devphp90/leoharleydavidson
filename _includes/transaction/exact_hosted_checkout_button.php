<form action="/_includes/transaction/<?php echo $payment_gateway_page;?>" method="post" id="payment_method_form_0" name="payment_method_form_0" class="payment_methods" style="display:none;">
<input name="payment_method" type="hidden" value="6" />
<ul>
  <li>
   <label><?php echo language('cart/step_payment', 'LABEL_COMMENTS');?></label>
   <textarea name="trnComments" id="trnComments" style="width:180px; height:60px;"><?php echo $trnComments;?></textarea>
  </li>
</ul>        
<?php echo language('cart/step_payment','MESSAGE_PLACE_ORDER_HOSTED_CHECKOUT'); ?>
<div style="margin-top:20px">
    <div class="button_previous_step fl"><input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="previous_step" name="btn_previous_step" onclick="document.location.href='step_validation'" /></div>
    <div class="button_next_step fr"><input type="button" value="<?php echo language('global', 'BTN_CHECKOUT');?>" class="next_step button_checkout" name="submit_order" onclick="javascript:please_wait_display(this.form.id);" /></div>
    <div class="cb"></div>
</div>     
</form> 