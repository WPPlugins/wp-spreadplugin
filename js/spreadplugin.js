/**
 * Plugin Name: WP-Spreadplugin
 * Plugin URI: http://wordpress.org/extend/plugins/wp-spreadplugin/
 * Description: This plugin uses the Spreadshirt API to list articles and let your customers order articles of your Spreadshirt shop using Spreadshirt order process.
 * Version: 3.9.39
 * Author: Thimo Grauerholz
 */

var ajax_object;

jQuery(function($) {

	var sep = '?';
	var prod = getParameterByName('productCategory');
	var prod2 = getParameterByName('productSubCategory');
	var sor = getParameterByName('articleSortBy');
	var paged = getParameterByName('pagesp');
	var infiniteItemSel = '.spreadplugin-article';
	var appearance = '';
	var view = '';
	var _instance;
//	var sid = document.cookie.match(/PHPSESSID=[^;]+/);
	//var usePrettyUrl = ajax_object.prettyUrl;
	var usePrettyUrl = 0;


	if (ajax_object.display == 1) {
		infiniteItemSel = '.spreadplugin-designs';
	}

	if (ajax_object.pageLink.indexOf('?') > -1) {
		sep = '&';
	}
	
	
	$('.spreadplugin-cart').hide();
	
	// hide cart when user clicks outside
	$(document).click(function(e) {
	    if (e.target.className != 'spreadplugin-checkout-link' && e.target.className != 'spreadplugin-checkout-link button' && $('.spreadplugin-cart').is(':visible') && !$('.spreadplugin-cart').find(e.target).length) {
    	    $(".spreadplugin-cart").hide();
    	}
	});
	
	// stops hover lose when hovering min-view select
	$(".spreadplugin-items select").hover(function(e){
    	e.stopPropagation();
	});

	
	/*
	 * change article color and view
	 */
	function bindClick() {
		// avoid double firing events
		$('.spreadplugin-article .colors li,.spreadplugin-article-detail .colors li').unbind();
		$('.spreadplugin-article .views li,.spreadplugin-article-detail .views li').unbind();
		$('.spreadplugin-article .description-wrapper div.header,.spreadplugin-article-detail .description-wrapper div.header').unbind();
		$('.spreadplugin-article .product-description-wrapper div.header,.spreadplugin-article-detail .product-description-wrapper div.header').unbind();
		$('.spreadplugin-design .image-wrapper').unbind();
		$('.spreadplugin-article form,.spreadplugin-article-detail form').unbind();
		$('.spreadplugin-article .edit-wrapper a,.spreadplugin-article-detail .edit-wrapper a').unbind();
		$('.spreadplugin-article .edit-wrapper-integrated a,.spreadplugin-article-detail .edit-wrapper-integrated a').unbind();
		$('.spreadplugin-article .details-wrapper a,.spreadplugin-article-detail .details-wrapper a').unbind();
		$('.spreadplugin-article .image-wrapper,.spreadplugin-article-detail .image-wrapper').unbind();
		$('.spreadplugin-article .shipping-window,.spreadplugin-article-detail .shipping-window').unbind();

		$('.spreadplugin-article .colors li,.spreadplugin-article-detail .colors li').click(function() {	
	
			var id = '#' + $(this).closest('.spreadplugin-article,.spreadplugin-article-detail').attr('id');
			var image = $(id + ' img.preview');
			var src = image.attr('src');
			var srczoom = image.attr('data-zoom-image');
			var srczoomData = image.data('elevateZoom');
			
			appearance = $(this).attr('value');
			view = 	$(id + ' #view').val();						
			$(id + ' #appearance').val(appearance);

			image.attr('src',image.attr('src').replace(/\,appearanceId=(\d+)/g, '') + ',appearanceId=' + appearance);
			
			image.attr('data-zoom-image',srczoom.replace(/\,appearanceId=(\d+)/g,'').replace(/\,viewId=(\d+)/g,'') + ',appearanceId=' + appearance + ',viewId=' + view);
			
			$(id + ' img.previewview').each(function() {
				var originalsrc = $(this).attr('src');
				$(this).attr('src',originalsrc.replace(/\,appearanceId=(\d+)/g,'') + ',appearanceId=' + appearance);
			});

			if (srczoomData) {
				var url = srczoomData.imageSrc.replace(	/\,appearanceId=(\d+)/g, '').replace(/\,viewId=(\d+)/g, '');
				url = url + ',appearanceId=' + appearance + ',viewId=' + view;
				srczoomData.imageSrc = url;
				srczoomData.zoomImage = url;
				srczoomData.currentImage = url;
				
				if (srczoomData.zoomWindow) {
					srczoomData.zoomWindow.css({
						backgroundImage : "url('" + url + "')"
					});
				}
				if (srczoomData.zoomLens) {
					srczoomData.zoomLens.css({
						backgroundImage : "url('" + url + "')"
					});
				}
			}		
		});

		$('.spreadplugin-article .views li,.spreadplugin-article-detail .views li').click(function() {
			var id = '#' + $(this).closest('.spreadplugin-article,.spreadplugin-article-detail').attr('id');						
			var image = $(id + ' img.preview');
			var src = image.attr('src');
			var srczoom = image.attr('data-zoom-image');
			var srczoomData = image.data('elevateZoom');
			
			view = $(this).attr('value');						
			appearance = $(id + ' #appearance').val();						
			$(id + ' #view').val(view);
				
			image.attr('src',src.replace(/\,viewId=(\d+)/g, '') + ',viewId=' + view);

			image.attr('data-zoom-image',srczoom.replace(/\,appearanceId=(\d+)/g,'').replace(/\,viewId=(\d+)/g,'') + ',appearanceId='	+ appearance + ',viewId=' + view);
			
			if (srczoomData) {
				var url = srczoomData.imageSrc.replace(	/\,appearanceId=(\d+)/g, '').replace(/\,viewId=(\d+)/g,'');
				url = url + ',appearanceId='	+ appearance + ',viewId=' + view;
				srczoomData.imageSrc = url;
				srczoomData.zoomImage = url;
				srczoomData.currentImage = url;
				if (srczoomData.zoomWindow) {
					srczoomData.zoomWindow.css({
						backgroundImage : "url('" + url + "')"
					});
				}
				if (srczoomData.zoomLens) {
					srczoomData.zoomLens.css({
						backgroundImage : "url('" + url + "')"
					});
				}
			}
		});

		$('.spreadplugin-article .description-wrapper div.header,.spreadplugin-article-detail .description-wrapper div.header').click(function() {
					var par = $(this).parent().parent().parent();
					var field = $(this).next();

					if (field.is(':hidden')) {
						par.addClass('activeDescription');
						field.show();
						$(this).children('a').html(ajax_object.textHideDesc);
					} else {
						par.removeClass('activeDescription');
						$('.description-wrapper div.description').hide();
						$('.description-wrapper div.header a').html(ajax_object.textShowDesc);
					}
				});
		$('.spreadplugin-article .product-description-wrapper div.header,.spreadplugin-article-detail .description-wrapper div.header').click(function() {
					var par = $(this).parent().parent().parent();
					var field = $(this).next();

					if (field.is(':hidden')) {
						par.addClass('activeDescription');
						field.show();
						$(this).children('a').html(ajax_object.textProdHideDesc);
					} else {
						par.removeClass('activeDescription');
						$('.product-description-wrapper div.description').hide();
						$('.product-description-wrapper div.header a').html(ajax_object.textProdShowDesc);
					}
				});

		$('.spreadplugin-article form,.spreadplugin-article-detail form').submit(function(event) {

			event.preventDefault();
			var data = $(this).serialize() + '&action=myAjax'; //&'+sid
			var form = this;
			var button = $('#' + form.id + ' input[type=submit]').not('.add-basket-button');


			// to basket animation vars
			var productIdValSplitter = form.id.split("_");
			var productIdVal = productIdValSplitter[1];
			var newImageWidth   = $("#previewimg_" + productIdVal).width() / 3;	
			var newImageHeight  = $("#previewimg_" + productIdVal).height() / 3;
			var productX = $("#previewimg_" + productIdVal).offset().left;
			var productY = $("#previewimg_" + productIdVal).offset().top;
			var basketX = $(".spreadplugin-checkout").offset().left;
			var basketY = $(".spreadplugin-checkout").offset().top;
			var gotoX = basketX - productX;
			var gotoY = basketY - productY;
								
		//console.debug(ajax_object.textButtonAdd);
			button.val(ajax_object.textButtonAdded);
			$(this).find('.add-basket-button').parent().append('<img src="' + ajax_object.loadingImage + '" class="spreadplugin-loading product' + productIdVal + '" />');
			/*
				.clone()
				//.prependTo("#article_" + productIdVal)
				.appendTo($(this).parent())
				.addClass('clone')
				.css({'position' : 'absolute'})
				.css({'z-index' : '1008'})
				.animate({opacity: 0.9}, 100 )
				.animate({opacity: 0.1, marginLeft: gotoX, marginTop: gotoY, width: newImageWidth, height: newImageHeight}, 600, function() { 
				*/
					$.post(ajax_object.ajaxLocation,data,function(json) {
						
											if (json.c.m==1) {
												button.val(ajax_object.textButtonAdd);
											} else {
												button.val(ajax_object.textButtonFailed);
											}
											
											refreshCart(json);	
											// lets try to merge available baskets,...			
											//mergeBasket();
											
											$('img.spreadplugin-loading.product' + productIdVal).remove();
											
										}, 'json');
					//$(this).remove();
						
				//});

			return false;
		});

		/*
		 $('.spreadplugin-article .shipping-window,.spreadplugin-article-detail .shipping-window').click(function(){
			$('#spreadplugin-shipment-wrapper').show();
			$('#spreadplugin-shipment-wrapper').position({at: 'bottom center', of: $(this), my: 'top'});
		 });
	*/
	
		$('.spreadplugin-article .shipping-window,.spreadplugin-article-detail .shipping-window').magnificPopup({
			
			items: {
			  src: '#spreadplugin-shipment-wrapper'
			},
			type: 'inline',
			callbacks: {
				open: function() {
					$('#spreadplugin-shipment-wrapper').show();
					$('#spreadplugin-shipment-wrapper').parent('.mfp-content').css('width',400);
				},
				close: function () {
					$('#spreadplugin-shipment-wrapper').hide();
				}
			}
			});

		

		// integrated edit wrapper
		$('.spreadplugin-article .edit-wrapper-integrated,.spreadplugin-article-detail .edit-wrapper-integrated').click(function() {
			
			var designid = $(this).data('designid');
			var productid = $(this).data('productid');
			var viewid = $(this).data('viewid');
			var appearanceid = $(this).data('appearanceid');
			var producttypeid = $(this).data('producttypeid');
			
			$.magnificPopup.open({
				items: {
					type: 'inline',
					src: '#spreadplugin-designer-wrapper',
				},
				callbacks: {
					open: function() {
						$('.mfp-iframe-holder .mfp-content').css('height',$(window).height()-200);
						callIntegratedDesigner(productid, producttypeid);
					},
					resize: function () {
						$('.mfp-iframe-holder .mfp-content').css('height',$(window).height()-200);
					},
					close: function() {
						$('#spreadplugin-designer').html("");
					}
				}
			});
		});



		if (ajax_object.pageCheckoutUseIframe == 2) {
			// premium edit wrapper (inline)
			$('.spreadplugin-article .edit-wrapper a,.spreadplugin-article-detail .edit-wrapper a').magnificPopup({
			type: 'iframe',
			callbacks: {
				open: function() {
					$('.mfp-iframe-holder .mfp-content').css('height',$(window).height()-200);
				},
				resize: function () {
					$('.mfp-iframe-holder .mfp-content').css('height',$(window).height()-200);
				}
			}
			});


			$('.spreadplugin-article .details-wrapper a,.spreadplugin-article-detail .details-wrapper a').magnificPopup({
				type: 'iframe',
				preloader: true
			});
		}

		$('.spreadplugin-design .image-wrapper').click(function() {

					var id = $(this).parent().attr('id');
					id = '#' + id.replace('design', 'designContainer');

					if ($(id).is(':hidden')) {
						$(id).addClass('active');
						$(id).slideDown('slow');
					} else {
						$('#spreadplugin-list .design-container').slideUp('slow', function() {
									$(this).removeClass('active');
								});
					}

				});

	}

	function bindHover() {
		$(".spreadplugin-article img.preview,.spreadplugin-article-detail img.preview").unbind();
		$("div.spreadplugin-article.min-view").unbind();

		// display image caption on top of image
		$(".spreadplugin-design div.image-wrapper").each(function() {

					$(this).hover(
							function() {
								$(this).find(".img-caption").stop(true)
										.css('display', 'inline-block')
										.animate({
											'top' : -50
										}, {
											queue : false,
											duration : 400
										});
							},
							function() {
								$(this).find(".img-caption").stop(true)
										.hide().animate({
											'top' : 0
										});
							});
				});

		// Articles zoom image
		if (ajax_object.zoomActivated==1) {
			$(".spreadplugin-article img.preview,.spreadplugin-article-detail img.preview").hover(function() {
				$(this).elevateZoom(ajax_object.zoomConfig);
			});
		}

		// socials
		$('.spreadplugin-article ul.soc-icons a,.spreadplugin-article-detail ul.soc-icons a').hover(function() {
			$(this).parent().css('background-color',$(this).attr('data-color'));
		}, function() {
			$(this).parent().removeAttr('style');
		});
		
		$('div.spreadplugin-article.min-view').hover(function () {
						$(this).addClass('active');
		},function() {
						$(this).removeClass('active');
		});
		
		// hover modal effekt when min-view
		//if (!$.browser.msie || parseInt($.browser.version, 10) > 8) {
			var onMouseOutOpacity = 1;
			$('div.spreadplugin-article.min-view').css('opacity', onMouseOutOpacity).hover(function () {
				$(this).prevAll().stop().not('.clear,#infscr-loading').fadeTo('slow', 0.60);
				$(this).nextAll().stop().not('.clear,#infscr-loading').fadeTo('slow', 0.60);
			},
			function() {
				$(this).prevAll().stop().not('.clear,#infscr-loading').fadeTo('slow', onMouseOutOpacity);
				$(this).nextAll().stop().not('.clear,#infscr-loading').fadeTo('slow', onMouseOutOpacity);
			});
		//} 

	}

	// Fixed menu bar
	//var msie6 = $.browser == 'msie' && $.browser.version < 7;
	//if (!msie6 && $('.spreadplugin-menu').length != 0) {
	if ($('.spreadplugin-menu').length != 0) {
		var top = $('#spreadplugin-menu').offset().top - parseFloat($('#spreadplugin-menu').css('margin-top').replace(/auto/, 0));

		$(window).scroll(function(event) {
					// what the y position of the scroll is
					var y = $(this).scrollTop();
					// whether that's below the form
					if (y >= top - 0) {
						// if so, ad the fixed class
						$('#spreadplugin-menu').addClass('fixed');

						// using wp #main container width and pos for fixed
						$('#spreadplugin-menu').css('width',$('div.spreadplugin-items').width());
					} else {
						// otherwise remove it
						$('#spreadplugin-menu').css('width', '');
						$('#spreadplugin-menu').removeClass('fixed');
					}
				});
	}

	// reload caption
	$(window).resize(function() {
		$(".img-caption").hide();
	});

	if (ajax_object.infiniteScroll == 1) {
		// infinity scroll
		$('#spreadplugin-list').infinitescroll({
			nextSelector : '#spreadplugin-items #pagination a',
			navSelector : '#spreadplugin-items #pagination',
			itemSelector : '#spreadplugin-list ' + infiniteItemSel,
			loading : {
				img : ajax_object.loadingImage,
				msgText : ajax_object.loadingMessage,
				finishedMsg : ajax_object.loadingFinishedMessage
			},
			animate : true,
			debug : false,
			bufferPx : 40
		}, function(arrayOfNewElems) {
			bindClick();
			bindHover();

			if (ajax_object.lazyLoad == 1) {
				$("img.lazyimg").lazyload({effect : "fadeIn"});
			}
			
			 var $newElems = $( arrayOfNewElems );
				$('#spreadplugin-list').isotope( 'appended', $newElems, true ); 

		});
	}


	$('#spreadplugin-items #productCategory').change(function() {
				prod = $(this).val();
				
				if (usePrettyUrl==1) {
					//document.location = ajax_object.pageLink + 'pagesp/' + (paged!=''?paged + '/' : '') + 'productCategory/' + (prod!=''?prod + '/':'') + 'productSubCategory/' + (prod2!=''?prod2 + '/' : '') + 'articleSortBy/' + (sor!=''?sor + '/':'');
					document.location = ajax_object.pageLink + (paged?'pagesp/' + paged + '/' : '') + 'productCategory/' + prod;
				} else {
					document.location = ajax_object.pageLink + sep + 'pagesp=' + paged + '&productCategory=' + prod + '&productSubCategory=' + prod2 + '&articleSortBy=' + sor;
				}
			});
	$('#spreadplugin-items #productSubCategory').change(function() {
				prod2 = $(this).val();

				if (usePrettyUrl==1) {
					document.location = ajax_object.pageLink + (paged?'pagesp/' + paged + '/' : '') + 'productCategory/' + prod + '/productSubCategory/' + prod2;
				} else {
					document.location = ajax_object.pageLink + sep + 'pagesp=' + paged + '&productCategory=' + prod + '&productSubCategory=' + prod2 + '&articleSortBy=' + sor;
				}
			});

	$('#spreadplugin-items #articleSortBy').change(function() {
				sor = $(this).val();
				if (usePrettyUrl==1) {
					document.location = ajax_object.pageLink + (paged?'pagesp/' + paged + '/' : '') + 'productCategory/' + prod + '/productSubCategory/' + prod2 + '/articleSortBy/' + sor + '/';
				} else {
					document.location = ajax_object.pageLink + sep + 'pagesp=' + paged + '&productCategory=' + prod + '&productSubCategory=' + prod2 + '&articleSortBy=' + sor;
				}
			});


	$('.spreadplugin-checkout-link').click(function(e) {
		e.preventDefault();
		
		var cart = $(this).parent().next('.spreadplugin-cart');

		if (cart.attr('id') == "spreadplugin-widget-cart") {
			cart.css('position','relative');
		}

		if (cart.is(':hidden')) {
			cart.css('display','inline-block');
		} else {
			cart.hide();
		}
	});



	function getParameterByName(name) {
		name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
		var regexS = "[\\?&]" + name + "=([^&#]*)";
		var regex = new RegExp(regexS);
		var results = regex.exec(window.location.search);
		if (results == null) {
			return "";
		} else {
			return encodeURIComponent(decodeURIComponent(results[1].replace(/\+/g, " ")));
		}
	}
	
	
	function refreshCart(json) {
		
		$('.spreadplugin-checkout-link').attr('href', json.c.u);
		$('.spreadplugin-checkout-link').removeAttr('title');
		$('.spreadplugin-checkout span').text(json.c.q);
		$('.spreadplugin-cart-checkout a').attr('href', json.c.u);
		
		// &'+sid
		$.get(ajax_object.ajaxLocation,'action=myCart',function (data) {
			$('.spreadplugin-cart').html(data);
			
			
			// checkout in an iframe in page
			if (ajax_object.pageCheckoutUseIframe == 1) {
						$('.spreadplugin-cart-checkout a').click(function(event) {
									event.preventDefault();
		
									var checkoutLink = $(this).attr('href');
		
									if (typeof checkoutLink !== "undefined" && checkoutLink.length > 0) {
		
										$('#spreadplugin-items #pagination').remove();
										$('#spreadplugin-items #spreadplugin-menu').remove();
										$(window).unbind('.infscr');
		
										$('#spreadplugin-list').html('<iframe style="z-index:10002" id="checkoutFrame" frameborder="0" width="900" height="2000" scroll="yes">');
										$('#spreadplugin-list #checkoutFrame').attr('src', checkoutLink);
		
										$('html, body').animate({
															scrollTop : $("#spreadplugin-items #checkoutFrame").offset().top
														}, 2000);
		
									}
								});
		
			}
		
			// checkout in an iframe with modal window (magnific)
			if (ajax_object.pageCheckoutUseIframe == 2) {
					var checkoutLink = $('.spreadplugin-cart-checkout a').attr('href');
		
					if (typeof checkoutLink !== "undefined" && checkoutLink.length > 0) {
					
							$('.spreadplugin-cart-checkout a').magnificPopup({
					type: 'iframe',
					callbacks: {
						close: function() {
							location.reload();
							return;
						}
					}
					});
				}
			}
			
			
			$('.cart-row a.deleteCartItem').click(function(e) {
				e.preventDefault;
				$(this).closest('.cart-row').show().fadeOut('slow');
				
				// &'+sid+'
				$.post(ajax_object.ajaxLocation,'action=myDelete&id='+$(this).closest('.cart-row').data('id'),function() {
						$.post(ajax_object.ajaxLocation,'action=myAjax',function(json) {
							refreshCart(json);
						}, 'json');
					});

			});
			
			// hide cart when user clicks close
			$('.spreadplugin-cart-close').click(function(e) {
				e.preventDefault();
				$(".spreadplugin-cart").hide();
			});

		});
	}

	
	// &'+sid
	$.post(ajax_object.ajaxLocation,'action=myAjax',function(json) {
		refreshCart(json);
	}, 'json');	
	

	bindClick();
	bindHover();
	if (ajax_object.lazyLoad == 1) {
		$("img.lazyimg").lazyload({effect : "fadeIn"});
	}
	
	
	
	
	$("#spreadplugin-tabs li a").click(function() {
		//	First remove class "active" from currently active tab
		$("#spreadplugin-tabs li").removeClass('active');

		//	Now add class "active" to the selected/clicked tab
		$(this).parent().addClass("active");

		//	Hide all tab content
		$(".spreadplugin-tab_content").hide();

		//	Show the selected tab content
		$($(this).parent().find("a").attr("href")).fadeIn();

		//	At the end, we add return false so that the click on the link is not executed
		return false;
	});
	
	
	
	
	


	// integrated designer shop // conformat
	function callIntegratedDesigner(desiredProductId, desiredProducttypeId) {
		
			// @see http://spreadshirt.github.io/apps/tablomat
            spreadshirt.create("tablomat",{

                shopId: ajax_object.designerShopId,
				target: document.getElementById(ajax_object.designerTargetId),
				platform: ajax_object.designerPlatform,
				locale: ajax_object.designerLocale,
				width: ajax_object.designerWidth,
				productId: desiredProductId,
				setProductType: desiredProducttypeId,
				/*
				* Currently disabled - apiBasketId not taken?
				
				apiBasketId: ajax_object.designerBasketId,
				basketId: ajax_object.designerBasketId,
  
				addToBasket: function(item, callback) {
				
					 // implement how to get the item to your basket
					 // e.g. do some AJAX request
				
					 // invoke callback function when you're done
				
					 var err = null; // set to a js truly type for showing an error in tabloat,
					 // see http://www.sitepoint.com/javascript-truthy-falsy/
				
					 callback && callback(err);
				
				}
				*/
                addToBasket: function(basketItem, callback) {
					
					var data = {
						article: basketItem.product.id,
						size: basketItem.size.id,
						appearance: basketItem.appearance.id,
						quantity: basketItem.quantity,
						shopId: basketItem.shopId,
						action: 'myAjax',
						type: '1' // type switch for using articleId as productId
					}

					$.post(ajax_object.ajaxLocation,data,function(json) {
						
						if (json.c.m == 1) {
							// return success to confomat
							callback && callback();
						} else {
							// return failure to confomat
							callback && callback(true);
						}
						
						// Refresh shopping cart
						refreshCart(json);
					}, 'json');
                }

			}, function(err, app) {
			 if (err) {
				// something went wrong
				console.log(err);
			} else {
				// cool I can control the application (see below)
				// app.setProductTypeId(6);
			}
			});
	}
	
	
	// Dashboard grid
	if (typeof window.Isotope !== "undefined") {
		$('#spreadplugin-list').isotope({
		  // options
		  itemSelector: '.spreadplugin-item',
		  layoutMode: 'fitRows'
		});
	}
	
});
