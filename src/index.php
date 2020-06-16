<?php
// Корневая директория сайта
define('root',$_SERVER['DOCUMENT_ROOT']);
/**
 * Путь к инструментам ядра
 * Должен быть относитель root
 */
define('instruments','instruments/');
/**
 * Путь к настройкам ядра
 * Должен быть относитель root
 */
define('configs','configs/');
/**
 * Путь к контенту ядра
 * У ядра должны быть права на запись в эту директорию
 * Должен быть относитель root
 */
define('content','content/');
// Импортируем ядро
include_once(instruments.'SeriousCompanyCore.php');
// Стартуем ядро
SeriousCompany_start();
