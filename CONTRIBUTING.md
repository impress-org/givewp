# Contributing to GiveWP

Contributions to GiveWP are more than welcome.

## License

By contributing code to GiveWP, you agree to license your contribution under the [GPL License](license.txt).

## Before reporting a bug

Search our [issue tracker](https://github.com/impress-org/give/issues) for similar issues.

* __Do not report potential security vulnerabilities here. Email them privately to our security team at [security@givewp.com](mailto:security@givewp.com)__

## How to report a bug

1. Specify the version number for GiveWP. 
2. Describe the problem in detail. Explain what happened, and what you expected would happen.
3. If helpful, include a screenshot. Annotate the screenshot for clarity.

## How to contribute to GiveWP

If you would like to submit a pull request, please follow the steps below:

* Make sure you have a GitHub account
* Fork the repository on GitHub
* Make changes to your fork of the repository
* Ensure you stick to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
 *  When committing, reference your issue (if present) and include a note about the fix
* Push the changes to your fork and [submit a pull request](https://help.github.com/articles/creating-a-pull-request) to the 'master' branch of the GiveWP repository

## Code Documentation

* We ensure that every GiveWP function is documented well and follows the standards set by phpDoc
* If you're adding/editing a function in a class, make sure to add `@access {private|public|protected}`
* Finally, please use tabs and not spaces. The tab indent size should be 4 for all GiveWP code.

At this point, you're waiting for us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.

## Javascript Coding Standards

We ensure that each change in JS files follows [Javascript Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/javascript/) provided by WordPress.

#### Step 1: Install Node Modules as defined in `package.json`

```
$ npm install
```

#### Step 2: How to use ESLint?

##### with Terminal 

1. Open Terminal
2. Type command in a format `$ eslint [options] file.js [file.js] [dir]`.
3. You'll get a list of suggestions.

For Example,
```
$ eslint yourfile.js
```

##### with PHPStorm

Follow the steps below to configure ESLint with PHPStorm:
1. Go to PHPStorm `Preferences > Languages and Frameworks > Javascript > Code Quality Tools > ESLint`.
2. Now, Tick the `Enable` checkbox.
3. Set path for Node Interpreter and ESLint package, if its not set.


Open any JS file and you'll see suggestions from ESLint, if any.

That's all! You're done.

## How to test code before creating PR
You can create an awesome PR if:
1. `/wp-content/debug.log` is empty after testing code functionality.
2. Does not contain and testing code like `var_dump()` and `console.log()`.
3. Passes all phpunit tests.
4. No formatting issue when check formatting via `php_codesniffer`/`eslint` with `WordPress Coding Standard`.
5. Works fine on at least on PHP `5.3` and PHP `7.*`
6. Plugin loads fine with your code on PHP `5.2`
