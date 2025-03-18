<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('neo_copykey_alert_f12');
delete_option('neo_copykey_alert_i');
delete_option('neo_copykey_alert_j');
delete_option('neo_copykey_alert_u');
delete_option('neo_copykey_alert_r');
delete_option('neo_copykey_alert_s');
delete_option('neo_copykey_alert_p');
delete_option('neo_copykey_alert_d');
delete_option('neo_copykey_redirect_url');
delete_option('neo_copykey_alert_message');
