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

function fn_get_department_data($department_id = 0, $lang_code = CART_LANGUAGE) 
{
    $department = [];
    if (!empty($department_id)){
        list($departments) = fn_get_departments([
            'department_id' => $department_id
        ], 1, $lang_code);

        if (!empty($departments)) {
            $department = reset($departments);
            $department['workers_ids'] = fn_department_get_links($department['department_id']);
        }
    }
    return $department;
}

function fn_get_departments($params = [], $items_per_page = 0, $lang_code = CART_LANGUAGE) 
{
   // Set default values to input params
   $default_params = array(
    'page' => 1,
    'items_per_page' => $items_per_page
);

$params = array_merge($default_params, $params);

if (AREA == 'C') {
    $params['status'] = 'A';
}

$sortings = array(
    'timestamp' => '?:departments.timestamp',
    'name' => '?:departments_descriptions.department',
    'status' => '?:departments.status',
);

$condition = $limit = $join = '';

if (!empty($params['limit'])) {
    $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
}

$sorting = db_sort($params, $sortings, 'name', 'asc');

if (!empty($params['item_ids'])) {
    $condition .= db_quote(' AND ?:departments.department_id IN (?n)', explode(',', $params['item_ids']));
}

if (!empty($params['name'])) {
    $condition .= db_quote(' AND ?:departments_descriptions.department LIKE ?l', '%' . trim($params['name']) . '%');
}

if (!empty($params['department_id'])) {
    $condition .= db_quote(' AND ?:departments.department_id = ?i', $params['department_id']);
}

if (!empty($params['director_id'])) {
    $condition .= db_quote(' AND ?:departments.director_id = ?i', $params['director_id']);
}

if (!empty($params['status'])) {
    $condition .= db_quote(' AND ?:departments.status = ?s', $params['status']);
}

$fields = array (
    '?:departments.department_id',
    '?:departments.status',
    '?:departments.director_id',
    '?:departments.timestamp',
    '?:departments_descriptions.department',
    '?:departments_descriptions.description',
);

$join .= db_quote(' LEFT JOIN ?:departments_descriptions ON ?:departments_descriptions.department_id = ?:departments.department_id AND ?:departments_descriptions.lang_code = ?s', $lang_code);

if (!empty($params['items_per_page'])) {
    $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:departments $join WHERE 1 $condition");
    $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
}

$departments = db_get_hash_array(
    "SELECT ?p FROM ?:departments " .
    $join .
    "WHERE 1 ?p ?p ?p",
    'department_id', implode(', ', $fields), $condition, $sorting, $limit
);

$department_image_ids = array_keys($departments);
$images = fn_get_image_pairs($department_image_ids, 'department', 'M', true, false, $lang_code);

foreach ($departments as $department_id => $department) {
    $departments[$department_id]['main_pair'] = !empty($images[$department_id]) ? reset($images[$department_id]) : array();
}

return array($departments, $params);
}

function fn_update_department($data, $department_id, $lang_code = DESCR_SL)
{

    if (isset($data['timestamp'])) {
        $data['timestamp'] = fn_parse_date($data['timestamp']);
    }

    if (!empty($department_id)) {
        db_query("UPDATE ?:departments SET ?u WHERE department_id = ?i", $data, $department_id);
        db_query("UPDATE ?:departments_descriptions SET ?u WHERE department_id = ?i AND lang_code = ?s", $data, $department_id, $lang_code);

    } else {
        $department_id = $data['department_id'] = db_replace_into('departments', $data);

        foreach (Languages::getAll() as $data['lang_code'] => $v) {
            db_query("REPLACE INTO ?:departments_descriptions ?e", $data);
        }

    } if (!empty($department_id)) {
        fn_attach_image_pairs('department_main', 'department', $department_id, $lang_code);
    } 

    $workers_ids = !empty($data['workers_ids']) ? $data['workers_ids'] : [];
    fn_department_delete_links($department_id);
    fn_department_add_links($department_id, $workers_ids);

    return $department_id;
} 
function fn_delete_department($department_id)
{

    if (!empty($department_id)) {
        $res = db_query("DELETE FROM ?:departments WHERE department_id = ?i", $department_id);
        db_query("DELETE FROM ?:departments_descriptions WHERE department_id = ?i", $department_id);
        fn_delete_image_pairs($department_id, 'department');
        fn_department_delete_links($department_id);
    }
}
function fn_department_delete_links($department_id)
{
    db_query("DELETE FROM ?:department_links WHERE department_id = ?i", $department_id);
}
function fn_department_add_links($department_id, $workers_ids)
{
    if (!empty($workers_ids)) {
        $workers_ids = explode(',',$workers_ids);
        foreach ($workers_ids as $workers_id) {
            db_query("REPLACE INTO ?:department_links ?e", [
                'workers_id' => $workers_id,
                'department_id' => $department_id,
            ]);
        }
    }
}
function fn_department_get_links($department_id)
{
    return !empty($department_id) ? db_get_fields('SELECT workers_id FROM ?:department_links WHERE department_id = ?i', $department_id) : [];
}
