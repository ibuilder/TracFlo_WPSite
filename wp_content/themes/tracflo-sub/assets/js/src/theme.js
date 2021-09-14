/**
 * Get Viewport Dimensions
 * returns object with viewport dimensions to match css in width and height properties
 * ( source: http://andylangton.co.uk/blog/development/get-viewport-size-width-and-height-javascript )
 */
function updateViewportDimensions() {
	var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;
	return { width:x,height:y };
}
// setting the viewport width
var viewport = updateViewportDimensions();

/**
 * Throttle Resize-triggered Events
 * Wrap your actions in this function to throttle the frequency of firing them off, for better performance, esp. on mobile.
 * ( source: http://stackoverflow.com/questions/2854407/javascript-jquery-window-resize-how-to-fire-after-the-resize-is-completed )
 */
var waitForFinalEvent = (function () {
	var timers = {};
	return function (callback, ms, uniqueId) {
		if (!uniqueId) { uniqueId = "Don't call this twice without a uniqueId"; }
		if (timers[uniqueId]) { clearTimeout (timers[uniqueId]); }
		timers[uniqueId] = setTimeout(callback, ms);
	};
})();

// how long to wait before deciding the resize has stopped, in ms. Around 50-100 should work ok.
var timeToWaitForLast = 100;


function getProjectNextNumber() {
	
}

document.addEventListener('DOMContentLoaded', function() {

	// Hack to add new client link to project
	jQuery('.acf-field-58d4ca46ac178 .acf-label > label').append('<a href="/add-client/" target="_blank" style="margin-left:20px;">+ Add new client</a>');

	if ( ! patch.mobile_breakpoint ) {
		patch.mobile_breakpoint = 768;
	}

	jQuery('.datepicker').datepicker({
		dateFormat : 'yy-mm-dd',
	});

	jQuery('.m-signature-pad').each(function() {
		var signatureWrap = this;
		var canvas = signatureWrap.querySelector("canvas");
		var clearButton = signatureWrap.querySelector("[data-action=clear]");
		var input = signatureWrap.querySelector("input");
		
		var signaturePad = new SignaturePad(canvas);
		
		// Returns signature image as data URL (see https://mdn.io/todataurl for the list of possible parameters)
		//signaturePad.toDataURL(); // save image as PNG
		//signaturePad.toDataURL("image/jpeg"); // save image as JPEG
		//signaturePad.toDataURL("image/svg+xml"); // save image as SVG
		
		// Draws signature image from data URL.
		// NOTE: This method does not populate internal data structure that represents drawn signature. Thus, after using #fromDataURL, #toData won't work properly.
		//signaturePad.fromDataURL("data:image/png;base64,iVBORw0K...");
		
		// Returns signature image as an array of point groups
		//var data = signaturePad.toData();
		
		// Draws signature image from an array of point groups
		//signaturePad.fromData(data);
		
		// Clears the canvas
		//signaturePad.clear();
	
		//function resizeCanvas() {
		//}
		
		jQuery(window).on("resize", function() {
			var ratio =  Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);
			signaturePad.clear(); // otherwise isEmpty() might return incorrect value
		});
	
		//resizeCanvas();
		
		// Returns true if canvas is empty, otherwise returns false
		//signaturePad.isEmpty();
		
		// Unbinds all event handlers
		//signaturePad.off();
		
		// Rebinds all event handlers
		//signaturePad.on();
	
		jQuery(clearButton).on("click", function(e) {
			e.preventDefault();
			signaturePad.clear();
			jQuery(input).val('');
		});
	
		jQuery(canvas).on("touchend mouseup mouseleave", function(event) {
			if ( signaturePad.isEmpty() ) {
				jQuery(input).val('');
			} else {
				jQuery(input).val(signaturePad.toDataURL());
			}
		});
	});

	jQuery('#record_payment_button').on('click', function(e) {
		e.preventDefault();
		jQuery('#payment_form').slideToggle();
	});
	jQuery('#payment_form .toggle-cancel-form').on('click', function(e) {
		e.preventDefault();
		jQuery('#payment_form').slideUp();
	});

	jQuery('#send_ticket_button').on('click', function(e) {
		e.preventDefault();
		jQuery('#send_ticket_form').slideToggle();
	});
	jQuery('.toggle-cancel-form').on('click', function(e) {
		e.preventDefault();
		jQuery(this).closest('.inline_form').slideUp();
	});

	jQuery('#approve-ticket').on('click', function(e) {
		e.preventDefault();
		jQuery('#approve_ticket_form').slideToggle(400, function() {
			jQuery(window).trigger('resize');
		});
		jQuery('#reject_ticket_form').slideUp();
		jQuery('#revise_ticket_form').slideUp();
	});
	jQuery('#reject-ticket').on('click', function(e) {
		e.preventDefault();
		jQuery('#approve_ticket_form').slideUp();
		jQuery('#reject_ticket_form').slideToggle(400, function() {
			jQuery(window).trigger('resize');
		});
		jQuery('#revise_ticket_form').slideUp();
	});
	jQuery('#revise-ticket').on('click', function(e) {
		e.preventDefault();
		jQuery('#approve_ticket_form').slideUp();
		jQuery('#reject_ticket_form').slideUp();
		jQuery('#revise_ticket_form').slideToggle();
	});

/** /
	jQuery('.m-signature-pad').each(function(){

		var wrapper = this,
		    clearButton = wrapper.querySelector("[data-action=clear]"),
		    canvas = wrapper.querySelector("canvas"),
		    input = wrapper.querySelector("input"),
		    signaturePad;

		// Adjust canvas coordinate space taking into account pixel ratio,
		// to make it look crisp on mobile devices.
		// This also causes canvas to be cleared.
		function resizeCanvas() {
		    var ratio = window.devicePixelRatio || 1;
		    canvas.width = canvas.offsetWidth * ratio;
		    canvas.height = canvas.offsetHeight * ratio;
		    canvas.getContext("2d").scale(ratio, ratio);
		}
		//if (! signature.is_mobile )
		//{
			window.onresize = resizeCanvas;
		//}
		resizeCanvas();

		signaturePad = new SignaturePad(canvas);
console.log('jQuery(input)', jQuery(input));
console.log('canvas', canvas);
		var data = jQuery(input).val();
		if ( data.length ) {
			console.log(data);
			signaturePad.fromDataURL( data );
		}
/** /
		jQuery(clearButton).on("click", function(e) {
			e.preventDefault();
		    signaturePad.clear();
		    jQuery(input).val('');
		});
/** /
		jQuery(canvas).on("touchend mouseup mouseleave", function(event) {
			if ( signaturePad.isEmpty() ) {
				jQuery(input).val('');
			} else {
				jQuery(input).val(signaturePad.toDataURL());
			}
		});
		
	});

	/**
	 * Refine conditional adjustments for Lump Sum, Rates vs Manual Total
	 */
	function trac_updateCoTypeConditional(value) {
		if ('total' === value) {
			jQuery('.acf-field[data-name="rate"]').hide();
		} else {
			jQuery('.acf-field[data-name="rate"]').show();
		}
	}
	jQuery('.acf-field-58fe1d38f3774 [type="radio"]').each(function() {
		if (this.checked) { trac_updateCoTypeConditional(this.value); }
		jQuery(this).on('change', function() {
			trac_updateCoTypeConditional(this.value);
		});
	});

	/**
	 * Update Ticket Number by Project
	 */

	//next_ticket_number
	// Update next ticket number based on project
	if ('/add-ticket/' === window.location.pathname && -1 < jQuery('[data-name="project"] select').length) {
		var tFieldNumber = document.getElementById('acf-field_59126d9b699ae'),
			tFieldProject = document.getElementById('acf-field_58d4c271742df');
		if (tFieldNumber && tFieldProject) {
			if (tFieldProject.value) {
				jQuery.ajax({
					url: patch.ajaxurl,
					dataType: 'json',
					method: 'POST',
					data: {
						action: 'next_ticket_number',
						projectName: tFieldProject.value,
					},
					complete: function() {},
					success: function(data) {
						tFieldNumber.value = (data.number) ? data.number : 1;
					},
					error: function(jXHR, textStatus, errorThrown) {
						console.log(errorThrown);
					},
				});
			}
			jQuery(tFieldProject).on('change', function() {
				 jQuery.ajax({
					url: patch.ajaxurl,
					dataType: 'json',
					method: 'POST',
					data: {
						action: 'next_ticket_number',
						projectId: this.value,
					},
					complete: function() {},
					success: function(data) {
						tFieldNumber.value = (data.number) ? data.number : 1;
					},
					error: function(jXHR, textStatus, errorThrown) {
						console.log(errorThrown);
					},
				});
			});
		}
	}

/** /
	.chosen().change(function(event) {
		console.log('select2 change', event, jQuery(event.target).val());
	});
/**/
	jQuery('.filter-menu > button').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var pos = jQuery(this).position().top + jQuery(this).height();
		jQuery(this)
			.toggleClass('is-active')
			.next()
				.css({top: pos})
				.toggleClass('is-visible');
	});
//	jQuery('.filter-projects a').on('click', function(e) {
//		e.stopPropagation();
//	}


	jQuery(document).on('click', function() {
		jQuery('.filter-menu > button')
			.removeClass('is-active')
			.next()
				.removeClass('is-visible');
	});


	/**
	 * Adds the screen reader text to the icon's title so it will show on hover
	 */
	function trac_addScreenReaderHover()
	{
		var icons = document.querySelectorAll('span[aria-hidden=true]');
		for ( var i=0, len=icons.length; i<len; i++ )
		{
			var icon_parent = icons[i].parentNode.querySelector('.screen-reader-text');
			if ( icon_parent ) {
				var icon_text = icon_parent.innerHTML;
				icons[i].setAttribute('title', icon_text);
			}
		}
	}
	trac_addScreenReaderHover();


	/**
	 * Open share links in new window & select all on the share permalink field
	 */
	function trac_shareLinks()
	{
		var shareLinks = document.getElementsByClassName('share-links'),
			i,
			shareSelectClick = function(e) {
				this.select();
			},
			shareAnchorClick = function(e) {
				e.preventDefault();
				window.open( this.getAttribute('href'), 'Share', 'height=470, width=550, top='+(window.height/2 - 225) +', left='+window.width/2 +', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
			};
		if ( shareLinks )
		{
			for ( i=0; i<shareLinks.length; i++ )
			{
				var shareAnchors = shareLinks[i].getElementsByTagName('a'),
					shareUrl     = shareLinks[i].querySelector('.share-url');

				if ( shareUrl )
				{
					shareUrl.addEventListener('click', shareSelectClick);
				}

				if ( shareAnchors )
				{
					for ( i=0; i<shareAnchors.length; i++ )
					{
						shareAnchors[i].addEventListener('click', shareAnchorClick);
					}
				}
			}
		}
	}
	trac_shareLinks();


	/**
	 * Cache and declare variables
	 * /
	var window_height     = window.innerHeight,
		window_width      = window.innerWidth,
		doc_top           = document.scrollTop,
		last_top          = doc_top,
		cur_top           = 0,
		cur_pos           = window_height - header_height,
		scrollParent      = document.documentElement.scrollTop ? document.documentElement : document.body;


	/**
	 * Resize image slides to fill window height
	 * /
	function resizeHandler()
	{
		// Update variables
		window_height     = window.innerHeight;
		window_width      = window.innerWidth;
		header_height     = header.offsetHeight;
		cur_pos           = window_height - header_height;
	}
	resizeHandler();
	window.addEventListener('resize', function() {
		waitForFinalEvent(resizeHandler, timeToWaitForLast, 'mainresize');
	});


	/**
	 * Checks the scroll position to see if the header should be fixed or not
	 * /
	function scrollHandler()
	{
		doc_top = scrollParent.scrollTop;
		cur_top = cur_pos - doc_top; // Determines the position of the header

		last_top = doc_top;
	};
	scrollHandler();
	window.addEventListener('scroll', function() {
		waitForFinalEvent(scrollHandler, timeToWaitForLast, 'mainscroll');
	}, false);
	/**/

});