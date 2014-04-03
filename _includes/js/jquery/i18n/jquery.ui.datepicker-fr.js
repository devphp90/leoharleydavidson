/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood{at}iinet.com.au),
              Stéphane Nahmani (sholby@sholby.net),
              Stéphane Raimbault <stephane.raimbault@gmail.com> */
jQuery(function(jQuery){
	jQuery.datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: 'Précédent',
		nextText: 'Suivant',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
		'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
		monthNamesShort: ['Janv.','Févr.','Mars','Avril','Mai','Juin',
		'Juil.','Août','Sept.','Oct.','Nov.','Déc.'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'],
		dayNamesMin: ['D','L','M','M','J','V','S'],
		weekHeader: 'Sem.',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
		};
	jQuery.datepicker.setDefaults(jQuery.datepicker.regional['fr']);
	
	jQuery.timepicker.regional['fr'] = {
		timeOnlyTitle: 'Choisissez l\'heure',
		timeText: 'Heure',
		hourText: 'Heure',
		minuteText: 'Minute',
		secondText: 'Seconde',
		millisecText: 'Miliseconde',
		closeText: 'Fermer',
		currentText: 'Actuel',
		ampm: false
	};
	jQuery.timepicker.setDefaults(jQuery.timepicker.regional['fr']);
});
