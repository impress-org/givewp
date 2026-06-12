# Contributing to GiveWP

Contributions to GiveWP are more than welcome.

## License

By contributing code to GiveWP, you agree to license your contribution under the [GPL License](license.txt).

## Reporting bugs

Search our [issue tracker](https://github.com/impress-org/givewp/issues) first to see if the bug has already been reported. When opening a new issue:

1. Specify the version number for GiveWP.
2. Describe the problem in detail. Explain what happened, and what you expected would happen. Include a screenshot if helpful.

__Do not report potential security vulnerabilities here.__ You can report security bugs through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/give). The Patchstack team helps validate, triage, and handle any security vulnerabilities.

## Development setup

See the [README](README.md) for prerequisites, the Docker-based quick start, local development setup, and build commands.

## Coding standards

* **PHP** follows the [PSR-12 coding standard](https://www.php-fig.org/psr/psr-12/) rather than the WordPress coding standards.
* **JavaScript and styles** follow the `.eslintrc` and `.editorconfig` rulesets in the repository root — your editor should pick these up automatically.
* **Docblocks** use `@since` tags. For new or changed code, use `@since TBD` — it's replaced with the version number at release time.

```php
/**
 * @since TBD
 */
public function myNewMethod()
```

## Running tests

From the plugin root:

```bash
composer test
```

See [tests/README.md](tests/README.md) for environment setup and how to filter to specific tests. Tests also run automatically via GitHub Actions on every pull request.

## Submitting a pull request

1. Fork the repository on GitHub and make your changes on a branch.
2. Before opening the PR, check that:
   * All PHPUnit tests pass (`composer test`).
   * Your code follows PSR-12 and the `.eslintrc`/`.editorconfig` rulesets.
   * New or changed code has `@since TBD` docblock tags.
   * You've added a changelog entry with `composer run changelog:add` — this drops a YAML file into `./changelog` that gets compiled into `readme.txt` at release time. Commit it with your PR.
   * There's no leftover debug code (`var_dump()`, `console.log()`) and `/wp-content/debug.log` stays empty while testing your changes.
3. [Submit the pull request](https://help.github.com/articles/creating-a-pull-request) to the `develop` branch, referencing the related issue if there is one.
4. Prefix the PR title with the type of change, e.g. `Feature: add donation form export`, `Fix: prevent duplicate receipts`, or `Tweak: improve settings copy`.

We review all pull requests and will make suggestions and changes if necessary.

## Security considerations

* When integrating with payment gateways, make sure all data relevant to the gateway goes directly to the gateway and nowhere else — especially credit card data.
* Under no circumstances should payment method details (i.e. credit card details) be stored on the server.
