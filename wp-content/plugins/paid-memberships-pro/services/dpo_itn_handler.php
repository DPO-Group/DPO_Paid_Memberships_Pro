<?php
/**
 * Copyright (c) 2024 DPO Pay
 *
 * class.dpo_itn_handler.php
 *
 * @author     App Inlet
 */

require_once(dirname(__FILE__) . "/dpo.php");
//in case the file is loaded directly
if (!defined("WP_USE_THEMES")) {
    global $isapage;
    $isapage = true;

    define('WP_USE_THEMES', false);
    require_once(dirname(__FILE__) . '/../../../../wp-load.php');
}

//some globals
global $wpdb, $gateway_environment, $logstr;
$logstr = "";   //will put debug info here and write to ipnlog.txt
const LOCATION = 'Location: ';

ipnlog('DPO ITN call received');

$test_mode   = pmpro_getOption("gateway_environment") == 'yes';
$success_url = pmpro_url("confirmation", "?level=" . $order->membership_level->id);
$fail_url    = pmpro_url("levels");
$dpo         = new Dpo($test_mode);

$transactionToken = filter_var($_REQUEST['TransactionToken'], FILTER_SANITIZE_STRING);
$reference        = filter_var($_REQUEST['CompanyRef'], FILTER_SANITIZE_STRING);
$companyToken     = $dpo->get_company_token();
$data             = [
    'companyToken' => $companyToken,
    'transToken'   => $transactionToken,
];

try {
    $query          = $dpo->verifyToken($data);
    $verified       = new SimpleXMLElement($query);
    $transaction_id = $verified->CompanyRef->__toString();
    if ($verified->Result == '000' && $reference == $verified->CompanyRef->__toString()) {
        // Approved
        $morder = new MemberOrder($verified->CompanyRef->__toString());
        $morder->getMembershipLevel();
        $morder->getUser();

        //update membership
        if (pmpro_itnChangeMembershipLevel($transaction_id, $morder)) {
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

/*
    Add message to ipnlog string
*/
function ipnlog($s)
{
    global $logstr;
    $logstr .= "\t" . $s . "\n";
}

/*
    Output ipnlog and exit;
*/
function pmpro_ipnExit()
{
    global $logstr;

    //for log
    if ($logstr) {
        $logstr = "Logged On: " . date("m/d/Y H:i:s") . "\n" . $logstr . "\n-------------\n";

        //log?

        echo $logstr;
        $loghandle = fopen(dirname(__FILE__) . "/../logs/dpo_itn.txt", "a+");
        fwrite($loghandle, $logstr);
        fclose($loghandle);
    }

    exit;
}


/*
    Change the membership level. We also update the membership order to include filtered valus.
*/
function pmpro_itnChangeMembershipLevel($txn_id, &$morder)
{
    global $wpdb;
    //filter for level
    $morder->membership_level = apply_filters("pmpro_ipnhandler_level", $morder->membership_level, $morder->user_id);

    //fix expiration date
    if (!empty($morder->membership_level->expiration_number)) {
        $enddate = "'" . date(
                "Y-m-d",
                strtotime(
                    "+ " . $morder->membership_level->expiration_number . " " . $morder->membership_level->expiration_period
                )
            ) . "'";
    } else {
        $enddate = "NULL";
    }

    //get discount code
    $morder->getDiscountCode();
    if (!empty($morder->discount_code)) {
        //update membership level
        $morder->getMembershipLevel(true);
        $discount_code_id = $morder->discount_code->id;
    } else {
        $discount_code_id = "";
    }

    //set the start date to current_time('timestamp') but allow filters
    $startdate = apply_filters(
        "pmpro_checkout_start_date",
        "'" . current_time('mysql') . "'",
        $morder->user_id,
        $morder->membership_level
    );

    //custom level to change user to
    $custom_level = array(
        'user_id'         => $morder->user_id,
        'membership_id'   => $morder->membership_level->id,
        'code_id'         => $discount_code_id,
        'initial_payment' => $morder->membership_level->initial_payment,
        'billing_amount'  => $morder->membership_level->billing_amount,
        'cycle_number'    => $morder->membership_level->cycle_number,
        'cycle_period'    => $morder->membership_level->cycle_period,
        'billing_limit'   => $morder->membership_level->billing_limit,
        'trial_amount'    => $morder->membership_level->trial_amount,
        'trial_limit'     => $morder->membership_level->trial_limit,
        'startdate'       => $startdate,
        'enddate'         => $enddate
    );

    global $pmpro_error;
    if (!empty($pmpro_error)) {
        echo $pmpro_error;
        ipnlog($pmpro_error);
    }

    //change level and continue "checkout"
    if (pmpro_changeMembershipLevel($custom_level, $morder->user_id) !== false) {
        //update order status and transaction ids
        $morder->status                 = "success";
        $morder->payment_transaction_id = $txn_id;
        if (!empty($_GET['TransactionToken'])) {
            $morder->subscription_transaction_id = $_GET['CompanyRef'];
        } else {
            $morder->subscription_transaction_id = "";
        }
        $morder->saveOrder();

        //add discount code use
        if (!empty($discount_code) && !empty($use_discount_code)) {
            $wpdb->query(
                "INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . $discount_code_id . "', '" . $morder->user_id . "', '" . $morder->id . "', '" . current_time(
                    'mysql'
                ) . ""
            );
        }

        //save first and last name fields
        if (!empty($_POST['first_name'])) {
            $old_firstname = get_user_meta($morder->user_id, "first_name", true);
            if (!empty($old_firstname)) {
                update_user_meta($morder->user_id, "first_name", $_POST['first_name']);
            }
        }
        if (!empty($_POST['last_name'])) {
            $old_lastname = get_user_meta($morder->user_id, "last_name", true);
            if (!empty($old_lastname)) {
                update_user_meta($morder->user_id, "last_name", $_POST['last_name']);
            }
        }

        //hook
        do_action("pmpro_after_checkout", $morder->user_id, $morder);

        //setup some values for the emails
        if (!empty($morder)) {
            $invoice = new MemberOrder($morder->id);
        } else {
            $invoice = null;
        }

        // cancel order previous DPO subscription if applicable
        $oldSub = $wpdb->get_var(
            "SELECT paypal_token FROM $wpdb->pmpro_membership_orders WHERE user_id = '" . $morder->user_id . "' AND status = 'cancelled' ORDER BY timestamp DESC LIMIT 1"
        );

        return true;
    } else {
        return false;
    }
}
