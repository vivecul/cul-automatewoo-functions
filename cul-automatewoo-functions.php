<?php

/**
 * A Plugin that adds CUL's Custom functions to AutomateWoo
 *
 * @package cul-automatewoo-functions
 *
 * Plugin Name:       CUL - AutomateWoo Custom Functions
 * Description:       Plugin that adds CUL's Custom functions to AutomateWoo
 * Version:           1.0
 * Author:            CUL
 */


/**
 * ALL AUTOMATEWOO CUSTOM FUNCTIONS
 */

/**
 * Custom function to create renewal ordervia AutomateWoo action
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_create_renewal_order( $workflow ) {

    // retrieve the workflow data from the data layer
    $sub = $workflow->data_layer()->get_subscription();
    //$customer = $workflow->data_layer()->get_customer();

    // Create the renewal order
    wcs_create_renewal_order( $sub );
    $sub->add_order_note( __( 'AutomateWoo Create Renewal Order.', 'woocommerce-subscriptions' ), false, true );
    $workflow->log_action_note( $workflow , __( 'Renewal order created', 'automatewoo' ) );
}

/**
 * Custom function to create change subscription status when late payment is made
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_change_late_payment_status( $workflow ) {

    // retrieve the workflow data from the data layer
    $sub = $workflow->data_layer()->get_subscription();
    
    // get al related orders to subscription array
    $relared_orders_ids_array = $sub->get_related_orders();

    // count pending orders
    $pending_order_count = 0;
    foreach ($relared_orders_ids_array as $order_id){
        $order = wc_get_order( $order_id );
        //$order_status  = $order->get_status();

        if ( $order->has_status('pending') || $order->has_status('bad-payment') || $order->has_status('unreachable') || $order->has_status('late-payment') ) {
        $pending_order_count += 1;
        }
    }

    // change to proper late status
    if ($pending_order_count == 0) {
        $sub->update_status( 'active' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to active', 'automatewoo' ) );
    }
    else if ($pending_order_count == 1) {
        $sub->update_status( 'on-hold' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to on-hold', 'automatewoo' ) );
    }
    else if ($pending_order_count == 2) {
        $sub->update_status( 'late-payment-30' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to late-payment-30', 'automatewoo' ) );
    }
    else if ($pending_order_count == 3) {
        $sub->update_status( 'late-payment-60' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to late-payment-60', 'automatewoo' ) );
    }
    else if ($pending_order_count == 4) {
        $sub->update_status( 'late-payment-90' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to late-payment-90', 'automatewoo' ) );
    }
    else if ($pending_order_count == 5) {
        $sub->update_status( 'late-payment-120' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to late-payment-120', 'automatewoo' ) );
    }
    else if ($pending_order_count == 6) {
        $sub->update_status( 'late-payment-150' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to late-payment-150', 'automatewoo' ) );
    }
    else if ($pending_order_count == 7) {
        $sub->update_status( 'late-payment-180' );
        $sub->add_order_note( __( 'AutomateWoo Change Late Payment Status.', 'woocommerce-subscriptions' ), false, true );
        $workflow->log_action_note( $workflow , __( 'Late Payment order paid Subscription status changed to late-payment-180', 'automatewoo' ) );
    }

}

/**
 * Custom function to change subscription status to wc-expired-offer when and offer is completed and log offer id and order id in the subscription metadata
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_subscrition_status_finalized_offer( $workflow ) {
    //get order id from data layer
    $order = $workflow->data_layer()->get_order();
    $order_id = $order->get_id();
    
    // get offer id from order
    global $wpdb;
    $offer_ids_query = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'offer_order_id' AND meta_value = $order_id");
    $offer_id = $offer_ids_query[0]->post_id;

    //get sub id from postmeta of offer
    $subscription_id = get_post_meta( $offer_id, 'offer_subscription_id', true );

    //change sub status with sub id
    $subscription_obj = wcs_get_subscription($subscription_id);
    $subscription_obj->update_status( 'expired' );
    $subscription_obj->update_status( 'expired-offer' );

    //Add offer and order to subscription postmeta
    add_post_meta( $subscription_id, 'aw_offer_order_id', $order_id, true );
    add_post_meta( $subscription_id, 'aw_offer_id', $offer_id, true );

    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'subscription moved to finalized with offer (expired-offer) with order id:'.$order_id. ' and offer id: '.$offer_id.' Subscription id: '.$subscription_id, 'automatewoo' ) );
}

/**
 * Custom function to record late payments over 30 days on user and subscription meta
 * Adds aw_late_payments to postmeta
 * Adds aw_total_late_payments to usermeta
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_subscrition_add_late_payment_meta( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();
    $user_id = get_metadata( 'post', $subscription_id, '_customer_user', true );

    // Adds aw_late_payments postmeta to the subscription
    if(metadata_exists('post', $subscription_id, 'aw_late_payments')){
        echo $late_payments = get_post_meta($subscription->get_id(), 'aw_late_payments', true ) + 1;
        update_post_meta( $subscription->get_id(), 'aw_late_payments', $late_payments );
    }

    else {
        add_post_meta( $subscription->get_id(), 'aw_late_payments', 1 );
    }

    // Adds aw_total_late_payments meta to the user
    if(metadata_exists('user', $user_id, 'aw_total_late_payments')){
        $user_late_payments = get_user_meta($user_id, 'aw_total_late_payments', true ) + 1;
        update_user_meta( $user_id, 'aw_total_late_payments', $late_payments );
    }
    else {
        add_user_meta( $user_id, 'aw_total_late_payments', 1 );
    }

    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'Late Payment Recorded in usermeta and postmeta', 'automatewoo' ) );
    
}

/**
 * Custom function to record late payments over 60 days on user and subscription meta
 * Adds aw_late_payments to postmeta
 * Adds aw_total_late_payments to usermeta
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_subscrition_add_late_payment_60_meta( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();
    $user_id = get_metadata( 'post', $subscription_id, '_customer_user', true );

    // Adds aw_late_payments postmeta to the subscription
    if(metadata_exists('post', $subscription_id, 'aw_late_payments_60')){
        $late_payments = get_post_meta($subscription->get_id(), 'aw_late_payments_60', true ) + 1;
        update_post_meta( $subscription->get_id(), 'aw_late_payments_60', $late_payments);
    }

    else {
        add_post_meta( $subscription->get_id(), 'aw_late_payments_60', 1 );
    }

    // Adds aw_total_late_payments meta to the user
    if(metadata_exists('user', $user_id, 'aw_total_late_payments_60')){
        $user_late_payments = get_user_meta($user_id, 'aw_total_late_payments_60', true ) + 1;
        update_user_meta( $user_id, 'aw_total_late_payments_60', $late_payments );
    }
    else {
        add_user_meta( $user_id, 'aw_total_late_payments_60', 1 );
    }

    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'Late Payment 60 Recorded in usermeta and postmeta', 'automatewoo' ) );
    
}

/**
 * Custom function to record late payments over 90 days on user and subscription meta
 * Adds aw_late_payments to postmeta
 * Adds aw_total_late_payments to usermeta
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_subscrition_add_late_payment_90_meta( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();
    $user_id = get_metadata( 'post', $subscription_id, '_customer_user', true );

    // Adds aw_late_payments postmeta to the subscription
    if(metadata_exists('post', $subscription_id, 'aw_late_payments_90')){
        $late_payments = get_post_meta($subscription->get_id(), 'aw_late_payments_90', true ) + 1;
        update_post_meta( $subscription->get_id(), 'aw_late_payments_90', $late_payments );
    }

    else {
        add_post_meta( $subscription->get_id(), 'aw_late_payments_90', 1 );
    }

    // Adds aw_total_late_payments meta to the user
    if(metadata_exists('user', $user_id, 'aw_total_late_payments_90')){
        $user_late_payments = get_user_meta($user_id, 'aw_total_late_payments_90', true ) + 1;
        update_user_meta( $user_id, 'aw_total_late_payments_90', $late_payments );
    }
    else {
        add_user_meta( $user_id, 'aw_total_late_payments_90', 1 );
    }

    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'Late Payment 90 Recorded in usermeta and postmeta', 'automatewoo' ) );
    
}

/***
** Functions that sets the next payment date one month after the renewal order was creted
** Uses saved data in aw_next_payment
***/
function update_next_payment_date( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();
    
    //Sets nex payment time 1 month later minus 5 hours to account for server time and payment won't go to the next day if created after 19h
    $next_month = date("Y-m-d", strtotime("+1 month -5 hour")).' 12:00:00';

    
    $subscription->update_dates(array('next_payment' => $next_month));
    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'Next payment date updated to: '.$next_month, 'automatewoo' ) );
    //Leave note
    //$subscription->add_order_note('Next payment date updated to: '.$next_month);
       
}

/**
 * Custom function to add subscription meta data with the number of periods in the current subscription
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_subscription_length_meta( $workflow ) {
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_length = wcs_estimate_periods_between( $subscription->get_time( 'start' ), $subscription->get_time( 'end' ), $subscription->get_billing_period() );

     if(metadata_exists('post', $subscription_id, 'aw_mp_renter')){
        $mp_renter = get_metadata( 'post', $subscription_id, 'aw_mp_renter', true );
    }
    else {
        $mp_renter = 'cul';
    }
    
     // Get al rules for non marketplace and each marketplace player
    /*
    * Rules for CUL (non marketplace)
    * 6 Months resubscribes 6 months
    * 9 Months resubscribes 5 months 
    * 12 Months resubscribes 4 months 
    * 18 Months resubscribes to 0 months 
    **/
    if ($mp_renter == 'cul'){
        
        if ($subscription_length == '6'){
            $resubscription_months = 6;
        }
        else if ($subscription_length == '9'){
            $resubscription_months = 5;
        }
        else if ($subscription_length == '12'){
            $resubscription_months = 4;
        }
        else if ($subscription_length == '18'){
            $resubscription_months = 0;
        }
    }
    
    /*
    * Rules for Rayco (marketplace)
    * 6 Months resubscribes 6 months
    * 9 Months resubscribes 5 months 
    * 12 Months resubscribes 4 months 
    * 18 Months resubscribes to 4 months 
    * 24 Months resubscribes to 4 months 
    * 30 Months resubscribes to 4 months 
    **/
    else if ($mp_renter == 'rayco'){
        
        if ($subscription_length == '6'){
            $resubscription_months = 6;
        }
        else if ($subscription_length == '9'){
            $resubscription_months = 5;
        }
        else if ($subscription_length == '12'){
            $resubscription_months = 4;
        }
        else if ($subscription_length == '18'){
            $resubscription_months = 4;
        }
        else if ($subscription_length == '24'){
            $resubscription_months = 4;
        }
        else if ($subscription_length == '34'){
            $resubscription_months = 4;
        }
    }
    
    
    add_post_meta( $subscription->get_id(), 'aw_subscription_length', $subscription_length, true );

    add_post_meta( $subscription->get_id(), 'aw_resubscription_length', $resubscription_months, true );

    $workflow->log_action_note( $workflow , __( 'Renter is'.$mp_renter.' subscription length is: '.$subscription_length.' and resubscription length is: '.$resubscription_months, 'automatewoo' ) );
}

/**
 * Custom function to add subscription meta data with the number of periods in the resubscription
 * @param $workflow AutomateWoo\Workflow
 */
function update_next_end_date_for_rescubscribe( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();

    //Get Marketplace renter
    if(metadata_exists('post', $subscription_id, 'aw_mp_renter')){
        $mp_renter = get_metadata( 'post', $subscription_id, 'aw_mp_renter', true );
    }
    else {
        $mp_renter = 'cul';
    }
    
    //Get subscription length
    if(metadata_exists('post', $subscription_id, 'aw_subscription_length')){
        $subscription_length = get_metadata( 'post', $subscription_id, 'aw_subscription_length', true );
    }
    else {
        $subscription_length = 'aw_error';
    }

    // Get al rules for non marketplace and each marketplace player
    /*
    * Rules for CUL (non marketplace)
    * 6 Months resubscribes 6 months
    * 9 Months resubscribes 5 months 
    * 12 Months resubscribes 4 months 
    * 18 Months resubscribes to 0 months 
    **/
    if ($mp_renter == 'cul'){
        
        if ($subscription_length == '6'){
            $resubscription_months = 6;
        }
        else if ($subscription_length == '9'){
            $resubscription_months = 5;
        }
        else if ($subscription_length == '12'){
            $resubscription_months = 4;
        }
    }
    
    /*
    * Rules for Rayco (marketplace)
    * 6 Months resubscribes 6 months
    * 9 Months resubscribes 5 months 
    * 12 Months resubscribes 4 months 
    * 18 Months resubscribes to 4 months 
    * 24 Months resubscribes to 4 months 
    * 30 Months resubscribes to 4 months 
    **/
    else if ($mp_renter == 'rayco'){
        
        if ($subscription_length == '6'){
            $resubscription_months = 6;
        }
        else if ($subscription_length == '9'){
            $resubscription_months = 5;
        }
        else if ($subscription_length == '12'){
            $resubscription_months = 4;
        }
        else if ($subscription_length == '18'){
            $resubscription_months = 4;
        }
        else if ($subscription_length == '24'){
            $resubscription_months = 4;
        }
        else if ($subscription_length == '34'){
            $resubscription_months = 4;
        }
    }

    //Sets new post_meta with resubscription current time
    update_post_meta( $subscription_id, 'aw_subscription_length', $resubscription_months );
    add_post_meta( $subscription_id, 'aw_parent_subscription_length', $subscription_length, true );

    //Sets next payment time 1 month later minus 5 hours to account for server time and payment won't go to the next day if created after 19h
    $end_date = date("Y-m-d", strtotime("+$resubscription_months month -5 hour")).' 12:00:00';

    
    $subscription->update_dates(array('end' => $end_date));
    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'End date updated to: '.$end_date.' by '.$resubscription_months.' months', 'automatewoo' ) );
    //Leave note
    //$subscription->add_order_note('End date updated to: '.$next_month);
       
}

function aw_update_username_to_doc_id( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();

    //get user id from subscription
    $user_id = get_post_meta( $subscription_id, '_customer_user', true );
    
    //get document id from postmeta of order
    $document_id = get_post_meta( $subscription_id, '_billing_docid', true );

    if (isset($document_id)) {
        global $wpdb;
        $wpdb->update(
            $wpdb->users, 
            ['user_login' => $document_id], 
            ['ID' => $user_id]
        );
        
        //Automatewoo log
        $workflow->log_action_note( $workflow , __( 'Username changed to Document ID # '.$document_id. ' For user: '.$user_id.' On placed Subscription id: '.$subscription_id, 'automatewoo' ) );
    }

    else {
        //Automatewoo log
        $workflow->log_action_note( $workflow , __( 'No Document ID to change', 'automatewoo' ) );
    }
}

/**
 * Custom function to send data to Wati through the API
 * @param $workflow AutomateWoo\Workflow
 */
function aw_send_wati_data ( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();

    //get user id from subscription
    $user_id = get_post_meta( $subscription_id, '_customer_user', true );

    //get all user subscriptions
    $users_subscriptions = wcs_get_users_subscriptions($user_id);
    foreach ($users_subscriptions as $subscription){
      if ($subscription->has_status(array('active','on-hold','late-payment-30','late-payment-60','late-payment-90','late-payment-120','late-payment-150','late-payment-180','expired','expired-offer'))) {
             $all_subscriptions .= $subscription->get_id().","; 
      }
    }
    
    //get information from postmeta of subscription
    $document_id = get_post_meta( $subscription_id, '_billing_docid', true );
    $document_type = get_post_meta( $subscription_id, '_billing_typedoc', true );
    $first_name = get_post_meta( $subscription_id, '_billing_first_name', true );
    $last_name = get_post_meta( $subscription_id, '_billing_last_name', true );
    $email = get_post_meta( $subscription_id, '_billing_email', true );
    $phone = str_replace("+", "", get_post_meta( $subscription_id, '_billing_phone', true ));
    $city = get_post_meta( $subscription_id, '_billing_city', true );
    $mp_rent = get_post_meta( $subscription_id, 'aw_mp_renter', true );


    $url = "https://live-server-2438.wati.io/api/v1/updateContactAttributes/".$phone;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
       "Accept: application/json",
       "Content-Type: application/json",
       "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIyMmYwMzg0Mi01MzUyLTQwNzctOTlmNS0zMDczZTJkYjA2YTkiLCJ1bmlxdWVfbmFtZSI6ImhvbGFAdml2ZWN1bC5jb20iLCJuYW1laWQiOiJob2xhQHZpdmVjdWwuY29tIiwiZW1haWwiOiJob2xhQHZpdmVjdWwuY29tIiwiYXV0aF90aW1lIjoiMDgvMDYvMjAyMSAxNTowMDoyMyIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkFETUlOSVNUUkFUT1IiLCJleHAiOjI1MzQwMjMwMDgwMCwiaXNzIjoiQ2xhcmVfQUkiLCJhdWQiOiJDbGFyZV9BSSJ9.yYvYcpSaCODUaOnjmXKX2UZ18-Z5OOOUBZ-6bLW83ps",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = <<<DATA
    {
      "customParams": [
        {
          "name": "subscriptions",
          "value": "$all_subscriptions"
        },
        {
          "name": "document_id",
          "value": "$document_id"
        },
        {
          "name": "document_type",
          "value": "$document_type"
        },
        {
          "name": "email",
          "value": "$email"
        },
        {
          "name": "city",
          "value": "$city"
        },
        {
          "name": "mp_rent",
          "value": "$mp_rent"
        },
        {
          "name": "name",
          "value": "$first_name $last_name"
        }
      ]
    }
    DATA;

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //for debug only!
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    //var_dump($resp);
        
    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'Response: '.var_dump($resp).$phone.'subs: '.$all_subscriptions.'Data: '.$data, 'automatewoo' ) );
}

/**
 * Custom function to send pending payment template message throu Wati API
 * @param $workflow AutomateWoo\Workflow
 */
function aw_send_wati_pending_payment ( $workflow ) {
    //get subscription id from data layer
    $subscription = $workflow->data_layer()->get_subscription();
    $subscription_id = $subscription->get_id();

    //get user id from subscription
    $user_id = get_post_meta( $subscription_id, '_customer_user', true );

    
    
    //get information from postmeta of subscription
    
    $first_name = get_post_meta( $subscription_id, '_billing_first_name', true );
    $last_name = get_post_meta( $subscription_id, '_billing_last_name', true );
    $phone = str_replace("+", "", get_post_meta( $subscription_id, '_billing_phone', true ));


    $url = "https://live-server-2438.wati.io/api/v1/sendTemplateMessage/".$phone;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
       "Accept: application/json",
       "Content-Type: application/json",
       "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIyMmYwMzg0Mi01MzUyLTQwNzctOTlmNS0zMDczZTJkYjA2YTkiLCJ1bmlxdWVfbmFtZSI6ImhvbGFAdml2ZWN1bC5jb20iLCJuYW1laWQiOiJob2xhQHZpdmVjdWwuY29tIiwiZW1haWwiOiJob2xhQHZpdmVjdWwuY29tIiwiYXV0aF90aW1lIjoiMDgvMDYvMjAyMSAxNTowMDoyMyIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkFETUlOSVNUUkFUT1IiLCJleHAiOjI1MzQwMjMwMDgwMCwiaXNzIjoiQ2xhcmVfQUkiLCJhdWQiOiJDbGFyZV9BSSJ9.yYvYcpSaCODUaOnjmXKX2UZ18-Z5OOOUBZ-6bLW83ps",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = <<<DATA
    {
      "template_name": "pending_payment_w",
      "broadcast_name": "pending_payment_w",
      "parameters": "[{'name':'name', 'value':'$first_name'}, {'name':'subscription', 'value':'$subscription_id'}]"
    }
    DATA;

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //for debug only!
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    var_dump($resp);
        
    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'Response: '.var_dump($resp).'phone: '.$phone.'Data: '.$data, 'automatewoo' ) );
}

/**
 * Custom function to change subscription status to wc-expired-offer when a prypayment is completed.
 * @param $workflow AutomateWoo\Workflow
 */
function my_automatewoo_prepaid_finalize( $workflow ) {
    //get order id from data layer
    $order = $workflow->data_layer()->get_order();
    $order_id = $order->get_id();

    //get sub id from postmeta of offer
    $subscription_id = get_post_meta( $order_id, 'reantal_prepay_id', true );

    //change sub status with sub id
    $subscription_obj = wcs_get_subscription($subscription_id);
    $subscription_obj->update_status( 'expired' );
    $subscription_obj->update_status( 'expired-offer' );
    
    //Automatewoo log
    $workflow->log_action_note( $workflow , __( 'subscription finalized and moved to finalized with offer (expired-offer) with order id:'.$order_id.' and subscription id: '.$subscription_id, 'automatewoo' ) );
}
