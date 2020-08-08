**Task description:**  

It develops an address book in which we can add, edit and delete entries. we can also have an overview of all contacts.

*The address book contains the following data:*  

Firstname  
Lastname  
Street and number  
Zip  
City  
Country  
Phone number  
Birthday  
Email address  
Picture (optional)  

**Tech Stack:**        
Symfony 3.4  
Doctrine with SQLite  
Twig  
PHP 7.0


**Project Setup:**
 
- run `composer install` to install dependencies
- DB connection is defined in `.env` file
- run `php bin/console doctrine:database:create` to create DB
- run `php bin/console doctrine:migrations:migrate` to migrate DB schema
- run `php bin/console server:run` to start web server



