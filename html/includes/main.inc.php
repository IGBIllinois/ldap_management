<?php

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

ini_set('display_errors', 1);
set_include_path(get_include_path() . ":../libs");
require_once('../conf/settings.inc.php');
function my_autoloader($class_name) {
    if ( file_exists("../libs/" . $class_name . ".class.inc.php") ) {
        require_once $class_name . '.class.inc.php';
    }
}

spl_autoload_register('my_autoloader');

// TODO come up with a better location for this function
/**
 * @param string          $key
 * @param LdapObject|null $class
 * @return mixed
 */
function requireGetKey($key, $class = null) {
    if ( (!isset($_GET[$key]) || $_GET[$key] == "") || ($class !== null && !$class::exists($_GET[$key])) ) {
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
function renderTwigTemplate($template, $context) {
    global $twig;
    try {
        $template = $twig->load($template);
        echo $template->render($context);
    } catch (LoaderError $e) {
        echo $e->getMessage();
    } catch (RuntimeError $e) {
        echo $e->getMessage();
    } catch (SyntaxError $e) {
        echo $e->getMessage();
    }
}

// Initialize Twig
require_once '../vendor/autoload.php';
$loader = new Twig_Loader_Filesystem('../templates');
$twig = new Twig_Environment($loader, array());
$twig->addGlobal('title', __TITLE__);

Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);
?>