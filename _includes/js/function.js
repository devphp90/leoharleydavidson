if (jQuery) {
	// increase the default animation speed for the dialog effect
	jQuery.fx.speeds._default = 700;
	
	jQuery(function(){		
		//Clear or populate textfield when focus or blur
		var clearMePrevious = "";
	
		// clear input on focus
		jQuery('.clearMeFocus').focus(function(){
			if(jQuery(this).val()==jQuery(this).attr('title')){
				clearMePrevious = jQuery(this).val();
				jQuery(this).val("");
			}
		});
		
		// if field is empty afterward, add text again
		jQuery('.clearMeFocus').blur(function(){
			if(jQuery(this).val()==""){
				jQuery(this).val(clearMePrevious);
			}
		});
				
		//Follow menu when scroll
		jQuery.fn.scrollelement = function(options) {
			var defaults = { 
				'animate': false,
				'duration': 'fast',
				'easing': 'linear',
				'complete': function(){},
				'offset': 0
			};
			
			var options = jQuery.extend(defaults, options);
			
			return this.each(function() {
				var element = jQuery(this);
				var offset = element.offset().top - options.offset;
	
				var toScroll = 0;
				
				jQuery(window).scroll(function(){ 
				//alert(jQuery('#side_menu_filter').height());
				//alert((jQuery('#side_menu_filter').offset().top+jQuery('#side_menu_filter').height()));
				//alert(jQuery(window).scrollTop());
					var scroll = jQuery(window).scrollTop();
					if(((element.offset().top + element.height()) < scroll) || (element.offset().top > scroll)){
						
						if( scroll > offset){
							toScroll = scroll - offset;
							
						} else {
							toScroll = 0;
						}
						if(toScroll < 0){
							toScroll = 0;
						}
						
						
						if( options.animate == true ){
							element.stop().animate({"margin-top": toScroll + "px"}, options.duration, options.easing, function() {
								//alert(jQuery('#side_menu_filter').offset().top);
								//alert(jQuery('#side_menu_filter').height());
								//alert(jQuery('#wrap_top-footer').height());
								//alert(jQuery('#content').height()-jQuery('#side_menu_filter').height());
								var offset_bottom = (jQuery('#content').height()+jQuery('#all_top').height())-(element.height());
								var scroll_2 = element.offset().top;
								//alert(scroll_2);
								//alert(offset_bottom);
								if( scroll_2 < offset_bottom){
									toScroll_2 = scroll_2 - offset;
								}else{
									toScroll_2 = offset_bottom - 250;
								}
								if(toScroll_2 < 0){
									toScroll_2 = 0;
								}
								if( options.animate == true){
									element.animate({"margin-top": toScroll_2 + "px"}, options.duration, options.easing, options.complete );
								} else {
									element.stop().css("margin-top", toScroll_2 + "px");
								}
							});
						} else {
							element.stop().css("margin-top", toScroll + "px");
						}
					}
	
				});
				
			});
		}	
	});

}

//Open Review Dialog
function open_review (id,saved,url,page,language){
	jQuery.ajax({
		url: "/_includes/ajax/review.php?action=open",
		type: "POST",
		data: { "id":id, "return":url, "page":page, "language":language},
		success: function(data) {
			switch (data) {					
				case 'false':
					alert('Une erreur est survenue.');
					break;					
				default:
					jQuery("#dialog").html('').append(data);
					jQuery( "#dialog" ).dialog( "open" );
					if(saved!=""){
						form_review();
						if(saved!="login"){
							jQuery("#form_review_content").html('').append(saved);
						}
					}
					break;
			}								
		},
		error: function(e, xhr, settings, exception) {
			alert('Une erreur est survenue.');	
		}
	});			
}

function save_review (){
	jQuery.ajax({
		url: "/_includes/ajax/review.php?action=save",
		type: "POST",
		dataType: "json",
		data: jQuery("#form_review_rating").serialize(),
		cache: false,

		success: function(data) {
			jQuery("#review_title").removeClass("error");
			jQuery("#review_review").removeClass("error");
			jQuery(".rating_star_big_X5").removeClass("error");
			if (data.title) {					
				jQuery("#review_title").addClass("error");
			}
			if (data.review) {					
				jQuery("#review_review").addClass("error");
			}
			if (data.rated) {					
				jQuery(".rating_star_big_X5").addClass("error");
			}
			if(data.id){
				open_review(data.id,data.success);
			}

			//jQuery.scrollTo( { top:400, left:0 }, 2500, { easing:'elasout' });	
									
		},
		error: function(e, xhr, settings, exception) {
			alert('Une erreur est survenue.');	
		}
	});		
}

function rated_star(number,choose){
	var x;
	var remove_class;
	var add_class;
	if(choose==1){
		for(x=1;x<=number;x++){
			jQuery("#star_"+x).addClass("rating_star_full_big_choose");
		}
		for(x=number+1;x<=5;x++){
			jQuery("#star_"+x).removeClass("rating_star_full_big_choose");
		}
		jQuery("#rated").val(number);
	}else{
		if(jQuery("#star_"+number).is('.rating_star_full_big')){
			for(x=1;x<=number;x++){
				jQuery("#star_"+x).removeClass("rating_star_full_big");
			}
		}else{
			for(x=1;x<=number;x++){
				jQuery("#star_"+x).addClass("rating_star_full_big");
			}
		}
		
	}
}


function form_review(){
	
	if(jQuery('.form_review').is(":visible")){
		jQuery('.form_review').hide("fast");

	}else{
		jQuery('.form_review').show("fast");
	}	
}

function deleteconfirm(msg, urladress) {
	if (window.confirm(msg)) {
		location.href=urladress;
	}
}


function limitChars(textid, limit, infodiv){
	var text = jQuery('#'+textid).val();	
	var textlength = text.length;
	if(textlength > limit){
		jQuery('#'+textid).val(text.substr(0,limit));
		return false;
	}
	else{
		jQuery('#' + infodiv).html((limit - textlength));
		return true;
	}
}

function rewrite_number(field_id){
	//Put the back slashes in the name to use with jquery
	field_id = "#"+field_id.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
	
	var new_number = jQuery(field_id).val();
	//Replace spaces by -
	new_number = new_number.replace(",",".");
	
	new_number = new_number.replace(/[^0-9.]/g, "");

	jQuery(field_id).val(new_number);	
}


function number_format (number, decimals, dec_point, thousands_sep) {
    // http://kevin.vanzonneveld.net
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +      input by: Amirouche
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    // *    example 13: number_format('1 000,50', 2, '.', ' ');
    // *    returns 13: '100 050.00'
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function clear_form(form){
	jQuery(':input','#' + form) 
   .not(':button, :submit, :reset, :hidden, :radio, :checkbox') 
   .val('');
   jQuery(':input','#' + form) 
   .removeAttr('checked') 
   .removeAttr('selected');
}
