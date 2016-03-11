# Contributing

**Phony** is open source software; contributions from the community are
encouraged. Please take a moment to read these guidelines before submitting
changes.

## Code style

All PHP code must adhere to the [PSR-2] standards. Running `make lint` will
automatically fix most issues, but be sure to stage changes first.

[psr-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

## Branching and pull requests

As a guideline, please follow this process:

1. [Fork the repository].
2. Create a topic branch for the change, branching from **develop**
(`git checkout -b branch-name develop`).
3. Make the relevant changes.
4. [Squash] commits if necessary (`git rebase -i develop`).
5. Submit a pull request to the **develop** branch.

[fork the repository]: https://help.github.com/articles/fork-a-repo
[squash]: http://git-scm.com/book/en/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages

## Tests

- Run the tests with `make test`.
- Generate a coverage report with `make coverage`.
- Run the example test suites with `make examples`.
- Run the integration tests with `make integration`. Each test suite has one
  passing test, and one failing test. This demonstrates *Phony*'s output.
- Run the benchmarks with `make benchmarks`. This compares *Phony*'s performance
  with other mocking frameworks.
