# Composer Integration Plugin

Quick switching between integration dependencies

## Installation

```bash
composer require --dev no-response-mate/composer-integration-plugin
```

## Configuration

Add this plugin to allowed plugins:

```json
"config": {
    "allow-plugins": {
        "no-response-mate/composer-integration-plugin": true
    }
},
```

Add your integration configuration:

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

## Usage

Once you have your integrations configured you can switch between them by running:

```bash
composer integration my-integration
```

To return to your base composer dependencies simply run:

```bash
composer install
```
