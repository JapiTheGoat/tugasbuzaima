<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/controllers/HomeController.php';

$controller = new HomeController();

$action = $_GET['action'] ?? 'index';
$controller->handleRequest($action);