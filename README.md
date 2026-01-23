# Internal Tools API

## Technologies
- Langage : PHP 8.4
- Framework : Symfony 8
- ORM : Doctrine ORM
- Base de données : PostgreSQL 15
- -Serveur web : Nginx
- Port API : http://localhost:8000

## Quick Start

1. `docker-compose --profile up`
- Cela démarre :
- PostgreSQL (avec initialisation via init.sql)
- PHP (Symfony)
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
5. Accéder à l’API
- http://localhost:8000
- Exemple de endpoints GET http://localhost:8000/api/tools

## Configuration
- Variables d'environnement: voir .env
- POSTGRES_DATABASE=internal_tools
  POSTGRES_USER=
  POSTGRES_PASSWORD=
  POSTGRES_PORT=
  
## Base de données
- postgresql/init.sql

## APIs testée avec Postman
GET /api/tools
GET /api/tools/filter?ownerDepartment=Design&status=active





