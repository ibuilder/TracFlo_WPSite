/*global patch: false*/
var ajax = {
	x: function()
	{
		if ( 'undefined' !== typeof XMLHttpRequest ) {
			return new XMLHttpRequest();
		}
	},
	send: function( url, callback, method, data, sync )
	{
		// Async by default
		if ( undefined === sync ) { sync = true; }

		// Get ajax object and open connection
		var x = ajax.x();
		x.open(method, url, sync);

		// Add success callback
		x.onreadystatechange = function() {
			if ( 4 === x.readyState ) {
				callback( x.responseText );
			}
		};

		// Add post headers
		if ( 'POST' === method ) {
			x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		}

		// Send the data
		x.send(data);
	},
	get: function( url, data, callback, sync )
	{
		var query = [];
		for ( var key in data )
		{
			query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
		}
		ajax.send(url + '?' + query.join('&'), callback, 'GET', null, sync);
	},
	post: function( url, data, callback, sync )
	{
		var query = [];
		for ( var key in data )
		{
			query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
		}
		ajax.send(url, callback, 'POST', query.join('&'), sync);
	}
};
!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.SignaturePad=e()}(this,function(){"use strict";function t(t,e,i){this.x=t,this.y=e,this.time=i||(new Date).getTime()}function e(t,e,i,o){this.startPoint=t,this.control1=e,this.control2=i,this.endPoint=o}function i(t,e,i){var o,n,s,r=null,h=0;i||(i={});var a=function(){h=!1===i.leading?0:Date.now(),r=null,s=t.apply(o,n),r||(o=n=null)};return function(){var c=Date.now();h||!1!==i.leading||(h=c);var d=e-(c-h);return o=this,n=arguments,d<=0||d>e?(r&&(clearTimeout(r),r=null),h=c,s=t.apply(o,n),r||(o=n=null)):r||!1===i.trailing||(r=setTimeout(a,d)),s}}function o(t,e){var n=this,s=e||{};this.velocityFilterWeight=s.velocityFilterWeight||.7,this.minWidth=s.minWidth||.5,this.maxWidth=s.maxWidth||2.5,this.throttle="throttle"in s?s.throttle:16,this.minDistance="minDistance"in s?s.minDistance:5,this.throttle?this._strokeMoveUpdate=i(o.prototype._strokeUpdate,this.throttle):this._strokeMoveUpdate=o.prototype._strokeUpdate,this.dotSize=s.dotSize||function(){return(this.minWidth+this.maxWidth)/2},this.penColor=s.penColor||"black",this.backgroundColor=s.backgroundColor||"rgba(0,0,0,0)",this.onBegin=s.onBegin,this.onEnd=s.onEnd,this._canvas=t,this._ctx=t.getContext("2d"),this.clear(),this._handleMouseDown=function(t){1===t.which&&(n._mouseButtonDown=!0,n._strokeBegin(t))},this._handleMouseMove=function(t){n._mouseButtonDown&&n._strokeMoveUpdate(t)},this._handleMouseUp=function(t){1===t.which&&n._mouseButtonDown&&(n._mouseButtonDown=!1,n._strokeEnd(t))},this._handleTouchStart=function(t){if(1===t.targetTouches.length){var e=t.changedTouches[0];n._strokeBegin(e)}},this._handleTouchMove=function(t){t.preventDefault();var e=t.targetTouches[0];n._strokeMoveUpdate(e)},this._handleTouchEnd=function(t){t.target===n._canvas&&(t.preventDefault(),n._strokeEnd(t))},this.on()}return t.prototype.velocityFrom=function(t){return this.time!==t.time?this.distanceTo(t)/(this.time-t.time):1},t.prototype.distanceTo=function(t){return Math.sqrt(Math.pow(this.x-t.x,2)+Math.pow(this.y-t.y,2))},t.prototype.equals=function(t){return this.x===t.x&&this.y===t.y&&this.time===t.time},e.prototype.length=function(){for(var t=0,e=void 0,i=void 0,o=0;o<=10;o+=1){var n=o/10,s=this._point(n,this.startPoint.x,this.control1.x,this.control2.x,this.endPoint.x),r=this._point(n,this.startPoint.y,this.control1.y,this.control2.y,this.endPoint.y);if(o>0){var h=s-e,a=r-i;t+=Math.sqrt(h*h+a*a)}e=s,i=r}return t},e.prototype._point=function(t,e,i,o,n){return e*(1-t)*(1-t)*(1-t)+3*i*(1-t)*(1-t)*t+3*o*(1-t)*t*t+n*t*t*t},o.prototype.clear=function(){var t=this._ctx,e=this._canvas;t.fillStyle=this.backgroundColor,t.clearRect(0,0,e.width,e.height),t.fillRect(0,0,e.width,e.height),this._data=[],this._reset(),this._isEmpty=!0},o.prototype.fromDataURL=function(t){var e=this,i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},o=new Image,n=i.ratio||window.devicePixelRatio||1,s=i.width||this._canvas.width/n,r=i.height||this._canvas.height/n;this._reset(),o.src=t,o.onload=function(){e._ctx.drawImage(o,0,0,s,r)},this._isEmpty=!1},o.prototype.toDataURL=function(t){var e;switch(t){case"image/svg+xml":return this._toSVG();default:for(var i=arguments.length,o=Array(i>1?i-1:0),n=1;n<i;n++)o[n-1]=arguments[n];return(e=this._canvas).toDataURL.apply(e,[t].concat(o))}},o.prototype.on=function(){this._handleMouseEvents(),this._handleTouchEvents()},o.prototype.off=function(){this._canvas.removeEventListener("mousedown",this._handleMouseDown),this._canvas.removeEventListener("mousemove",this._handleMouseMove),document.removeEventListener("mouseup",this._handleMouseUp),this._canvas.removeEventListener("touchstart",this._handleTouchStart),this._canvas.removeEventListener("touchmove",this._handleTouchMove),this._canvas.removeEventListener("touchend",this._handleTouchEnd)},o.prototype.isEmpty=function(){return this._isEmpty},o.prototype._strokeBegin=function(t){this._data.push([]),this._reset(),this._strokeUpdate(t),"function"==typeof this.onBegin&&this.onBegin(t)},o.prototype._strokeUpdate=function(t){var e=t.clientX,i=t.clientY,o=this._createPoint(e,i),n=this._data[this._data.length-1],s=n&&n[n.length-1],r=s&&o.distanceTo(s)<this.minDistance;if(!s||!r){var h=this._addPoint(o),a=h.curve,c=h.widths;a&&c&&this._drawCurve(a,c.start,c.end),this._data[this._data.length-1].push({x:o.x,y:o.y,time:o.time,color:this.penColor})}},o.prototype._strokeEnd=function(t){var e=this.points.length>2,i=this.points[0];if(!e&&i&&this._drawDot(i),i){var o=this._data[this._data.length-1],n=o[o.length-1];i.equals(n)||o.push({x:i.x,y:i.y,time:i.time,color:this.penColor})}"function"==typeof this.onEnd&&this.onEnd(t)},o.prototype._handleMouseEvents=function(){this._mouseButtonDown=!1,this._canvas.addEventListener("mousedown",this._handleMouseDown),this._canvas.addEventListener("mousemove",this._handleMouseMove),document.addEventListener("mouseup",this._handleMouseUp)},o.prototype._handleTouchEvents=function(){this._canvas.style.msTouchAction="none",this._canvas.style.touchAction="none",this._canvas.addEventListener("touchstart",this._handleTouchStart),this._canvas.addEventListener("touchmove",this._handleTouchMove),this._canvas.addEventListener("touchend",this._handleTouchEnd)},o.prototype._reset=function(){this.points=[],this._lastVelocity=0,this._lastWidth=(this.minWidth+this.maxWidth)/2,this._ctx.fillStyle=this.penColor},o.prototype._createPoint=function(e,i,o){var n=this._canvas.getBoundingClientRect();return new t(e-n.left,i-n.top,o||(new Date).getTime())},o.prototype._addPoint=function(t){var i=this.points,o=void 0;if(i.push(t),i.length>2){3===i.length&&i.unshift(i[0]),o=this._calculateCurveControlPoints(i[0],i[1],i[2]);var n=o.c2;o=this._calculateCurveControlPoints(i[1],i[2],i[3]);var s=o.c1,r=new e(i[1],n,s,i[2]),h=this._calculateCurveWidths(r);return i.shift(),{curve:r,widths:h}}return{}},o.prototype._calculateCurveControlPoints=function(e,i,o){var n=e.x-i.x,s=e.y-i.y,r=i.x-o.x,h=i.y-o.y,a={x:(e.x+i.x)/2,y:(e.y+i.y)/2},c={x:(i.x+o.x)/2,y:(i.y+o.y)/2},d=Math.sqrt(n*n+s*s),l=Math.sqrt(r*r+h*h),u=a.x-c.x,v=a.y-c.y,p=l/(d+l),_={x:c.x+u*p,y:c.y+v*p},y=i.x-_.x,f=i.y-_.y;return{c1:new t(a.x+y,a.y+f),c2:new t(c.x+y,c.y+f)}},o.prototype._calculateCurveWidths=function(t){var e=t.startPoint,i=t.endPoint,o={start:null,end:null},n=this.velocityFilterWeight*i.velocityFrom(e)+(1-this.velocityFilterWeight)*this._lastVelocity,s=this._strokeWidth(n);return o.start=this._lastWidth,o.end=s,this._lastVelocity=n,this._lastWidth=s,o},o.prototype._strokeWidth=function(t){return Math.max(this.maxWidth/(t+1),this.minWidth)},o.prototype._drawPoint=function(t,e,i){var o=this._ctx;o.moveTo(t,e),o.arc(t,e,i,0,2*Math.PI,!1),this._isEmpty=!1},o.prototype._drawCurve=function(t,e,i){var o=this._ctx,n=i-e,s=Math.floor(t.length());o.beginPath();for(var r=0;r<s;r+=1){var h=r/s,a=h*h,c=a*h,d=1-h,l=d*d,u=l*d,v=u*t.startPoint.x;v+=3*l*h*t.control1.x,v+=3*d*a*t.control2.x,v+=c*t.endPoint.x;var p=u*t.startPoint.y;p+=3*l*h*t.control1.y,p+=3*d*a*t.control2.y,p+=c*t.endPoint.y;var _=e+c*n;this._drawPoint(v,p,_)}o.closePath(),o.fill()},o.prototype._drawDot=function(t){var e=this._ctx,i="function"==typeof this.dotSize?this.dotSize():this.dotSize;e.beginPath(),this._drawPoint(t.x,t.y,i),e.closePath(),e.fill()},o.prototype._fromData=function(e,i,o){for(var n=0;n<e.length;n+=1){var s=e[n];if(s.length>1)for(var r=0;r<s.length;r+=1){var h=s[r],a=new t(h.x,h.y,h.time),c=h.color;if(0===r)this.penColor=c,this._reset(),this._addPoint(a);else if(r!==s.length-1){var d=this._addPoint(a),l=d.curve,u=d.widths;l&&u&&i(l,u,c)}}else{this._reset();o(s[0])}}},o.prototype._toSVG=function(){var t=this,e=this._data,i=this._canvas,o=Math.max(window.devicePixelRatio||1,1),n=i.width/o,s=i.height/o,r=document.createElementNS("http://www.w3.org/2000/svg","svg");r.setAttributeNS(null,"width",i.width),r.setAttributeNS(null,"height",i.height),this._fromData(e,function(t,e,i){var o=document.createElement("path");if(!(isNaN(t.control1.x)||isNaN(t.control1.y)||isNaN(t.control2.x)||isNaN(t.control2.y))){var n="M "+t.startPoint.x.toFixed(3)+","+t.startPoint.y.toFixed(3)+" C "+t.control1.x.toFixed(3)+","+t.control1.y.toFixed(3)+" "+t.control2.x.toFixed(3)+","+t.control2.y.toFixed(3)+" "+t.endPoint.x.toFixed(3)+","+t.endPoint.y.toFixed(3);o.setAttribute("d",n),o.setAttribute("stroke-width",(2.25*e.end).toFixed(3)),o.setAttribute("stroke",i),o.setAttribute("fill","none"),o.setAttribute("stroke-linecap","round"),r.appendChild(o)}},function(e){var i=document.createElement("circle"),o="function"==typeof t.dotSize?t.dotSize():t.dotSize;i.setAttribute("r",o),i.setAttribute("cx",e.x),i.setAttribute("cy",e.y),i.setAttribute("fill",e.color),r.appendChild(i)});var h='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 '+n+" "+s+'" width="'+n+'" height="'+s+'">',a=r.innerHTML;if(void 0===a){var c=document.createElement("dummy"),d=r.childNodes;c.innerHTML="";for(var l=0;l<d.length;l+=1)c.appendChild(d[l].cloneNode(!0));a=c.innerHTML}var u=h+a+"</svg>";return"data:image/svg+xml;base64,"+btoa(u)},o.prototype.fromData=function(t){var e=this;this.clear(),this._fromData(t,function(t,i){return e._drawCurve(t,i.start,i.end)},function(t){return e._drawDot(t)}),this._data=t},o.prototype.toData=function(){return this._data},o});

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
var navToggle,
	container,
	navOverlay = document.createElement('div'),
	i,l;

function nav_toggleDrawer(e) {
	e.preventDefault();
	if (navToggle) {
		navToggle.classList.toggle('is-active');
	}
	document.body.classList.toggle('is-navOpen');
	//this.blur();
}

document.addEventListener('DOMContentLoaded', function() {
	navToggle = document.getElementById('NavToggle');
	container = document.getElementById('Page');

	// Click to open
	if (navToggle) {
		navToggle.addEventListener('click', nav_toggleDrawer);
	}

	// Add overlay
	if (container && navOverlay) {
		navOverlay.classList.add('NavOverlay');
		navOverlay.addEventListener('click', nav_toggleDrawer);
		container.appendChild(navOverlay);
	}

});
