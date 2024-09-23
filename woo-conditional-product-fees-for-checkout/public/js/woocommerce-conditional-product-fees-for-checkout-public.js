(function ($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(document).on('change', 'input[name="payment_method"]', function () {
        $('body').trigger('update_checkout');
    });
    if ($('#billing_state').length) {
        $(document).on('change', '#billing_state', function () {
            $('body').trigger('update_checkout');
        });
    }
    $( document ).ready(function( $ ) {
        $(document).on('click', '.checbox_row', function(e){
            if ( 'checkbox' !== e.target.type ) {
                if($(this).find('.input-checkbox').is(':checked')) {
                    $(this).find('.input-checkbox').prop('checked',false);
                } else {
                    $(this).find('.input-checkbox').prop('checked',true);
                }
            }
            $('body').trigger('update_checkout');
        });

        $(document).on('change', '.input-dropdown', function(){
            dropdown_check();
            $('body').trigger('update_checkout');
        });

        var reload_for_fee = true;
        $( document ).ajaxComplete(function( event, request, settings ) {
            dropdown_check();
            if( settings.url.indexOf('update_order_review') > -1 && reload_for_fee && ( $('.input-checkbox:checked').length > 0 || jQuery('.input-dropdown').next('input[type="hidden"]').length > 0 ) ) {
                $('body').trigger('update_checkout');
                reload_for_fee = false;
            }
        });

        $(document).on('click', '.wcpfc-fee-tooltip', function(){
            $('.wcpfc-fee-tooltiptext').toggle();
        });
    });
    function dropdown_check(){        
        $('.input-dropdown').each(function(){ 
            $(this).next(':hidden').remove();
            if( 'yes' === $(this).val()) { 
                $('<input>').attr({
                    type: 'hidden',
                    name: 'wef_fees_id_array[]',
                    value: $(this).data('value'),
                }).insertAfter($(this));
            }
        });
    }
})(jQuery);