<?php
try { 
  $db = new PDO('sqlite:../database/database.sqlite'); 
  $res = $db->query("SELECT sql FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_ASSOC); 
  foreach($res as $r) { 
    echo $r['sql'] . "\n\n"; 
  } 
} catch (Exception $e) { 
  echo $e->getMessage(); 
}
