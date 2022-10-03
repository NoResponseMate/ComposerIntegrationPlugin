# Composer Integration Plugin

Quick switching between integration dependencies

## Installation

```bash
composer require --dev no-response-mate/composer-integration-plugin
```

Add this plugin to allowed plugins:

```json
"config": {
    "allow-plugins": {
        "no-response-mate/composer-integration-plugin": true
    }
},
```

## Configuration

Basic integration configuration:

```json
"extra": {
    "integration": {
        "my-integration": {
            "require": {
                "league/uri": "^6",
                "psr/http-factory": "^1"
            }
        }
    }
}
```

With a custom `APP_ENV` variable if you're using .env files:

```json
"extra": {
    "integration": {
        "my-integration": {
            ...
            "env": "my-integration-special-environment"
        }
    }
}
```

If your .env files are located somewhere else than project root, you can point to their location using the `integration-options.env-path` node:

```json
"extra": {
    "integration-options": {
        "env-path": "public/app/env/"
    },
    "integration": {
        "my-integration": {
            ...
            "env": "my-integration-special-environment"
        }
    }
}
```

The `env-directory` node takes in a path relative to your current working directory (usually project root).

## Usage

Once you have your integrations configured you can switch between them by running:

```bash
composer integration my-integration
```

To return to your base composer dependencies simply run:

```bash
composer install
```

---

By default, no scripts are run when installing an integration, if you would like to enable them use the `with-scripts` option:

```bash
composer integration my-integration --with-scripts
```

The plugin uses the `install` command internally and as such, only install related scripts would be run.
