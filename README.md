# [GiveWP](https://givewp.com "Give - Democratizing Generosity") #

![WordPress version](https://img.shields.io/wordpress/plugin/v/give.svg) ![WordPress Rating](https://img.shields.io/wordpress/plugin/r/give.svg) ![WordPress Downloads](https://img.shields.io/wordpress/plugin/dt/give.svg) [![Build Status](https://travis-ci.org/WordImpress/Give.svg?branch=master)](https://travis-ci.org/WordImpress/Give) [![Code Coverage](https://scrutinizer-ci.com/g/WordImpress/Give/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WordImpress/Give/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WordImpress/Give/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/WordImpress/Give/?branch=master) [![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://github.com/WordImpress/Give/blob/master/license.txt) 

Welcome to the GiveWP GitHub repository. This is the code source and the center of active development. Here you can browse the source, look at open issues, and contribute to the project. We recommend all developers follow the GiveWP development blog to stay up to date on the latest features and changes.
 
## Getting Started 

If you're looking to contribute or actively develop on Give then skip ahead to the [Local Development](https://github.com/WordImpress/Give/tree/issue/339#local-development) section below. The following is if you're looking to actively use the plugin on your WordPress site.

### Minimum Requirements

* WordPress 4.8 or greater
* PHP version 7.0 or greater
* MySQL version 5.6 or greater
* Some payment gateways require fsockopen support (for IPN access)
* cURL version 5.40 or higher
* An SSL certificate -- while this is not strictly required, it is highly recommend. If you are converting your site to use SSL/HTTPS now, [we have a detailed guide to help you here](http://docs.givewp.com/ssl).

### Automatic installation

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click "Add New".

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

### Manual installation

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).


### Support
This repository is not suitable for support. Please don't use GitHub issues for support requests. To get support please use the following channels:

* [WP.org Support Forums](https://wordpress.org/support/plugin/give) - for all users
* [GiveWP.com Priority Support](https://givewp.com/priority-support/) - exclusively for customers

## Local Development 

To get started developing on the Give platform you will need to perform the following steps:

1. Create a new WordPress site with `give.test` as the URL

2. `cd` into your local plugins directory: `/path/to/wp-content/plugins/`

3. Clone this repository from GitHub into your plugins directory: `https://github.com/WordImpress/Give.git`

4. Run composer to set up dependancies: `composer install`. After composer finishes installing all its dependencies, it will automatically fire `npm install` and get the necessary npm packages.

5. Activate the plugin in WordPress

That's it. You're now ready to start development.

### NPM Commands

Give relies on several npm commands to get you started:

* `npm run watch` - Live reloads JS and SASS files. Typically you'll run this command before you start development. It's necessary to build the JS/CSS however if you're working strictly within PHP it may not be necessary to run. 
* `npm run dev` - Runs a one time build for development. No production files are created.
* `npm run production` - Builds the minified production files for release.

### Development Notes

* Ensure that you have `SCRIPT_DEBUG` enabled within your wp-config.php file. Here's a good example of wp-config.php for debugging:
    ```
     // Enable WP_DEBUG mode
    define( 'WP_DEBUG', true );
    
    // Enable Debug logging to the /wp-content/debug.log file
    define( 'WP_DEBUG_LOG', true );
   
    // Loads unminified core files
    define( 'SCRIPT_DEBUG', true );
    ```
* Commit the `package.lock` file. Read more about why [here](https://docs.npmjs.com/files/package-lock.json). 
* Your editor should recognize the `.eslintrc` and `.editorconfig` files within the Repo's root directory. Please only submit PRs following those coding style rulesets. 
* Read [CONTRIBUTING.md](https://github.com/WordImpress/Give/blob/master/CONTRIBUTING.md) - it contains more about contributing to GiveWP.