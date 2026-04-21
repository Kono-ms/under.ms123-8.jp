$(function() {
 var h = $(window).height();
  $('#loading__wrapper').css('display','none');
  $('#is-loading ,#loading').height(h).css('display','block');
 });

 $(window).load(function () {
  $('#is-loading').delay(900).fadeOut(800);
  $('#loading').delay(600).fadeOut(300);
  $('#loading__wrapper').css('display', 'block');
 });


 $(function(){
  setTimeout('stopload()',10000);
  });

  function stopload(){
   $('#loading__wrapper').css('display','block');
   $('#is-loading').delay(900).fadeOut(800);
   $('#loading').delay(600).fadeOut(300);
 }

$(function(){
　$(window).scroll(function (){
    $('.effect-fade').each(function(){
        var elemPos = $(this).offset().top;
        var scroll = $(window).scrollTop();
        var windowHeight = $(window).height();
        if (scroll > elemPos - windowHeight){
            $(this).addClass('effect-scroll');
        }
    });
　});
});
// アンカーリンク 高さ調整
$(function () {
 var headerHight = 90; //ヘッダの高さ
 $('a[href^=#]').click(function(){
     var href= $(this).attr("href");
      var $targetModal = $(href);
      var isModalTrigger = href !== "#" && $targetModal.hasClass('teian-search-modal');
      var isModalClose = $(this).closest('.teian-search-modal').length > 0;
      if (isModalTrigger || isModalClose) {
        return true;
      }
       var target = $(href == "#" || href == "" ? 'html' : href);
        var position = target.offset().top-headerHight; //ヘッダの高さ分位置をずらす
     $("html, body").animate({scrollTop:position}, 550, "swing");
        return false;
   });
});
// modal
$(function(){
    $('.js-modal-open').on('click',function(){
        $('.js-modal').fadeIn();
        return false;
    });
    $('.js-modal-close').on('click',function(){
        $('.js-modal').fadeOut();
        return false;
    });
});
// ボタンロールオーバー用
function rollOver(){
    var preLoad = new Object();
    $('img.over,input.over').each(function(){
        var imgSrc = this.src;
        var fType = imgSrc.substring(imgSrc.lastIndexOf('.'));
        var imgName = imgSrc.substr(0, imgSrc.lastIndexOf('.'));
        var imgOver = imgName + '-on' + fType;
        preLoad[this.src] = new Image();
        preLoad[this.src].src = imgOver;
        $(this).hover(
            function (){
                this.src = imgOver;
            },
            function (){
                this.src = imgSrc;
            }
        );
    });
}
$(function(){
  $('.on-off').change(function(){
    var ta = $(this).attr("id");
    var id = Number(ta.replace("Panel",""))-1;
    
    for(var i=0; i<$('label').size(); i++){
      var name = $('label').eq(i).attr("for");
      if(name == ta){
        if($(this).prop("checked")){
          $('label').eq(i).addClass("label_open");
        }else{
          $('label').eq(i).removeClass("label_open");      
        }        
      }
    }
    
  });
});
$(document).ready(rollOver);
