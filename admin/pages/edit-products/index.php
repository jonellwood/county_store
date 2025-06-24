<?php
// Created: 2025/04/09 11:34:08
// Last modified: 2025/04/10 10:57:55

// There is a VS Code extension called 'Auto Time Stamp' that will automatically add the created and last modified comments for you. If you don't want this in the file you can remove it from /tools/indexTemplate.php   
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| These files were created when the project was initiated. They need to included
|--------------------------------------------------------------------------
|
*/
// require_once '../../rootConfig.php';
require_once '../../../rootConfig.php';
include_once APP_ROOT . '/classes/Layout.php';
include_once APP_ROOT . '/classes/Logger.php';
include_once APP_ROOT . '/classes/Product.php';
$loggedIn = Layout::confirmLoggedIn();
if (!$loggedIn) {
    // header("Location: ../signin/signin.php");
    header("Location: /admin/signin/signin.php");
}

// this value will be created in the make method... I will work on placing it dynamically after creation
// $pageId = '4b6c96a1-4d33-4723-9bd1-738d41f781b9';
// $accessRequired = Page::getAccessRequired($pageId);
// AccessControl::enforce($accessRequired);

$product = new Product();

$products = $product->getProducts();
/*
|--------------------------------------------------------------------------
| Initialize page assets
|--------------------------------------------------------------------------
| These are the default js and css file generated with this page. Ideally
| there is no need to change these settings, but feel free to do so to work
| with your coding styles. Around line 61 there is a method to add other js
| files. 
|
| If you have additional CSS files to load - specific to the page - you can 
| load them here. Site wide CSS should be in the default template file.
| `/templates/layouts/default.php`
|
| TODO I know the formatting for the path is jacked up with extra ' in there. I will get to it at some point.
*/
$assets = new Assets();
$assets->addCss("editproducts.css")
    ->addJs("editproducts.js", true);


/*
|--------------------------------------------------------------------------
| Create the Layout Instance
|--------------------------------------------------------------------------
|
*/
$layout = new Layout();

/*
|--------------------------------------------------------------------------
| Set page variables
|--------------------------------------------------------------------------
|
| The default `'pageTitle' => 'birthdays'` will default the page name to the 
| value passed in during page creation. Feel free to update to a string
| that better reflects the name of the page if desired
|
*/
$layout->setVars([
    'pageTitle' => 'editproducts',
    'language' => 'en',
    'bodyClass' => 'editproducts-page',
    'assets' => $assets
]);

/*
|--------------------------------------------------------------------------
| Set sidebar value
|--------------------------------------------------------------------------
|
| The default sidebar will be loaded in. In the event you create a custom
| sidebar, preferably in the `/components` folder (but thats up to you), you
| can replace the component name in the function call to load that asset.
|
*/
// $layout->setSection('sidebar', APP_ROOT . '/components/sidenav.php');
// $layout->setSection('sidebar', APP_ROOT . '../sideNav.php');
// $layout->setSection('sidebar', APP_ROOT . '../hideTopNav.php');

/*
|--------------------------------------------------------------------------
| Add page specific custom javascript
|--------------------------------------------------------------------------
|
| `$layout->setSectionContent('scripts', '<script src="https://example.com/fake.js"></script>')`
| will load external js scripts. You can also load in local js scripts, from a 
| /functions or /utilities folder for example. 
|
*/
// $layout->setSectionContent('scripts', '<script src="/js/super-slick.js"></script>');

/*
|--------------------------------------------------------------------------
| Render the page with specific variables for the content area
|--------------------------------------------------------------------------
|
| You can either call renderPage and just pass in the 'birthdays' value or 
| props
| The onboarding.php file is where you will add the html for the page. 
| You can add the props to the page as needed (example: second renderPage method call).
|
*/
// uncomment this line to render the page with no props
// $layout->renderPage("editproducts.php");

$layout->renderPage("editproducts.php", [
    'products' => $products,
]);
