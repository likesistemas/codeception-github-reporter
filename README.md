# [GitHubReporter] Reporter for PHP testing frameworks [![PHP 5.6](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-56.yml/badge.svg)](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-56.yml) [![PHP 7.3](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-73.yml/badge.svg)](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-73.yml) [![PHP 7.4](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-74.yml/badge.svg)](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-74.yml) [![PHP 8.0](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-80.yml/badge.svg)](https://github.com/likesistemas/codeception-github-reporter/actions/workflows/ci-80.yml)

## Installation

```
composer require likesistemas/codeception-github-reporter --dev
```

### Codeception

Run your tests with`Like\Codeception\GithubReporter` extension enabled: 

On Linux/MacOS:

```
VAR={VALUE} php vendor/bin/codecept run --ext "Like\Codeception\GithubReporter"
```

On Windows

```
set VAR={VALUE}&& php vendor/bin/codecept run  --ext "Like\Codeception\GithubReporter"
```

Alternatively, you can add `Like\Codeception\GithubReporter` extension to suite or global config.

```yml
extensions:
  enabled:
    - Like\Codeception\GithubReporter
```

### Enviroment Variables

- GITHUB_OWNER: Owner of repository
- GITHUB_REPO: Name of repository
- GITHUB_PR_NUMBER: Number of pull request of repository
- GITHUB_TOKEN: Token of the user's github who will post the comment 
- IMGBB_TOKEN=cfac55381b42ee1e55bf17fa185b3b51
- TEST_LANG: Language
- TEST_TITLE: Message to show in title
- TEST_FOOTER: Show footer with version of PHP
- IMGBB_TOKEN: Token to upload image acceptance test