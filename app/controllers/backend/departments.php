<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/


use Tygh\Tygh;
use Tygh\Registry;
use Tygh\Languages\Languages;

defined('BOOTSTRAP') or die('Access denied');

$suffix = '';

fn_trusted_vars(
    'department_data'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($mode == 'update_department') {
        $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
        $data = !empty($_REQUEST['department_data']) ? $_REQUEST['department_data'] : 0;
        $department_id = fn_update_department($data, $department_id);

        if (!empty($department_id)) {
            return array(CONTROLLER_STATUS_OK, "departments.update_department?department_id={$department_id}");
        } else {
            return array(CONTROLLER_STATUS_REDIRECT, "departments.add_department");
        }

    } elseif ($mode === 'delete_department') {
        $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
        fn_delete_department($department_id);
        return array(CONTROLLER_STATUS_REDIRECT, "departments.manage_departments");
        

    } elseif ($mode == 'delete_departments') {
        if (!empty($_REQUEST['department_ids'])) {
            foreach ($_REQUEST['department_ids'] as $department_id) {
                fn_delete_department($department_id);
            }
        }
        return array(CONTROLLER_STATUS_REDIRECT, "departments.manage_departments");
    } elseif (
        $mode === 'm_update_statuses'
        && !empty($_REQUEST['department_ids'])
        && is_array($_REQUEST['department_ids'])
        && !empty($_REQUEST['status'])
    ) {
        $status_to = (string) $_REQUEST['status'];

        foreach ($_REQUEST['department_ids'] as $department_id) {
            if (!fn_check_company_id('departments', 'department_id', $bdepartment_id)) {
                continue;
            }
            fn_tools_update_status([
                'table'             => 'departments',
                'status'            => $status_to,
                'id_name'           => 'department_id',
                'id'                => $department_id,
                'show_error_notice' => false
            ]);
        }

        if (defined('AJAX_REQUEST')) {
            $redirect_url = fn_url('departments.manage_departments');
            if (isset($_REQUEST['redirect_url'])) {
                $redirect_url = $_REQUEST['redirect_url'];
            }
            Tygh::$app['ajax']->assign('force_redirection', $redirect_url);
            Tygh::$app['ajax']->assign('non_ajax_notifications', true);
            return [CONTROLLER_STATUS_NO_CONTENT];
        }
    }

} elseif ($mode == 'add_department' || $mode == 'update_department') {
    
    $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
    $department_data = fn_get_department_data($department_id , DESCR_SL);

    if (empty($department_data) && $mode == 'update') {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    Tygh::$app['view']->assign([
        'department_data' => $department_data,
        'd_info' => !empty($department_data['director_id']) ? fn_get_user_short_info($department_data['director_id']) : [],
        'w_info' => !empty($department_data['workers_id']) ? fn_get_user_short_info($department_data['workers_id']) : []
    ]);

} elseif ($mode === 'manage_departments') {
    
    list($departments, $search) = fn_get_departments($_REQUEST, 4, DESCR_SL);

    $page = $search['page'];
    $valid_page = db_get_valid_page($page, $search['items_per_page'], $search['total_items']);

    Tygh::$app['view']->assign('departments', $departments);
    Tygh::$app['view']->assign('search', $search);

}