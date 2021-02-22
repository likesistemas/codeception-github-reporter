# [GitHubReporter] Reporter for PHP testing frameworks

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