<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Admin');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
//$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$route['api/login'] = 'Admin::login';
$route['api/add_temperature'] = 'Admin::addTemperature';
$route['update_consigne'] = 'Admin::updateCons';

$route['logout'] = 'Admin::logout';

$route['import_users'] = 'Admin::importUsers';
$route['users'] = 'Admin::showUsers';
$route['add_user'] = 'Admin::addUser';
$route['delete_user/(:num)'] = 'Admin::deleteUser';
$route['edit_user/(:num)'] = 'Admin::editUser';
$route['update_password/(:num)'] = 'Admin::passwordUpdate';
$route['profile'] = 'Admin::UserProfile';

$route['apartments'] = 'Admin::Apartments';
$route['/apartments/(:num)'] = 'Admin::apartmentDetails';
$route['add_apartment'] = 'Admin::addApartment';
$route['apartments/(:num)/rooms'] = 'Admin::roomDetails';
$route['delete_appt/(:num)'] = 'Admin::deleteAppt';
$route['edit_apartment/(:num)'] = 'Admin::editAppt';
$route['merge_apartment/(:num)'] = 'Admin::MergeAppt';

$route['password_forget'] = 'Admin::showPasswordForget';
$route['password_recovery'] = 'Admin::PasswordRecovery';
$route['confirm_pwd_recovery'] = 'Admin::ConfirmPasswordRecovery';
$route['reset_password'] = 'Admin::ResetPassword';
$route['user_edit_password'] = 'Admin::UserEditPassword';
$route['user_edit_infos'] = 'Admin::UserEditInfos';

$route['macs'] = 'Admin::ShowMacs';
$route['add_mac'] = 'Admin::AddMac';
$route['delete_mac'] = 'Admin::deleteMac';
$route['undelete_mac'] = 'Admin::undeleteMac';
$route['edit_mac'] = 'Admin::editMac';

$routes->get('/', 'Admin::index');
$routes->map($route);
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
