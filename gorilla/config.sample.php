<?php
/*
 * Copia questo file come config.php (stessa cartella di api.php) e inserisci i dati
 * del database MySQL che trovi nel pannello Aruba, sezione Database MySQL.
 *
 * Esempio tipico su Aruba:
 *   host:     31.11.xx.xx  oppure  xxxxxx.mysql.aruba.it
 *   database: Sql1234567_1
 *   utente:   Sql1234567
 *
 * config.php NON va messo nel repository: contiene la password.
 */

$DB_DSN  = 'mysql:host=HOST_MYSQL;dbname=NOME_DATABASE;charset=utf8mb4';
$DB_USER = 'UTENTE_MYSQL';
$DB_PASS = 'PASSWORD_MYSQL';

// Per provare in locale senza MySQL basta SQLite, un file nella stessa cartella:
// $DB_DSN  = 'sqlite:' . __DIR__ . '/gorillas.sqlite';
// $DB_USER = null;
// $DB_PASS = null;
