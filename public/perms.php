<?php
// public/perms.php
error_reporting(E_ALL);

$root = dirname(__DIR__); // dalla cartella public/ risali alla root del progetto

$paths = [
  $root . '/bootstrap/cache',
  $root . '/storage/framework',
  $root . '/storage/framework/cache',
  $root . '/storage/framework/cache/data',
  $root . '/storage/framework/sessions',
  $root . '/storage/framework/views',
];

foreach ($paths as $p) {
  echo $p, " | exists=", (is_dir($p)?'Y':'N'), " | writable=", (is_writable($p)?'Y':'N'), "<br>";
  @file_put_contents($p.'/.__perm_test', 'x');
  echo file_exists($p.'/.__perm_test') ? "wrote test file<br><br>" : "FAILED to write<br><br>";
}
