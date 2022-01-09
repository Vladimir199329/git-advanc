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

if ($mode == 'departments_views') {

        $params = $_REQUEST;
        if ($items_per_page = 3) {
            $params['items_per_page'] = $items_per_page;
        }

        foreach ($departments as $department => $key) {
            if($key['director_id']) {
                $departments[$department]['director_id'] = fn_get_user_short_info($departments[$department]['director_id']);
            } else {
                $departments[$department]['director_id'] = '';
            }
            $departments = $departments;
        }
        
        list($departments, $search) = fn_get_departments($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);
        
        Tygh::$app['view']->assign('departments', $departments);
        Tygh::$app['view']->assign('search', $search);
        Tygh::$app['view']->assign('columns', 3);

        fn_add_breadcrumb(__("departments"));

} elseif ($mode === 'department') {
    $params = $_REQUEST;

    if ($items_per_page = 5) {
        $params['items_per_page'] = $items_per_page;
    }

    $department_data = [];
    $department_id = !empty($_REQUEST['department_id']) ? $_REQUEST['department_id'] : 0;
    $department_data = fn_get_department_data($department_id , CART_LANGUAGE);

    if (empty($department_data)) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }
    
    if (!empty($department_data['workers_ids'])) {
        $params['user_id'] = $department_data['workers_ids'];
        list($users, $search) =fn_get_users($params, $auth, Registry::get('settings.Appearance.admin_elements_per_page'));
    }
    
    Tygh::$app['view']->assign('department_data', $department_data);
    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('workers', $users);

    fn_add_breadcrumb(__("departments"), [$department_data['department']]);   
}