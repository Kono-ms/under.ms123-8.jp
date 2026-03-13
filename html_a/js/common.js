$(function(){
	
	menuCompact();
	
	radioContextToggle();
	
	menuFixed();
	
	previewImage ();

	try{
		
		$('[data-remodal-id=modal]').remodal();
		
	} catch (e) {}
});

$(window).on('resize load', function(){
	
	sameHeight();
	heightFix();
});

function radioContextToggle(){
	
	if(!$('.radio_context_toggle')[0]){ return; }
	
	$('.radio_context_toggle').each(function(){
		
		var $me=$(this);
		
		$('input[type=radio]',this).on('click',function(){
			
			if($(this).attr('data-show')=='true'){
				
				$('.radio_context',$me).show();
				
			}else{
				
				$('.radio_context',$me).hide();
			}
		});
	});
}

function menuFixed(){
	
	if(!$('.menu_fixed')[0]){ return; }
	
	//init
	var $menu = $('.menu_fixed');
	var offset = $menu.offset();
	if($(window).scrollTop() > offset.top) {
		
		$menu.width($menu.parent().width());
		$menu.parent().css('padding-top',$menu.height()+'px');
		$menu.addClass('active');
		
	} else {
		
		$menu.parent().removeAttr('style');
		$menu.removeAttr('style');
		$menu.removeClass('active');
	}
	
	$(window).on('scroll resize', function() {
		
		if($(window).scrollTop() > offset.top) {
			
			$menu.width($menu.parent().width());
			$menu.parent().css('padding-top',$menu.height()+'px');
			$menu.addClass('active');
			
		} else {
			
			$menu.parent().removeAttr('style');
			$menu.removeAttr('style');
			$menu.removeClass('active');
		}
	});
}

//sameHeight セットした要素たちの高さを揃える
function sameHeight(){
	
	if(!$('.same_height')[0]){ return; }
	
	var maxHeight=0;
	var timer = false;
	
	//style属性をリセット
	$('.same_height').removeAttr('style');

	if (timer!==false) { clearTimeout(timer); }
	
	timer=setTimeout(function() {

		if (window.matchMedia('screen and (min-width:768px)').matches) {
			
			$('.same_height').each(function() {
				
				if(maxHeight<$(this).height()){
					
					maxHeight=$(this).height();
				}
			});
			
			$('.same_height').height(maxHeight);
		}
	}, 0);
}

//heightFix セットした要素の子要素たちの高さを揃える
function heightFix(){
	
	if(!$('.height_fix')[0]){ return; }
	
	var maxHeight=0;
	var timer = false;
	
	//style属性をリセット
	$('.height_fix').children().removeAttr('style');
	
	if (timer!==false) { clearTimeout(timer); }
	
	timer=setTimeout(function() {

		if (window.matchMedia('screen and (min-width:768px)').matches) {

			$('.height_fix').each(function() {
				
				maxHeight=0;
				
				$(this).children().each(function() {
					
					if(maxHeight<$(this).height()){
						
						maxHeight=$(this).height();
					}
				});
				
				$(this).children().height(maxHeight);
			});
		}
	}, 0);
}

//イメージ画像プレビュー
function previewImage (){

	if(!$('.list_photo_select').find('img')[0]){ return; }
	
	$('.list_photo_select input[type=file]').on('change',function(){
		
		var box = $(this).closest('.list_photo_select li');
		
		var filePath = $(this).prop('files')[0];
		var fileReader = new FileReader();
		
		if(filePath.type.match('image.*')){
			
			fileReader.onload = function() {
				
				$('img',box).replaceWith(function() {
					
					var img =$('<img>');
					img.attr('src', fileReader.result);
					return img;
				});
			}
			fileReader.readAsDataURL(filePath);
		}
	});
}

//メニューの開閉を記憶する
function menuCompact(){
	
	//ページロード時の開閉アニメーションを無効にする
	$('body').addClass('hold-transition');
	
	if(localStorage.getItem('menuCompact')=='true'){

		$('body').addClass('sidebar-collapse');
		
	}else{
		
		$('body').removeClass('sidebar-collapse');
	}
	
	$('.sidebar-toggle').on('click',function(){
		
		//開閉アニメーションを有効にする
		if($('body').hasClass('hold-transition')){
		
			$('body').removeClass('hold-transition');
		}
		
		//最小化を指定されている場合
		if($('body').hasClass('sidebar-collapse')){
			
			//最大化を記憶	
			localStorage.setItem('menuCompact','false');
			
		}else{
			
			//最小化を記憶	
			localStorage.setItem('menuCompact','true');
		}
	});
}