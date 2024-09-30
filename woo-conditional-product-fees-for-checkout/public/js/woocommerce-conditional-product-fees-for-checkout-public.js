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

    $(document.body).trigger('wc_update_cart');

    $(document).ready(function ($) {

        // Tooltip toggle on click
        $(document).on('click', '.wcpfc-fee-tooltip', function () {
            $('.wcpfc-fee-tooltiptext').toggle();
        });

        /**
         * Optional Fees Block Compatibility
         */

        // Event listeners for fee changes on cart and checkout pages
        $(document).on('change', '.woocommerce-cart .wp-block-woocommerce-checkout-optional-fee-block input[type="checkbox"], .woocommerce-cart .wp-block-woocommerce-checkout-optional-fee-block select, .woocommerce-cart .wp-block-woocommerce-checkout-optional-fee-block input[type="radio"]', function () {
            handleFeeChange('cart');
        });


        $(document).on('change', '.woocommerce-checkout .wp-block-woocommerce-checkout-optional-fee-block input[type="checkbox"], .woocommerce-checkout .wp-block-woocommerce-checkout-optional-fee-block select, .woocommerce-checkout .wp-block-woocommerce-checkout-optional-fee-block input[type="radio"]', function () {
            handleFeeChange('checkout');
        });

        // Initial load: Apply optional fees and update stored data
        setTimeout(function () {
            if (localStorage.getItem('browser_data') !== null) {
                updateFieldsBasedOnStoredData();
            }
            if ($('.woocommerce-checkout .wp-block-woocommerce-checkout-optional-fee-block').length > 0 || $('.woocommerce-cart .wp-block-woocommerce-checkout-optional-fee-block').length > 0) {
                addFeeDataInSession();
            }
            classicAddFeeDataInSession();
            dropdown_check();
        }, 1000);


        if (localStorage.getItem('browser_data') !== null) {
            setTimeout(function () {
                classicUpdateFieldsBasedOnStoredData();
            }, 2000);

        }

        // Common event handler for cart/checkout page checkbox/dropdown changes
        function handleFeeChange() {
            addFeeDataInSession();
            blockUI();

            updateFeeData().then(function () {
                // Update mini cart fragment
                setTimeout(function () {
                    $(document.body).trigger('wc_fragment_refresh');
                }, 300);
                unblockUI();
            });
        }

        // Block UI
        function blockUI() {
            $('.wp-block-woocommerce-checkout, .wp-block-woocommerce-cart').block({
                message: null,
                overlayCSS: { background: '#fff', opacity: 0.6 }
            });
        }

        // Unblock UI
        function unblockUI() {
            $('.wp-block-woocommerce-checkout, .wp-block-woocommerce-cart').unblock();
        }

        // Collect fee data from checkboxes, selects, and radios
        function collectFeeDataOnChange() {

            const data = [];
            $('.wp-block-woocommerce-checkout-optional-fee-block')
                .find('input[type="checkbox"]:checked, select, input[type="radio"]:checked')
                .each(function () {
                    const $this = $(this);
                    const value = $this.val();
                    if ($this.is('input[type="checkbox"]')) {
                        data.push(parseInt(value));
                    } else if ((value === 'yes' || value === 'Yes')) {
                        data.push(parseInt($this.data('value')));
                    }
                });
            return data;
        }

        // Update WooCommerce Blocks checkout with fee data
        function updateFeeData() {
            const data = collectFeeDataOnChange();
            return wc.blocksCheckout.extensionCartUpdate({
                namespace: 'woocommerce-conditional-product-optional-fees',
                data:data
            });
        }

        // Add fee data to session
        function addFeeDataInSession() {
            if (!window || !window.wc || !window.wc.blocksCheckout) {
                return;
            }

            const isCartPage = $('.wp-block-woocommerce-cart').length > 0;
            const isCheckoutPage = $('.wp-block-woocommerce-checkout').length > 0;

            if (!isCartPage && !isCheckoutPage) { return; }

            blockUI();

            let sendData = [];
            const data = collectFeeDataOnChange();

            var checkbox;
            var radio;
            var dropdown;
            var dropdownValue;

            if (isCartPage) {
                // Select all checkboxes and radio buttons dynamically
                checkbox = $('.woocommerce-cart .wp-block-woocommerce-checkout-optional-fee-block').find('.input-checkbox');
                radio = $('.woocommerce-cart .wp-block-woocommerce-checkout-optional-fee-block').find('input[type="radio"]:checked');
                dropdown = $('.woocommerce-cart .wp-block-woocommerce-checkout-optional-fee-block').find('select');

                // Check if the dropdown exists and has a value
                dropdownValue = (dropdown.length > 0) ? dropdown.val() : undefined;  // Check if the dropdown exists

                // Check if the checkbox is checked, or a "Yes" radio button is selected, or a valid dropdown option is selected
                if (checkbox.is(':checked') || radio.val() === 'yes' || (dropdownValue !== undefined && dropdownValue === 'Yes')) {
                    localStorage.setItem('browser_data', JSON.stringify(data));
                    localStorage.setItem('cart_hand_checked', true);
                    sendData = data;
                } else {
                    localStorage.removeItem('browser_data');
                    localStorage.setItem('cart_hand_checked', false);
                    sendData = [];
                }

            }

            if (isCheckoutPage) {

                var cartChecked = localStorage.getItem('cart_hand_checked');
                var feeCheckbox = $('.woocommerce-checkout').find('#optional_fee_in_checkout_only').val();
                if (cartChecked !== null && cartChecked === 'false' && feeCheckbox === 'on') {
                    localStorage.removeItem('browser_data');
                    sendData = [];
                } else {
                    if (cartChecked !== null && cartChecked === 'true') {
                        localStorage.setItem('browser_data', JSON.stringify(data));
                        localStorage.setItem('cart_hand_checked', false);
                        sendData = data;
                    } else {
                        checkbox = $('.woocommerce-checkout .wp-block-woocommerce-checkout-optional-fee-block').find('.input-checkbox');
                        radio = $('.woocommerce-checkout .wp-block-woocommerce-checkout-optional-fee-block').find('input[type="radio"]:checked');
                        dropdown = $('.woocommerce-checkout .wp-block-woocommerce-checkout-optional-fee-block').find('select');
                        dropdownValue = (dropdown.length > 0) ? dropdown.val() : undefined;

                        if (checkbox.is(':checked') || radio.val() === 'yes' || (dropdownValue !== undefined && dropdownValue === 'Yes')) {
                            localStorage.setItem('browser_data', JSON.stringify(data));
                            localStorage.setItem('cart_hand_checked', true);
                            sendData = data;
                        } else {
                            localStorage.removeItem('browser_data');
                            localStorage.setItem('cart_hand_checked', false);
                            sendData = [];
                        }
                    }
                }
            }

            wc.blocksCheckout.extensionCartUpdate({
                namespace: 'woocommerce-conditional-product-optional-fees',
                data: sendData,
            }).then(function () {
                setTimeout(function () {
                    $(document.body).trigger('wc_fragment_refresh');
                }, 300);
                unblockUI();
            });
        }

        // Update fields based on stored data
        function updateFieldsBasedOnStoredData() {

            const isCheckoutPage = $('.wp-block-woocommerce-checkout').length > 0;
            if (!isCheckoutPage) { return; }

            const storedData = JSON.parse(localStorage.getItem('browser_data')) || [];

            $('.wp-block-woocommerce-checkout-optional-fee-block input[type="checkbox"], .wp-block-woocommerce-checkout-optional-fee-block input[type="radio"]').each(function () {
                const value = $(this).is('input[type="radio"]') ? $(this).data('value') : $(this).val();
                updateInputState($(this), value);
            });

            $('.wp-block-woocommerce-checkout-optional-fee-block select').each(function () {
                const selectedValue = parseInt($(this).data('value'));
                $(this).val(storedData.includes(selectedValue) ? 'Yes' : 'No');
            });
        }

        /**
         * Optional Fees Classic Method
         */ 

        // Clear localStorage if the cart is empty
        $(document.body).on('wc_cart_emptied', function () {
            clearLocalStorage();
        });

        // Function to clear localStorage
        function clearLocalStorage() {
            localStorage.removeItem('browser_data');
            localStorage.removeItem('cart_hand_checked');
        }

        // Event listeners for fee changes on cart and checkout pages
        $(document).on('change', '.woocommerce-cart .optional_fee_container input[type="checkbox"], .woocommerce-cart .optional_fee_container select, .woocommerce-cart .optional_fee_container input[type="radio"]', function () {
            classicHandleFeeChange();
        });

        $(document).on('change', '.woocommerce-checkout .optional_fee_container input[type="checkbox"], .woocommerce-checkout .optional_fee_container select, .woocommerce-checkout .optional_fee_container input[type="radio"]', function () {
            classicHandleFeeChange('checkout');
            dropdown_check();
            $('body').trigger('update_checkout');
        });


        function classicHandleFeeChange() {
            classicAddFeeDataInSession();

            const isCartPage = $('.woocommerce-cart').length > 0;

            if (isCartPage) {
                setTimeout(function () {
                    classicUpdateFieldsBasedOnStoredData();
                }, 2000);
            }
        }

        // Collect fee data from checkboxes, selects, and radios classic method
        function classicCollectFeeData() {
            const data = [];
            $('.optional_fee_container')
                .find('input[type="checkbox"]:checked, select, input[type="radio"]:checked')
                .each(function () {
                    const $this = $(this);
                    const value = $this.val(); // Value of the input, select, or radio

                    if ($this.is('input[type="checkbox"]')) {
                        // For checkboxes, we expect a numeric value (fee ID)
                        if (value && !isNaN(value)) {
                            data.push(parseInt(value, 10));
                        }
                    } else if ($this.is('select')) {
                        // For select inputs, check if the value is 'yes'
                        if (value.toLowerCase() === 'yes') {
                            const feeValue = $this.data('value'); // Using the data-value attribute
                            if (feeValue && !isNaN(feeValue)) {
                                data.push(parseInt(feeValue, 10));
                            }
                        }
                    } else if ($this.is('input[type="radio"]')) {
                        // For radios, we expect a numeric value for 'Yes' option
                        if (value && !isNaN(value)) {
                            data.push(parseInt(value, 10));
                        }
                    }
                });
            return data;
        }

        // Add fee data to session classic method
        function classicAddFeeDataInSession() {

            if (window && window.wc && window.wc.blocksCheckout) {
                return;
            }

            const isCartPage = $('.woocommerce-cart').length > 0;
            const isCheckoutPage = $('.woocommerce-checkout').length > 0;
            if (!isCartPage && !isCheckoutPage) { return; }

            let sendData = [];
            const data = classicCollectFeeData(); // Get the current fee data
            if (isCartPage) {
                                
                // Get previously stored data from localStorage
                const previousCartData = JSON.parse(localStorage.getItem('browser_data')) || [];

                // Check if there is a change in data
                const isDataChanged = JSON.stringify(data) !== JSON.stringify(previousCartData);

                // Only update localStorage and trigger 'wc_update_cart' if data has changed
                if (isDataChanged) {

                    // Store the new data in localStorage
                    localStorage.setItem('browser_data', JSON.stringify(data));
                    sendData = data;

                    if ($('.cart_item').length > 0) {
                        
                        // Send the array of fee IDs via AJAX only if there's a change
                        $.ajax({
                            type: 'POST',
                            url: woocommerce_params.ajax_url,
                            data: {
                                action: 'wcpfc_pro_cart_optional_fees_ajax__premium_only',
                                fees_ids: sendData, // Send array of fee IDs
                            },
                            success: function (response) {
                                if (response) {
                                    // Trigger 'wc_update_cart' only if the data has changed
                                    $(document.body).trigger('wc_update_cart');
                                }
                            }
                        });
                    }
                }
            } else if (isCheckoutPage) {
                sendData = JSON.parse(localStorage.getItem('browser_data')) || [];
                $('body').trigger('update_checkout');
            }
        }

        function updateInputState(inputElement, storedData) {
            const value = parseInt(inputElement.val());
            inputElement.prop('checked', storedData.includes(value));
        }

        // Update fields based on stored data
        function classicUpdateFieldsBasedOnStoredData() {
            if (!window.wc || !window.wc.blocksCheckout) {
                return;
            }
        
            const storedData = JSON.parse(localStorage.getItem('browser_data')) || [];
        
            $('.optional_fee_container input[type="checkbox"], .optional_fee_container input[type="radio"]').each(function () {
                updateInputState($(this), storedData);
            });
        
            $('.optional_fee_container select').each(function () {
                const selectedValue = parseInt($(this).data('value'));
                $(this).val(storedData.includes(selectedValue) ? 'yes' : 'no');
            });
        
            $('body').trigger('update_checkout');
        }
        

        function dropdown_check() {
            $('.input-dropdown').each(function () {
                $(this).next(':hidden').remove();
                if ('yes' === $(this).val() || 'Yes' === $(this).val()) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'wef_fees_id_array_' + $(this).data('value') + '[]',
                        value: $(this).data('value'),
                    }).insertAfter($(this));
                }
            });
        }

    });

})(jQuery);
