# Contributing to the BigCommerce PHP API Client

Thanks for showing interest in contributing!

The following is a set of guidelines for contributing to the BigCommerce PHP API client. These are just guidelines, not rules. Use your best judgment, and feel free to propose changes to this document in a pull request.

By contributing to the BigCommerce PHP API client, you agree that your contributions will be licensed under its MIT license.

#### Table of Contents

[API Documentation](https://developer.bigcommerce.com/api)

[How Can I Contribute?](#how-can-i-contribute)
  * [Your First Code Contribution](#your-first-code-contribution)
  * [Pull Requests](#pull-requests)
  * [Tests](#tests)

[Styleguides](#styleguides)
  * [Git Commit Messages](#git-commit-messages)

### Your First Code Contribution

Unsure where to begin contributing to the API client? Check our [forums](https://forum.bigcommerce.com/s/group/0F913000000HLjECAW), our [stackoverflow](https://stackoverflow.com/questions/tagged/bigcommerce) tag, and the reported [issues](https://github.com/bigcommerce/bigcommerce-api-php/issues).

### Tests
You can run tests using the following command: `./vendor/bin/phpunit`

### Code quality - PhpStan
To check your code with [phpstan](https://phpstan.org/), run `./vendor/bin/phpstan`.

**Remove errors from baseline:**
While changing the code you might see the following error from the PhpStan
```
Ignored error pattern #.... was not matched in reported errors.
```
This means that the error is [no longer present](https://phpstan.org/user-guide/ignoring-errors#reporting-unused-ignores) in the code, so you can remove it from the baseline file.
To do so, run `./vendor/bin/phpstan --generate-baseline=.phpstan/baseline.neon` and commit the changes.


### Pull Requests

* Fill in [the required template](https://github.com/bigcommerce/bigcommerce-api-php/pull/new/master)
* Include screenshots and animated GIFs in your pull request whenever possible.
* End files with a newline.

## Styleguides

### Git Commit Messages

* Use the present tense ("Add feature" not "Added feature")
* Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
* Limit the first line to 72 characters or less
* Reference pull requests and external links liberally


