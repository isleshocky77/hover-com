# Hover.com Command-line Utility

This is a command line utility for managing Hover.com written in PHP

## Usage

2. Run `composer install`
3. To list available commands run `./bin/console`
4. To list domains run `./bin/console hover-com:login` to login and grab a authenticated cookie
5. To list domains run `./bin/console hover-com:domains:list`
6. To list all dns entries of listed domains which match content ` ./bin/console hover-com:dns:list --filter-content="123.123.123.123" dom123456 dom654321 dom66666`
7. To update a dns entry `./bin/console hover-com:dns:update --content="1.1.1.1" dns123456 dns654321`
8. To delete a dns entry `./bin/console hover-com:dns:delete dns123456 dns654321`


## Docker

```bash
$ docker-compose build
$ docker-compose run console
$ docker-compose run console hover-com:login
$ docker-compose run console hover-com:domains:list
```

## Get a list of DNS Ids based on content

```
docker-compose run console hover-com:dns:list --filter-content="123.123.123.123" --all-domains | sed -nr 's/.*(dns[0-9]+).*/\1/p' | tr "\n" " "
```
