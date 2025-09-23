<?php
// Cookie consent functionality
function setCookieConsent($consent) {
    setcookie('wof_cookie_consent', $consent, time() + (365 * 24 * 60 * 60), '/', '', false, true);
}

function hasCookieConsent() {
    return isset($_COOKIE['wof_cookie_consent']) && $_COOKIE['wof_cookie_consent'] === 'accepted';
}

function showCookieBanner() {
    return !isset($_COOKIE['wof_cookie_consent']);
}

// Handle cookie consent
if(isset($_POST['cookie_action'])) {
    if($_POST['cookie_action'] === 'accept') {
        setCookieConsent('accepted');
        // Set analytics and functional cookies
        setcookie('wof_analytics', 'enabled', time() + (365 * 24 * 60 * 60), '/', '', false, true);
        setcookie('wof_preferences', 'enabled', time() + (365 * 24 * 60 * 60), '/', '', false, true);
    } elseif($_POST['cookie_action'] === 'reject') {
        setCookieConsent('rejected');
        // Only essential cookies
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}
?>