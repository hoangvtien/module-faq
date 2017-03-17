<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 2-9-2010 14:43
 */

if (! defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

//List faq
$listcats = array();
$listcats[0] = array(
    'id' => 0, //
    'name' => $lang_module['nocat'], //
    'title' => $lang_module['nocat'], //
    'selected' => 0 == 0 ? " selected=\"selected\"" : "" //
);
$listcats = $listcats + nv_listcats(0);
if (empty($listcats)) {
    Header("Location: " . NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=cat&add=1");
    exit();
}

$page_title = $lang_module['faq_manager'];

$page = $nv_Request->get_int('page', 'get', 1);
$per_page = 1;

$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . NV_PREFIXLANG . "_" . $module_data . "_tmp";
$base_url = NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name. "&" . NV_OP_VARIABLE . "=acceptqa";
$base_url .= "&amp;page=" . $page;
if ($nv_Request->isset_request("catid", "get")) {
    $catid = $nv_Request->get_int('catid', 'get', 0);
    if (! $catid or ! isset($listcats[$catid])) {
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name. "&amp;" . NV_OP_VARIABLE . "=acceptqa");
        exit();
    }

    $caption = sprintf($lang_module['faq_list_by_cat'], $listcats[$catid]['title']);
    $sql .= " WHERE catid=" . $catid . " ORDER BY weight ASC";
    $base_url .= "&amp;catid=" . $catid;

    define('NV_IS_CAT', true);
} else {
    $caption = $lang_module['faq_accept_qa'];
    $sql .= " ORDER BY id DESC";
}
if(!empty($page)) {
	$sql .= " LIMIT "  . $per_page." OFFSET ".($page - 1) * $per_page;
}
else {
	$sql .= " LIMIT "  . $per_page;
}

$query = $db->query($sql);

$result = $db->query("SELECT FOUND_ROWS()");
$all_page = $result->fetchColumn();

if (! $all_page) {
    if (defined('NV_IS_CAT')) {
        $contents = "";
        include NV_ROOTDIR . '/includes/header.php';
        echo nv_admin_theme($contents);
        include NV_ROOTDIR . '/includes/footer.php';
        exit();
    } else {
        Header("Location: " . NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&add=1");
        exit();
    }
}

$array = array();

while ($row = $query->fetch()) {
	$user_info = $db->query('SELECT username, first_name, last_name, photo,email FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid = ' . $row['userid'])->fetch();
    $array[$row['id']] = array( //
        'id' => ( int )$row['id'], //
        'title' => $row['title'], //
        'cattitle' => $listcats[$row['catid']]['title'], //
        'catlink' => NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;catid=" . $row['catid'], //
        'username'=>$user_info['username'],
        'email'=>$user_info['email'],
        'addtime'=>date("h:i:s d/m/Y",$row['addtime'])
		);

}

$generate_page = nv_generate_page($base_url, $all_page, $per_page, $page);


$xtpl = new XTemplate("acceptqa.tpl", NV_ROOTDIR . "/themes/" . $global_config['module_theme'] . "/modules/" . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('TABLE_CAPTION', $caption);
$xtpl->assign('ADD_NEW_FAQ', NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;add=1");

if (defined('NV_IS_CAT')) {
    $xtpl->parse('main.is_cat1');
}

if (! empty($array)) {
    $a = 0;
    foreach ($array as $row) {
        $xtpl->assign('CLASS', $a % 2 == 1 ? " class=\"second\"" : "");
        $xtpl->assign('ROW', $row);
        $xtpl->assign('EDIT_URL', NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;edit=1&amp;id=" . $row['id']);
        $xtpl->parse('main.row');
        ++$a;
    }
}

if (! empty($generate_page)) {
    $xtpl->assign('GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';