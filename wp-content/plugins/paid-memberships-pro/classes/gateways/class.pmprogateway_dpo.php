<?php
/**
 * class.pmprogateway_dpo.php
 *
 * @author     App Inlet
 */

require_once(dirname(__FILE__) . "/class.pmprogateway.php");
require_once(dirname(__FILE__) . "../../../services/dpo.php");

//load classes init method
add_action('init', array('PMProGateway_dpo', 'init'));
add_action('admin_post_dpo_pmpro_wp_payment_success', 'dpo_pmpro_payment_success');
add_action('admin_post_dpo_pmpro_wp_payment_success', 'dpo_pmpro_payment_success');

class PMProGateway_dpo
{
    /**
     * Run on WP init
     *
     * @since 1.8
     */
    static function init()
    {
        //make sure DPO is a gateway option
        add_filter('pmpro_gateways', array('PMProGateway_dpo', 'pmpro_gateways'));

        //add fields to payment settings
        add_filter('pmpro_payment_options', array('PMProGateway_dpo', 'pmpro_payment_options'));

        add_filter('pmpro_payment_option_fields', array('PMProGateway_dpo', 'pmpro_payment_option_fields'), 10, 2);

        add_filter('pmpro_include_billing_address_fields', '__return_false');
        add_filter('pmpro_include_payment_information_fields', '__return_false');

        add_filter('pmpro_required_billing_fields', '__return_empty_array');
        add_filter(
            'pmpro_checkout_default_submit_button',
            array('PMProGateway_dpo', 'pmpro_checkout_default_submit_button')
        );
        add_filter(
            'pmpro_checkout_before_change_membership_level',
            array('PMProGateway_dpo', 'pmpro_checkout_before_change_membership_level'),
            10,
            2
        );
    }

    /**
     * Make sure that this gateway is in the gateways list
     *
     * @since 1.8
     */
    static function pmpro_gateways($gateways)
    {
        if (empty($gateways['dpo'])) {
            $gateways['dpo'] = __('DPO', 'pmpro');
        }

        return $gateways;
    }

    /**
     * Get a list of payment options that the this gateway needs/supports.
     *
     * @since 1.8
     */
    static function getGatewayOptions()
    {
        $options = array(
            'dpo_company_token',
            'dpo_company_token',
            'dpo_service_type',
            'dpo_success_url',
            'dpo_failure_url',
            'dpo_recaptcha_key',
            'dpo_recapture_secret',
            'dpo_item_details',
            'dpo_customer_dial_code',
            'dpo_customer_zip',
            'dpo_customer_address',
            'dpo_customer_city',
            'dpo_customer_phone',
        );

        return $options;
    }

    /**
     * Set payment options for payment settings page.
     *
     * @since 1.8
     */
    static function pmpro_payment_options($options)
    {
        //get stripe options
        $dpo_options = self::getGatewayOptions();

        //merge with others.
        $options = array_merge($dpo_options, $options);

        return $options;
    }

    /**
     * Display fields for this gateway's options.
     *
     * @since 1.8
     */
    static function pmpro_payment_option_fields($values, $gateway)
    {
        ?>

        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_company_token"><?php
                    _e('Company Token', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_company_token" name="dpo_company_token"
                       value="<?php
                       echo esc_attr($values['dpo_company_token']); ?>"/>&nbsp;<small><?php
                    _e('Enter your DPO Company (Merchant) Token'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_service_type"><?php
                    _e('Service Type', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_service_type" name="dpo_service_type"
                       value="<?php
                       echo esc_attr($values['dpo_service_type']); ?>"/>&nbsp;<small><?php
                    _e(
                        'Insert a default service type number according to the options accepted by the DPO Pay'
                    ); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_success_url"><?php
                    _e('Success URL', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_success_url" name="dpo_success_url"
                       value="<?php
                       echo esc_attr($values['dpo_success_url']); ?>"/>&nbsp;<small><?php
                    _e('The URL (full or slug) to which the user is redirected on payment success'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_failure_url"><?php
                    _e('Failure URL', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_failure_url" name="dpo_failure_url"
                       value="<?php
                       echo esc_attr($values['dpo_failure_url']); ?>"/>&nbsp;<small><?php
                    _e('The URL (full or slug) to which the user is redirected on payment failure'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_recaptcha_key"><?php
                    _e('Recaptcha Key', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_recaptcha_key" name="dpo_recaptcha_key"
                       value="<?php
                       echo esc_attr($values['dpo_recaptcha_key']); ?>"/>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_recapture_secret"><?php
                    _e('Recaptcha Secret', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_recapture_secret" name="dpo_recapture_secret"
                       value="<?php
                       echo esc_attr($values['dpo_recapture_secret']); ?>"/>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_item_details"><?php
                    _e('Item Details', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_item_details" name="dpo_item_details"
                       value="<?php
                       echo esc_attr($values['dpo_item_details']); ?>"/>
                &nbsp;<small><?php
                    _e('ItemDetails field will be used to create token'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_customer_dial_code"><?php
                    _e('Customer Dial Code', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_customer_dial_code" name="dpo_customer_dial_code"
                       value="<?php
                       echo esc_attr($values['dpo_customer_dial_code']); ?>"/>
                &nbsp;<small><?php
                    _e('CustomerDialCode field data will be used to create token'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_customer_zip"><?php
                    _e('Customer Zip', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_customer_zip" name="dpo_customer_zip"
                       value="<?php
                       echo esc_attr($values['dpo_customer_zip']); ?>"/>
                &nbsp;<small><?php
                    _e('CustomerZip field data will be used to create token'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_customer_address"><?php
                    _e('Customer Address', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_customer_address" name="dpo_customer_address"
                       value="<?php
                       echo esc_attr($values['dpo_customer_address']); ?>"/>
                &nbsp;<small><?php
                    _e('CustomerAddress field data will be used to create token'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_customer_city"><?php
                    _e('Customer City', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_customer_city" name="dpo_customer_city"
                       value="<?php
                       echo esc_attr($values['dpo_customer_city']); ?>"/>
                &nbsp;<small><?php
                    _e('CustomerCity field data will be used to create token'); ?></small>
            </td>
        </tr>
        <tr class="gateway gateway_dpo" <?php
        if ($gateway != "dpo") { ?>style="display: none;"<?php
        } ?>>
            <th scope="row" valign="top">
                <label for="dpo_customer_phone"><?php
                    _e('Customer Phone', 'pmpro'); ?>:</label>
            </th>
            <td>
                <input id="dpo_customer_phone" name="dpo_customer_phone"
                       value="<?php
                       echo esc_attr($values['dpo_customer_phone']); ?>"/>
                &nbsp;<small><?php
                    _e('CustomerPhone field data will be used to create token'); ?></small>
            </td>
        </tr>
        <?php
    }

    /**
     * Remove required billing fields
     *
     * @since 1.8
     */
    static function pmpro_required_billing_fields($fields)
    {
        return array();
    }

    /**
     * Swap in our submit buttons.
     *
     * @since 1.8
     */
    static function pmpro_checkout_default_submit_button($show)
    {
        global $gateway, $pmpro_requirebilling;

        //show our submit buttons
        $showDefaultButton = false;

        $str = '<span id="pmpro_dpo_checkout"';
        if ($gateway != "dpo" || !$pmpro_requirebilling) {
            $showDefaultButton = true;
            $str               .= 'style="display: none;"';
        }
        $str .= ' >
                        <input type="hidden" name="submit-checkout" value="1" />
                        <p>
                            <strong>Check Out with</strong>
                        </p>
                        <p>
                            <input type="image" style="border:1px solid #eee;padding:5px;border-radius:5px;width: 102px" value="' . _e(
                '',
                'pmpro'
            ) . '&raquo;"
                                src="' . esc_url(PMPRO_URL . '/images/dpo-pay.svg') . '"/>' .
                '</p>
                        <strong>NOTE:</strong> if changing a subscription it may take a minute or two to reflect. Please also log in to your dpo
                        account to ensure the old subscription is cancelled.
                    </span>';

        echo $str;

        return $showDefaultButton;
    }

    /**
     * Instead of change membership levels, send users to dpo to pay.
     *
     * @since 1.8
     */
    static function pmpro_checkout_before_change_membership_level($user_id, $morder)
    {
        global $discount_code_id;

        //if no order, no need to pay
        if (empty($morder)) {
            return;
        }

        $morder->user_id = $user_id;
        $morder->saveOrder();

        //save discount code use
        if (!empty($discount_code_id)) {
            $wpdb->query(
                "INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . $discount_code_id . "', '" . $user_id . "', '" . $morder->id . "', now())"
            );
        }

        $morder->Gateway->sendToDPO($morder);
    }

    function process(&$order)
    {
        if (empty($order->code)) {
            $order->code = $order->getRandomCode();
        }

        //clean up a couple values
        $order->payment_type = "DPO";
        $order->CardType     = "";
        $order->cardtype     = "";

        //just save, the user will go to DPO to pay
        $order->status = "review";
        $order->saveOrder();

        return true;
    }

    /**
     * @param $order
     */
    function sendToDPO(&$order)
    {
        global $pmpro_currency;
        //build DPO Redirect
        $environment = pmpro_getOption("gateway_environment");
        $email       = $order->Email;
        $amount      = $order->InitialPayment;

        if ($email == '') {
            header('Location:' . $_SERVER['HTTP_REFERER']);

            return;
        }

        $dpo = new Dpo(false);

        $reference = $order->code;
        $eparts    = explode('@', $email);

        $post_id = wp_insert_post(
            [
                'post_type'   => 'dpo_standalone_order',
                'post_status' => 'dposa_pending',
                'post_title'  => "DPOSA_order_$reference",
            ]
        );

        $data = array(
            'orderItems'        => $dpo->getOrderItems(),
            'companyToken'      => pmpro_getOption("dpo_company_token"),
            'serviceType'       => pmpro_getOption("dpo_service_type"),
            'paymentAmount'     => $amount,
            'paymentCurrency'   => $pmpro_currency,
            'companyRef'        => $reference,
            'customerDialCode'  => $dpo->dpo_standalone_customer_dial_code(),
            'customerZip'       => $dpo->dpo_standalone_customer_zip(),
            'customerCountry'   => 'KE',
            'customerFirstName' => $eparts[0],
            'customerLastName'  => $eparts[1],
            'customerAddress'   => $dpo->dpo_standalone_customer_address(),
            'customerCity'      => $dpo->dpo_standalone_customer_city(),
            'customerPhone'     => $dpo->dpo_standalone_customer_phone(),
            'customerEmail'     => $email,
            'redirectURL'       => PMPRO_URL . "/services/dpo_itn_handler.php",
            'backURL'           => pmpro_url("levels"),
        );

        $token = $dpo->createToken($data);
        if ($token['success'] !== true) {
            // Error
        }

        $data1                 = [];
        $data1['companyToken'] = $data['companyToken'];
        $transToken            = $data1['transToken'] = $token['transToken'];
        $transactionId         = $token['transRef'];
        $dpoPay                = $dpo->get_pay_url() . '?ID=' . $transToken;

        update_post_meta($post_id, 'dposa_transaction_token', $transToken);
        update_post_meta($post_id, 'dposa_transaction_id', $transactionId);
        update_post_meta($post_id, 'dposa_order_reference', $reference);
        update_post_meta($post_id, 'dposa_order_data', $data);

        // Verify the token
        $result = $dpo->verifyToken($data1);
        if ($result != '') {
            $result = new SimpleXMLElement($result);
        }
        if (!is_string($result) && $result->Result->__toString() == '900') {
            // Redirect to payment portal
            echo <<<HTML
<p>Kindly wait while you're redirected to the DPO Pay ...</p>
<form action="$dpoPay" method="post" name="dpo_redirect">
        <input name="transToken" type="hidden" value="$transToken" />
</form>
<script type="text/javascript">document.forms['dpo_redirect'].submit();</script>
HTML;
            die;
        } else {
            // Error
        }
        exit;
    }

    function dpo_pmpro_payment_success()
    {
        $test_mode   = pmpro_getOption("gateway_environment") == 'yes';
        $success_url = pmpro_url("confirmation", "?level=" . $order->membership_level->id);
        $fail_url    = pmpro_url("levels");
        $dpo         = new Dpo($test_mode);

        $post_id          = filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT);
        $transactionToken = filter_var($_REQUEST['TransactionToken'], FILTER_SANITIZE_STRING);
        $reference        = filter_var($_REQUEST['CompanyRef'], FILTER_SANITIZE_STRING);
        $companyToken     = $dpo->get_company_token();
        $data             = [
            'companyToken' => $companyToken,
            'transToken'   => $transactionToken,
        ];

        try {
            $query    = $dpo->verifyToken($data);
            $verified = new SimpleXMLElement($query);
            if ($verified->Result == '000' && $reference == $verified->CompanyRef->__toString()) {
                // Approved
                $this->subscribe($order);

                $morder = new MemberOrder($verified->TransactionRef->__toString());
                $morder->getMembershipLevel();
                $morder->getUser();

                //update membership
                if (pmpro_itnChangeMembershipLevel($post_id, $morder)) {
                    ipnlog("Checkout processed (" . $morder->code . ") success!");
                } else {
                    ipnlog("ERROR: Couldn't change level for order (" . $morder->code . ").");
                }

                header(LOCATION . $success_url);
                die();
            } else {
                $status_desc = $verified->ResultExplanation->__toString();
                update_post_meta($post_id, 'dposa_order_status', 'failed');
                update_post_meta($post_id, 'dposa_order_failed_reason', $status_desc);
                $qstring = "?reference=$reference&reason=$status_desc";
                header(LOCATION . $fail_url);
                die();
            }
        } catch (Exception $exception) {
            $qstring = "?reference=$reference&reason=" . esc_url('The transaction could not be verified');
            header(LOCATION . $fail_url . $qstring);
            die();
        }
    }

    function dpo_pmpro_payment_failure()
    {
        $test_mode = pmpro_getOption("gateway_environment") == 'yes';
        $dpo       = new Dpo($test_mode);

        $post_id          = filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT);
        $transactionToken = filter_var($_REQUEST['TransactionToken'], FILTER_SANITIZE_STRING);
        $reference        = filter_var($_REQUEST['CompanyRef'], FILTER_SANITIZE_STRING);
        $companyToken     = $dpo->get_company_token();
        $data             = [
            'companyToken' => $companyToken,
            'transToken'   => $transactionToken,
        ];

        try {
            $query       = $dpo->verifyToken($data);
            $verified    = new SimpleXMLElement($query);
            $status_desc = $verified->ResultExplanation->__toString();
            update_post_meta($post_id, 'dposa_order_status', 'failed');
            update_post_meta($post_id, 'dposa_order_failed_reason', $status_desc);
            $qstring = "?reference=$reference&reason=$status_desc";
            header(LOCATION . site_url() . '/' . get_option('dpo_standalone_failure_url') . $qstring);
            die();
        } catch (Exception $exception) {
            $qstring = "?reference=$reference&reason=" . esc_url('The transaction could not be verified');
            header(LOCATION . site_url() . '/' . get_option('dpo_standalone_failure_url') . $qstring);
            die();
        }
    }

    function subscribe(&$order)
    {
        global $pmpro_currency;

        if (empty($order->code)) {
            $order->code = $order->getRandomCode();
        }

        //filter order before subscription. use with care.
        $order = apply_filters("pmpro_subscribe_order", $order, $this);

        //taxes on initial amount
        $initial_payment     = $order->InitialPayment;
        $initial_payment_tax = $order->getTaxForPrice($initial_payment);
        $initial_payment     = round((float)$initial_payment + (float)$initial_payment_tax, 2);

        //taxes on the amount
        $amount     = $order->PaymentAmount;
        $amount_tax = $order->getTaxForPrice($amount);

        $order->status                      = "success";
        $order->payment_transaction_id      = $order->code;
        $order->subscription_transaction_id = $order->code;

        //update order
        $order->saveOrder();

        return true;
    }

    function cancel(&$order)
    {
        $nvpStr = "";
        $nvpStr .= "&PROFILEID=" . urlencode($order->subscription_transaction_id) . "&ACTION=Cancel&NOTE=" . urlencode(
                "User requested cancel."
            );
    }


}
