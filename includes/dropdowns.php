<?php
/* =========================
   GLOBAL DROPDOWN LISTS (DB Driven)
   ========================= */
$GOTRA_LIST = [];
$NIVASI_LIST = [];

if (isset($conn)) {
    // Fetch Gotras
    $res = $conn->query("SELECT name FROM gotras ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $GOTRA_LIST[] = $row['name'];
        }
    }
    
    // Fetch Niwas
    $res2 = $conn->query("SELECT name FROM niwas ORDER BY name ASC");
    if ($res2) {
        while ($row = $res2->fetch_assoc()) {
            $NIVASI_LIST[] = $row['name'];
        }
    }
}
?>
