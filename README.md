<h1><p align="center">GiveWP - The #1 WordPress Fundraising Plugin 💚</p></h1>

<p align="center">This plugin is the highest rated, most downloaded, and best supported donation plugin for WordPress. Built from the ground up for all your fundraising needs, GiveWP provides you with a powerful donation platform optimized for online giving that's both easy-to-use for beginners yet flexible for developers to craft their own unique giving experiences.</p>

---

👉🏻 Not a developer? Running WordPress? [Download GiveWP](https://wordpress.org/plugins/give/) on WordPress.org.

![WordPress version](https://img.shields.io/wordpress/plugin/v/give.svg) ![WordPress Rating](https://img.shields.io/wordpress/plugin/r/give.svg) ![WordPress Downloads](https://img.shields.io/wordpress/plugin/dt/give.svg) [![Build Status](https://travis-ci.org/impress-org/givewp.svg?branch=master)](https://travis-ci.org/impress-org/givewp) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/impress-org/give/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/impress-org/give/?branch=master) [![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://github.com/impress-org/give/blob/master/license.txt) 

Welcome to the GiveWP GitHub repository. This is the core repository and heart of an ecosystem of active development. Here you can browse the source, look at open issues, and contribute to the project. 

Many of our add-ons are in public repositories, however, the majority are private. If you have a legitimate need for access, please [reach out to us](https://givewp.com/contact-us/) and we'll be happy to grant you access. As well, we recommend all developers follow the [GiveWP development blog](https://developers.givewp.com) to stay up to date on the latest features and changes. Happy coding!
 
 ## 🙋 Support
 
 This repository is not suitable for WordPress admin or donor support. Please don't use GitHub issues for non-development related support requests. Don't get us wrong, we're more than happy to help you! However, to get the support you need please use the following channels:

* [WP.org Support Forums](https://wordpress.org/support/plugin/give) - for all **free** users.
* [Priority Support](https://givewp.com/priority-support/) - exclusively for our **customers**. 
* [GiveWP Documentation](https://givewp.com/docs/) - for all **admins**. 
 
## 🌱 Getting Started 

If you're looking to contribute or actively develop on GiveWP, welcome! We're glad you're here. Please ⭐️ this repository and fork it to begin local development. 

Most of us are using [Local by Flywheel](https://localbyflywheel.com/) to develop on WordPress, which makes set up quick and easy. If you prefer [Docker](https://www.docker.com/), [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV), or another flavor of local development that's cool too!

## ✅ Prerequisites
* [Node.js](https://nodejs.org/en/) as JavaScript engine
* [NPM](https://docs.npmjs.com/) npm command globally available in CLI
* [Composer](https://getcomposer.org/) composer command globally available in CLI

## 💻 Local Development 

To get started developing on the GiveWP platform you will need to perform the following steps:

1. Create a new WordPress site with `give.test` as the URL
2. `cd` into your local plugins directory: `/path/to/wp-content/plugins/`
3. Fork this repository from GitHub and then clone that into your plugins directory in a new `give` directory
4. Run `composer install` to set up dependencies
5. Run `npm install` to get the necessary npm packages
6. Activate the plugin in WordPress
7. Run `npm run watch` to start the watch process which will build the sass and script files and live reload using [Browsersync](https://www.browsersync.io/)  

That's it. You're now ready to start development.

**Available commands**

| Command             | Description  |
| :------------- | :------------ |
| `npm run watch`      | Live reloads JS and SASS files. Typically you'll run this command before you start development. It's necessary to build the JS/CSS however if you're working strictly within PHP it may not be necessary to run.  |
| `npm run dev`      |    Runs a one time build for development. No production files are created. |
| `npm run production` |  Builds the minified production files for release. |
| `npm run test` |  Run jest tests. Make sure that your docker container is running before running this command. [Read more](https://github.com/impress-org/give/tree/master/tests/e2e) |

**Development Notes**

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
* Read [CONTRIBUTING.md](https://github.com/impress-org/give/blob/master/CONTRIBUTING.md) - it contains more about contributing to GiveWP.
