# Internal Tools API

## Technologies
- Langage : PHP 8.4
- Framework : Symfony 8
- ORM : Doctrine ORM
- Base de données : PostgreSQL 15
- Serveur web : Nginx
- Port API : http://localhost:8000

## Quick Start
1. Lancement de Docker:
- `docker-compose --profile all up -d`

2. Installer les dépendances PHP
- Dans le conteneur PHP :
   ```
   docker exec -it internal-tools-php bash
   composer install
   composer require symfony/serializer-pack
   composer require --dev symfony/test-pack
   composer require --dev symfony/maker-bundle
   ```
3. Api disponible sur http://localhost:8000
4. Documentation: http://localhost:8000/api/doc

## Configuration
- Variables d'environnement: voir .env
```
  POSTGRES_DATABASE=internal_tools
  POSTGRES_USER=
  POSTGRES_PASSWORD=
  POSTGRES_PORT=
  DATABASE_URL
```
  
## Base de données
- postgresql/init.sql

## Tests
Commande à lancé dans le conteneur PHP
- php bin/phpunit

## Architecture
- L'application repose sur une architecture MVC adaptée à une API Rest
- Model représenté par les entité
- Conreoller situé dans Controller/
- les reponses sont retounées en JSON, ce qui correspond à une API REST


