# Internal Tools API

## Technologies
- Langage : PHP 8.4
- Framework : Symfony 8
- ORM : Doctrine ORM
- Base de données : PostgreSQL 15
- -Serveur web : Nginx
- Port API : http://localhost:8000

## Quick Start
- Api disponible sur http://localhost:8000
- Documentation: http://localhost:8000/api/doc
1. `docker-compose --profile all up -d`
- Cela démarre :
- PostgreSQL (avec initialisation via init.sql)
- PHP
- Nginx
- pgAdmin


3. Installer les dépendances PHP
- Dans le conteneur PHP :
   ```
   docker exec -it internal-tools-php bash
   composer install
   composer require symfony/serializer-pack
   composer require --dev symfony/test-pack
   composer require --dev symfony/maker-bundle
   ```

## Configuration
- Variables d'environnement: voir .env
```
  POSTGRES_DATABASE=internal_tools
  POSTGRES_USER=
  POSTGRES_PASSWORD=
  POSTGRES_PORT=
```
  
## Base de données
- postgresql/init.sql

## tests
- php bin/phpunit






