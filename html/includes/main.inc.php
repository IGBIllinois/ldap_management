<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

ini_set('display_errors', 1);
require_once('../conf/settings.inc.php');
require_once '../vendor/autoload.php';

// TODO come up with a better location for this function
/**
 * @param string          $key
 * @param LdapObject|null $class
 * @return mixed
 */
function requireGetKey($key, $class = null)
{
    if ((!isset($_GET[$key]) || $_GET[$key] == "") || ($class !== null && !$class::exists($_GET[$key]))) {
        header('location: index.php');
        exit();
    }
    return $_GET[$key];
}

// TODO and this one
/**
 * @param string $template
 * @param array  $context
 */
function renderTwigTemplate($template, $context)
{
    global $twig;
    try {
        $template = $twig->load($template);
        echo $template->render($context);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
        echo $e->getMessage();
    }
}

// Initialize Twig
$loader = new FilesystemLoader('../templates');
$twig = new Environment($loader, []);
$twig->addGlobal('title', __TITLE__);

Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);
