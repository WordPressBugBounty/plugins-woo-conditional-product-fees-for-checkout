(function($) {
    var wc_custom_fees = {
        init: function() {
            $('body').on('click', '.add-custom-fee', this.open_modal);
            $(document.body).on('wc_backbone_modal_response', this.handle_modal_response);
        },

        open_modal: function(e) {
            e.preventDefault();
            $(this).WCBackboneModal({ template: 'wc-modal-add-custom-fee' });
            return false;
        },

        handle_modal_response: function(e, target) {
            if ('wc-modal-add-custom-fee' === target) {
                var selectedFeeId = $('#custom_fee_select').val();
                var selectedOption = $('#custom_fee_select option:selected');
                var feeValue = selectedOption.text().split('-')[1].trim();
                var feeName = selectedOption.text().split('-')[0].trim();

                if (selectedFeeId) {
                    wc_custom_fees.add_custom_fee(selectedFeeId, feeValue, feeName);
                }
            }
        },

        add_custom_fee: function(feeId, feeValue, feeName) {
            var postId = $('#post_ID').val();

            // Temporarily override window.confirm
            var originalConfirm = window.confirm;
            window.confirm = function() {
                return true;
            };

            $.ajax({
                url: wc_custom_fees_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcpfc_add_custom_fee_to_order__premium_only',
                    wc_custom_nonce: wc_custom_fees_params.nonce,
                    order_id: postId,
                    fee_id: feeId,
                    fee_value: feeValue,
                    fee_name: feeName,
                },
                success: function(response) {
                    if (response.success) {
                        
                        // Trigger the button click event
                        $('.button.button-primary.calculate-action').click();
                    } else {
                        console.error('Error adding custom fee:', response.data);
                    }
                },
                complete: function() {
                    // Restore the original window.confirm functionality
                    window.confirm = originalConfirm;
                }
            });
        }
    };

    $(document).ready(function() {
        wc_custom_fees.init();
    });
})(jQuery);

