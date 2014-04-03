<div style="border-bottom: solid 1px #CCC; padding-bottom: 10px; margin-bottom: 10px; width:100%" class="hideprint">
   
    <div class="fl"><img src="/_images/<?php echo $config_site['company_logo_print_file'];?>" alt="<?php echo $config_site['site_name'];?>" name="logo" id="logo" height="70" /></div>
    <div class="fr" style="padding-top:8px; text-align:right;">
        <?php echo $config_site['site_name'];?><br />
        <?php echo $config_site['company_address']?' ' . $config_site['company_address']:'';?>
        <?php echo $config_site['company_city']?'<br />' . $config_site['company_city']:'';?>
        <?php
        $country_name = '';
        $state_name = ''; 
        if($config_site['company_country_code']){
            $query = 'SELECT 
                country_description.name
                FROM country_description
                WHERE country_description.country_code = "'.$mysqli->escape_string($config_site['company_country_code']).'" AND country_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"';
        
                if ($result = $mysqli->query($query)) {
                    $row = $result->fetch_assoc();
                    $country_name = $row['name'];						
                }
                $result->free();
                
        }
        if($config_site['company_state_code']){
            $query = 'SELECT 
                state_description.name
                FROM state_description
                WHERE state_description.state_code = "'.$mysqli->escape_string($config_site['company_state_code']).'" AND state_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"';
        
                if ($result = $mysqli->query($query)) {
                    $row = $result->fetch_assoc();
                    $state_name = $row['name'];						
                }
                $result->free();
        }
        echo $state_name?' ' . $state_name:'';?>
        <?php echo $country_name?' ' . $country_name:'';?>
        <?php echo $config_site['company_zip']?' ' . $config_site['company_zip']:'';?>
        <br />
        <?php echo $config_site['company_telephone']?'<strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_T').'</strong> ' . $config_site['company_telephone']:'';?>
        <?php echo $config_site['company_fax']?' <strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_F').'</strong> ' . $config_site['company_fax']:'';?>
        <?php echo $config_site['company_email']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_E').'</strong> <a href="mailto:' . $config_site['company_email'].'">' . $config_site['company_email'] . '</a>':'';?>
    </div>
    <div class="cb"></div>
  </div>