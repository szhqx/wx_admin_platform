<?php
//先跑update_permission  再跑update_role
$dbhost = '127.0.0.1:3306';
$dbuser = 'root';
$dbpass = '#sCWeC^2';
$conn = mysql_connect($dbhost, $dbuser, $dbpass);

mysql_set_charset('utf8',$conn);

if(! $conn ) {
    die('Could not connect: ' . mysql_error());
}

mysql_select_db('wx_admin_platform');

$sql = 'delete from authority_role;';
mysql_query($sql, $conn );
echo "Done to clear data\n";

$sql = 'ALTER TABLE authority_role AUTO_INCREMENT = 1;';
mysql_query($sql, $conn );
echo "Done to reset index\n";

// prepare base role info
$raw_sql = <<<EOT

insert  into `authority_role`(`id`,`name`,`description`,`company_id`,`is_super_admin`,`role_type`, `role_level`, `permission_id_list`,`status`,`created_at`,`updated_at`) values

(1,'超级管理员','系统的管理员', 0, 0, 1, 1, '[]', 1, NULL, NULL),

(2,'管理员','业务管理员', 0, 0, 2, 1, '[%s]',1,NULL,NULL),

(3,'编辑部长','编辑业务', 0, 0, 3, 1, '[%s]',1,NULL,NULL),
(4,'编辑组长','编辑业务', 0, 0, 3, 2, '[%s]',1,NULL,NULL),
(5,'编辑人员','编辑业务', 0, 0, 3, 3, '[%s]',1,NULL,NULL),

(6,'财务部长','财务业务', 0, 0, 4, 1, '[%s]',1,NULL,NULL),
(7,'财务组长','财务业务', 0, 0, 4, 2, '[%s]',1,NULL,NULL),
(8,'财务人员','财务业务', 0, 0, 4, 3, '[%s]',1,NULL,NULL),

(9,'商务部长','商务业务', 0, 0, 5, 1, '[%s]',1,NULL,NULL),
(10,'商务组长','商务业务', 0, 0, 5, 2, '[%s]',1,NULL,NULL),
(11,'商务人员','商务业务', 0, 0, 5, 3, '[%s]',1,NULL,NULL);

EOT;

$editor_module_list = [
    'official-account/',
    'excel/',
    'official-group/',
    'message/',
    'reply/',
    'menu/',
    'mass/',
    'material/',
];
$finance_module_listn = array_merge(['finance/'], $editor_module_list);
$bussiness_module_listn = array_merge(['advertise/'], $editor_module_list);

$admin_permission_list = [];
$editor_permission_list = [];
$finance_permission_list = [];
$bussiness_permission_list = [];

// prepare authority_permission info
$sql = "select * from authority_permission;";
$result = mysql_query($sql, $conn);
$role_data = [];

while($row = mysql_fetch_array($result)) {

    $module_prefix = explode($row['name'], '/')[0] + '/';

    $admin_permission_list[] = $row['id'];

    if(in_array($module_prefix, $editor_module_list)) {
        $editor_permission_list[] = $row['id'];
    }

    if(in_array($module_prefix, $finance_module_listn)) {
        $finanace_permission_list[] = $row['id'];
    }

    if(in_array($module_prefix, $bussiness_module_listn)) {
        $bussiness_permission_list[] = $row['id'];
    }
}

$final_sql = sprintf($raw_sql,
                     implode(',', $admin_permission_list),

                     implode(',', $editor_permission_list),
                     implode(',', $editor_permission_list),
                     implode(',', $editor_permission_list),

                     implode(',', $finanace_permission_list),
                     implode(',', $finanace_permission_list),
                     implode(',', $finanace_permission_list),

                     implode(',', $bussiness_permission_list),
                     implode(',', $bussiness_permission_list),
                     implode(',', $bussiness_permission_list)
);

echo $final_sql;

// insert authority_role data
$retval = mysql_query( $final_sql, $conn );
if(! $retval ) {
    die('Could not enter data: ' . mysql_error());
}

echo "insert authority role data successfully\n";

mysql_close($conn);
?>