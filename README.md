# Hover.com Command-line Utility

This is a command line utility for managing Hover.com written in PHP

## Usage

1. Copy `.env.dist` to `.env` and update the file with your correct username and password
2. Run `composer install`
3. To list available commands run `./bin/console`
4. To list domains run `./bin/console hover.com:domains:list`
5. To list all dns entries of listed domains which match content ` ./bin/console hover-com:dns:list --filter-content="123.123.123.123" dom123456 dom654321 dom66666`
6. To update a dns entry `./bin/console hover-com:dns:update --content="1.1.1.1" dns123456 dns654321`
