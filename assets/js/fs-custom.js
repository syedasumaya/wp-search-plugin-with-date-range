
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
    'use strict';

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
        jQuery('.fs-pagenavi').remove();
    }

    FancySearchResizeImage(jQuery(".fs-thumbnail"));
});


/*  Sidebar Menu Ajax   */
function fancysearch_menu_ajax($) {
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

                        /*  Padding-Menu    */
                        //var nav_width = $('.fs-menu').outerWidth();
                        //$('.fs-content').css({'padding-left':nav_width});
//                                $('.pagination-block').css({'margin-left':nav_width});

                        /*  Margin-Top    */
                        var h_head = $('.fs-header').outerHeight();
                        var w_window = $(window).width();
                        if(w_window > 480){
                            $('.fs-maincontent').css('margin-top',h_head+40);
                        }else{
                            $('.fs-maincontent').css('margin-top',h_head+20);
                        }

                        /*  Load Trigger and Scroll */
                        var $container        =   $('.FancySearch'),
                            paginationActive  =   Trigger_Scroll.paginationActive,
                            paginationType    =   Trigger_Scroll.paginationType;

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
                                    var $newElems =   $( newElements ).css({ opacity: 0 });
                                    // ensure that images load before adding to masonry layout
                                    $newElems.imagesLoaded(function(){
                                        // show elems now they're ready
                                        $newElems.animate({ opacity: 1 });
                                        // trigger scroll again
                                        if($newElems.length){
                                            //move item-more to the end
                                            $('div.fs-loadmore').find('a:first').show();
                                        }
                                    });
                                }
                            );

                            if ( paginationType == 'trigger' ){

                                $(window).unbind('.infscr');

                                $('.fs-loadmore a').click(function(){

                                    $('.fs-loadmore').find('a:first').hide();
                                    $container.infinitescroll('retrieve');
                                    return false;
                                });
                            }
                        }
                    }
                }
            });
        }else{
            return false;
        }
    });
}
fancysearch_menu_ajax(jQuery);


/*  Load Trigger and Scroll */
function fancysearch_trigger_scroll(){
    'use strict';

    var $container        =   jQuery('.FancySearch'),
        paginationActive  =   Trigger_Scroll.paginationActive,
        paginationType    =   Trigger_Scroll.paginationType;
    console.log(paginationActive);
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
                    return false;
                });
            }
        }
    });
}
fancysearch_trigger_scroll();




/*  Position- Menu  */
jQuery(document).ready(function(){
    'use strict';

    var menu        =   jQuery(".fs-menu"),
        container   =   jQuery(".fs-container"),
        content     =   jQuery(".fs-content"),
        width_menu  =   menu.width(),
        w_container =   container.width();
    content.css("width", w_container-width_menu);

    menu.theiaStickySidebar({
        additionalMarginTop: 90
    });


});