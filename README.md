PHP WebService REST API
=======================

A tiny and simple RESTful API w/ "Basic" and "Token" based HTTP Authorization.  

### Dependencies
> *Be sure to read the documentations of the following project dependencies.*

**GIT » obviously**

**PHP-JWT » [on GitHub](https://github.com/firebase/php-jwt)**  
A simple library to encode and decode JSON Web Tokens (JWT)  

**MysqliDb » [on GitHub](https://github.com/yodorada/PHP-MySQLi-Database-Class)**  
Simple MySQLi wrapper and object mapper with prepared statements.

**Swift Mailer » [Homepage](http://swiftmailer.org)**  
A component based mailing solution for PHP 5 >= .  
**Note: *not implemented yet***   

### Group- and User-Management

There are three diffent types of groups:
 - Admins
 - Editors 
 - Users

The rights to perform methods on Groups and Users are hierarchically fixed in the system.  
The rights to perform methods from custom services are specified separately on group records (or user records).  

"Admins" have the right to perform any of the provided methods of the different services.  

"Editors" may edit their own user account and can edit and create new groups with type "Users" and add users as such therein. They may also specify the rights for groups of type "Users" and override group rights on user records.  

"Users" may edit their own user account and perform methods on custom controllers as specified for their user group (or when overwritten those methods attached to their user records).  


**Initial name/password combinations**  
when setup used from [demouser.php](resources/demouser.php)  
> Group: Admins  
> Name: admin  
> Password: adminPassword99

> Group: Editors  
> Name: editor  
> Password: editorPassword99

> Group: Users  
> Name: user  
> Password: userPassword99


### Setup  
 - run ```composer install``` in directory: [app](app/)  
 - run a local Apache webserver (php >=5.5 recomm. >=7.x, mysql >=5, mod_rewrite) pointing it's root to this directory: [public/api](public/api)  
 - Create a database and execute the following sql: [database.sql](resources/database.sql)  
 - you can also run a Docker Container, make sure to edit Docker Specs accordingly: [docker-compose.yml](docker-compose.yml)  
 - For demo purposes execute the following sql: [demodata.sql](resources/demodata.sql)  
 - Copy the following PHP file to your webserver root and open it in a browser: [demouser.php](resources/demouser.php)  
 - Edit the database file for database credentials: [database.php](app/config/database.php)
 - Edit the config file for other settings: [config.php](app/config/config.php)
 - Open your API testing tool and start your requests – recommended: [Postman](https://www.getpostman.com/)  
 - don't forget to use HTTP Authorization headers "Basic Auth"  
 - or use "Token Auth" after initial login (Header: Authorization Token *your-token-string*)  
 - other HTTP Headers » Content-Type: application/json and Realm: *base64 encoded string $GLOBALS['CONFIG']['API']['ADMIN_KEY'] from config.php file*   

### First run
Start a GET request to [http://localhost](http://localhost) for a list of available services.  
**In production always use SSL!**  

### Recommendation API Subdomain
We recommend using the API on a subdomain separate from your frontend/backend application. Like [http://api.localhost](http://api.localhost) for your API and [http://www.localhost](http://www.localhost) for your application.  

For that scenario set the domain of your frontend/backend app
 - set API subdomain root directory to [public/api](public/api)  
 - set application root directory to [public](public)  
 - and set ```$GLOBALS['CONFIG']['APPLICATION_HOST'] = "http://www.localhost";``` in your [config.php](app/config/config.php) file, so that possible static files (images, videos, etc) will be referenced to that host.

In case you run both API and frontend/backend on the same domain make sure to  
 - set root directory to [public](public)  
 - and set ```$GLOBALS['CONFIG']['API_DIRECTORY'] = "api/";``` in your [config.php](app/config/config.php) file.


### Usage & Extensions
Group and User data can be easily extended by custom fields.  
You can also add your custom controllers for adding further services to your API.  
Check out how here: [USAGE.md](USAGE.md)  
**Note: *Extending is not yet update-safe :(***  



### Good reads
 - http://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api 
 - http://restful-api-design.readthedocs.io/en/latest/  
 - http://www.restapitutorial.com/  
 - https://github.com/marmelab/awesome-rest  
