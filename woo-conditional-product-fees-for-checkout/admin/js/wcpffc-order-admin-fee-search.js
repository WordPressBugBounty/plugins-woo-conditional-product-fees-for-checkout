(function($) {
    var wcpfc_fees_filter = {
        ajax_fee_search: function() {
            // Ajax customer search boxes
            $( ':input.wc-fee-search' ).filter( ':not(.enhanced)' ).each( function() {
                var select2_args = {
                    allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
                    placeholder: $( this ).data( 'placeholder' ),
                    minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '1',
                    escapeMarkup: function( m ) {
                        return m;
                    },
                    ajax: {
                        url:         wc_custom_fees_search_params.ajax_url,
                        dataType:    'json',
                        delay:       1000,
                        data:        function( params ) {
                            return {
                                term:     params.term,
                                action:   'wcpfc_json_search_fees',
                                security: wc_custom_fees_search_params.fee_filter_none
                            };
                        },
                        processResults: function( data ) {
                            var terms = [];
                            if ( data ) {
                                $.each( data, function( id, text ) {
                                    terms.push({
                                        id: id,
                                        text: text
                                    });
                                });
                            }
                            return {
                                results: terms
                            };
                        },
                        cache: true
                    }
                };

                $( this ).selectWoo( select2_args ).addClass( 'enhanced' );
            });
        }
    };

    $(document).ready(function() {
        wcpfc_fees_filter.ajax_fee_search();
    });
})(jQuery);

