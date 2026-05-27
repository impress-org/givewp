<h1><p align="center">GiveWP - The #1 WordPress Fundraising Plugin 💚</p></h1>

<p align="center">This plugin is the highest rated, most downloaded, and best supported donation plugin for WordPress. Built from the ground up for all your fundraising needs, GiveWP provides you with a powerful donation platform optimized for online giving that's both easy-to-use for beginners yet flexible for developers to craft their own unique giving experiences.</p>

---

👉 Not a developer? Running WordPress? [Download GiveWP](https://wordpress.org/plugins/give/) on WordPress.org.

![WordPress version](https://img.shields.io/wordpress/plugin/v/give.svg) ![WordPress Rating](https://img.shields.io/wordpress/plugin/r/give.svg) ![WordPress Downloads](https://img.shields.io/wordpress/plugin/dt/give.svg) [![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://github.com/impress-org/give/blob/master/license.txt) ![Wordpress Tests](https://github.com/impress-org/givewp/workflows/WordPress%20Tests/badge.svg?branch=develop)

Welcome to the GiveWP GitHub repository. This is the core repository and heart of an ecosystem of active development. Here you can browse the source, look at open issues, and contribute to the project.

Many of our add-ons are in public repositories, however, the majority are private. If you have a legitimate need for access, please [reach out to us](https://givewp.com/contact-us/) and we'll be happy to grant you access.

 ## 🙋 Support

 This repository is not suitable for WordPress admin or donor support. Please don't use GitHub issues for non-development related support requests. Don't get us wrong, we're more than happy to help you! However, to get the support you need please use the following channels:

* [WP.org Support Forums](https://wordpress.org/support/plugin/give) - for all **free** users.
* [Priority Support](https://givewp.com/priority-support/) - exclusively for our **customers**.
* [GiveWP Documentation](https://givewp.com/documentation/) - for all **admins**.

## 🌱 Getting Started

If you're looking to contribute or actively develop on GiveWP, welcome! We're glad you're here. Please ⭐️ this repository and fork it to begin local development.

## ✅ Prerequisites
* [Node.js](https://nodejs.org/en/) (v20+) as JavaScript engine
* [NPM](https://docs.npmjs.com/) npm command globally available in CLI
* [Composer](https://getcomposer.org/) composer command globally available in CLI
* [Docker](https://www.docker.com/) running locally (for the Docker-based quick start)

## 🐳 Quick Start (Docker)

The fastest way to get up and running is with [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/), which spins up a local WordPress environment using Docker.

1. Fork this repository from GitHub and clone it anywhere on your machine
2. Make sure Docker is running
3. From the plugin root, run:

```bash
composer install && npm install && npm run build && npm run env:start
```

That's it. WordPress will be available at the URL shown in the terminal output.

## 💻 Local Development (existing WordPress install)

To develop against an existing local WordPress install:

1. `cd` into your local plugins directory: `/path/to/wp-content/plugins/`
2. Fork this repository from GitHub and clone it into a new `give` directory
3. Run `composer install` to set up dependencies
4. Run `npm install` to get the necessary npm packages
5. Run `npm run dev` to build the initial scripts & styles
6. Activate the plugin in WordPress

**Available commands**

Note: We use [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/). The commands are as follows:

| Command         | Description                                                             |
|:----------------|:------------------------------------------------------------------------|
| `npm run dev`   | Runs a one time build for development. No production files are created. |
| `npm run watch` | Automatically re-builds as changes are made.                            |
| `npm run build` | Builds the minified production files for release.                       |

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
