# GiveWP - Addon Boilerplate
A demo plugin to serve as a boilerplate for developers to understand how to extend the GiveWP Donation
plugin for WordPress.

## Setup & Installation
1. Clone this repository to your local
2. Remove the `.git` directory
3. Run `php build.php` from the CLI
4. Run `composer install` from the CLI
5. Run `npm install` from the CLI
6. Update this README (see below for a starting point)

### Asset Compilation
To compile your CSS & JS assets, run one of the following:
- `npm run dev` — Compiles all assets for development one time
- `npm run watch` — Compiles all assets for development one time and then watches for changes, supporting [BrowserSync](https://laravel-mix.com/docs/5.0/browsersync)
- `npm run hot` — Compiles all assets for development one time and then watches for [hot replacement](https://laravel-mix.com/docs/5.0/hot-module-replacement)
- `npm run dev` — Compiles all assets for production one time

## Concepts

GiveWP follows a domain-driven model both in core and in add-ons. Each business feature defines
its own domain, including whatever it needs (settings, models, etc.) to do what it does. It's also
important these domains are portable, that is, they are not bound to the plugin and could move to or
from another plugin as needed.

For these reasons, each add-on has two primary directories for handling its logic:
- src/Addon
- src/Domain

### src directory

The src directory handles business domain logic (i.e. a specific feature). The src
directory should have no files in the root, but be a collection of folders. Each folder represents
a distinct domain. Even if there is only one domain for the add-on, it should still live inside a
domain directory.

### src/Domain directory

It is possible for an add-on to have multiple domains, but it will always have at least one. Feel
free to duplicate this directory and make more. This directory is just the starting point for the
initial domain.

### src/Addon directory

This unique domain directory is responsible for the fact that the add-on is a WordPress plugin.
Plugins do things such as activate, upgrade, and uninstall — the logic of which should be handled
there. All GiveWP add-ons also check for compatibility with GiveWP core, and this also is handled
here.

The `src/Addon` directory may reference code in the `src` directory, but not the other way around.
No domain code should reference (and therefore depend on) the `src/Addon` directory. Doing this
keeps the dependency unidirectional.

#### Note for developers
If running `npm run dev` throws an error then check whether the `images` folder exists in your addon directory under `src/Addon/resources`. 
1. If the `images` folder does not exist then create one. 
2. If the `images` folder isn't required then remove the code from `webpack.config.js`.

---

DELETE ABOVE THIS LINE WHEN REWRITING README

---

## Introduction

[Write an introduction to what this addon is for]

## Development

### Getting Set Up
1. Clone this repository locally
2. Run `composer install` from the CLI
3. Run `npm install` from the CLI

### Asset Compilation
To compile your CSS & JS assets, run one of the following:
- `npm run dev` — Compiles all assets for development one time
- `npm run watch` — Compiles all assets for development one time and then watches for changes, supporting [BrowserSync](https://laravel-mix.com/docs/5.0/browsersync)
- `npm run hot` — Compiles all assets for development one time and then watches for [hot replacement](https://laravel-mix.com/docs/5.0/hot-module-replacement)
- `npm run dev` — Compiles all assets for production one time
