<?php
/**
 * Для использования вставить в index.php
 */
$store = Boolive\data\Data::getStore('');
$u = $store->db->prepare('UPDATE objects SET parent_cnt = ? WHERE id = ?');
$u_count = array();
$q = $store->db->query('SELECT * FROM ids');
$q->execute();
while ($row = $q->fetch(\Boolive\database\DB::FETCH_ASSOC)){
    $u->execute(array(mb_substr_count($row['uri'],'/'), $row['id']));
    if ($u->rowCount()) $u_count[] = $row['uri'];
}
trace($u_count);