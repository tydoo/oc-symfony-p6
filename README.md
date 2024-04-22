[![SymfonyInsight](https://insight.symfony.com/projects/491e11df-483d-4efd-8fa4-7e2cdc93a136/big.svg)](https://insight.symfony.com/projects/491e11df-483d-4efd-8fa4-7e2cdc93a136)

# PROJET 6
## Formation Développeur d'application - PHP/Symfony - OpenClassrooms

### Installation d'un environnement de développement

#### Prérequis
 1. [PHP 8.2](https://www.php.net/downloads) ou supérieur et ses extensions :
	 1. [Ctype](https://www.php.net/book.ctype)
	 2. [iconv](https://www.php.net/book.iconv)
	 3. [PCRE](https://www.php.net/book.pcre)
	 4. [Session](https://www.php.net/book.session)
	 5. [SimpleXML](https://www.php.net/book.simplexml)
	 6. [Tokenizer](https://www.php.net/book.tokenizer)
	 7. [Mbstring](https://www.php.net/book.mbstring)
	 8. [Intl](https://www.php.net/book.intl)
	 9. [PDO + PDO Mysql](https://www.php.net/book.pdo)
	 10. [OPcache](https://www.php.net/book.opcache)
	 11. [cURL](https://www.php.net/book.curl)
 2. [Composer](https://getcomposer.org/doc/00-intro.md)
 3. Git
 4. [Docker + Docker Compose](https://www.docker.com/)

### Démarrage de l'environnement de développement
```bash
composer install
docker-compose up -d
symfony console importmap:install
symfony console d:m:m --no-interaction
symfony console d:f:l --no-interaction
symfony serve -d
```

### Utilisateurs
|Utilisateur|Mot de passe|
|:---------------:|:---------------:|
|thomas@tydoo.fr|112233|

### Email
Pour lire les emails, cliquez sur "Webmail" dans la bare de débug.
