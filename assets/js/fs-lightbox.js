'use strict';

/*
 * Method reize image
 * @variables class
 */
function FancySearchResizeImage(obj){
    var widthStage;
    var heightStage ;
    var widthImage;
    var heightImage;
    obj.each(function (i,el){

        heightStage = jQuery(this).height();

        widthStage = jQuery (this).width();

        var img_url = jQuery(this).find('img').attr('src');

        var image = new Image();
        image.src = img_url;

        widthImage = image.naturalWidth;
        heightImage = image.naturalHeight;

        var tzimg	=	new fancy_resizeImage(widthImage, heightImage, widthStage, heightStage);
        jQuery(this).find('img').css ({ top: tzimg.top, left: tzimg.left, width: tzimg.width, height: tzimg.height });


    });

}

jQuery(document).ready(function() {

    var link = jQuery(location).attr('href');


    /*  Margin-Top    */
    var w_window = jQuery(window).width();
    var h_head = jQuery('.fs-header').outerHeight();
    if(w_window > 480){
        jQuery('.fs-maincontent').css('margin-top',h_head+40);
    }else{
        jQuery('.fs-maincontent').css('margin-top',h_head+20);
    }

    /*   View As List - Grid   */
    jQuery(".fs-viewas .fs-list i").live("click",function(){
        jQuery(this).parents().find("#fancysearch-content").addClass("fancysearch-list").removeClass("fancysearch-grid");
    });
    jQuery(".fs-viewas .fs-grid i").live("click",function(){
        jQuery(this).parents().find("#fancysearch-content").addClass("fancysearch-grid").removeClass("fancysearch-list");
    });

    /*  errorsearch */
    if( jQuery('.fs-errorsearch').length > 0 ){
        jQuery('.fancysearch-content.fancysearch-grid .fs-blocksearch').css('width','100%');
        jQuery('.fs-pagenavi,.fs-loadmore').remove();
    }
});

jQuery(window).on('load resize',function(){
    'use strict';

   //if( jQuery(".fs-close-lightbox").length > 0 ){
       jQuery(".fs-close-lightbox").live("click",function(){
           jQuery(".fs-lightbox-content").remove();
       });
   //}
});

/*  Sidebar Menu Ajax   */
function fs_lightbox_menu_ajax($){
    'use strict';

    $(".fs-menu-type a").on("click",function(e){

        e.preventDefault();

        var current = $(this).hasClass("current");
        if( current == false ){
            /*  Show Loadajax image  */
            $(".fs-loadajax").fadeIn(500);

            $(".fs-menu-type a").removeClass("current");
            $(this).addClass("current");
            var url = $(this).attr("href");
            $.ajax({
                type : 'GET',
                url : url,
                complete : function (jqXHR, textStatus) {
                    var condition = (typeof (jqXHR.isResolved) !== 'undefined') ? (jqXHR.isResolved()) : (textStatus === "success" || textStatus === "notmodified");
                    if (condition) {
                        /*  Hide Loadajax image  */
                        $(".fs-loadajax").fadeOut(500);

                        /*  Load-content    */
                        var data    = jqXHR.responseText;
                        $('#fs_append').html($(data).find('.fs-content').html());

                        /*  Load Trigger and Scroll */
                        fs_lightbox_trigger_scroll();
                    }
                }
            });
        }else{
            return false;
        }
    });
}

/*  Load Trigger and Scroll */
function fs_lightbox_trigger_scroll(){
    var $container        =   jQuery('.FancySearch'),
        paginationActive  =   Lightbox_Trigger_Scroll.paginationActive,
        paginationType    =   Lightbox_Trigger_Scroll.paginationType;
    jQuery(function(){
        if( paginationActive == "1" && paginationType != "classic"){
            $container.infinitescroll({
                navSelector  : '.pagination-block a',    // selector for the paged navigation
                nextSelector : '.pagination-block a:first',  // selector for the NEXT link (to page 2)
                itemSelector : '.fs-blocksearch',     // selector for all items you'll retrieve
                errorCallback: function(){


                },
                loading: {
                    msgText:'',
                    finishedMsg: '',
                    selector: '.fs-pagenavi'
                }
            },
                // call Isotope as a callback
                function( newElements ) {
                    var $newElems =   jQuery( newElements ).css({ opacity: 0 });
                    // ensure that images load before adding to masonry layout
                    $newElems.imagesLoaded(function(){
                        // show elems now they're ready
                        $newElems.animate({ opacity: 1 });
                        // trigger scroll again
                        if($newElems.length){
                            //move item-more to the end
                            jQuery('div.fs-loadmore').find('a:first').show();
                        }
                    });
                }
            );

            if ( paginationType == 'trigger' ){

                jQuery(window).unbind('.infscr');

                jQuery('.fs-loadmore a').click(function(){
                    jQuery('.fs-loadmore').find('a:first').hide();
                    $container.infinitescroll('retrieve');

                });
            }
        }
    });
    FancySearchResizeImage(jQuery(".fs-thumbnail"));
}


