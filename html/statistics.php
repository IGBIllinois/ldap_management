<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

renderTwigTemplate('default/stats.html.twig', array(
    'siteArea' => 'Statistics',
    'totalUsers' => Statistics::users(),
    'passwordExpiredUsers' => Statistics::passwordExpiredUsers(),
    'expiringUsers' => Statistics::expiringUsers(),
    'expiredUsers' => Statistics::expiredUsers(),
    'lastMonthUsers' => Statistics::lastMonthUsers(),
    'neverLoggedInUsers' => Statistics::neverLoggedInUsers(),
    'leftCampusUsers' => Statistics::leftCampusUsers(),
    'nonCampusUsers' => Statistics::nonCampusUsers(),
    'classroomUsers' => Statistics::classroomUsers(),
    'totalGroups' => Statistics::groups(),
    'emptyGroups' => Statistics::emptyGroups(),
));
