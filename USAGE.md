Usage & Extending eins23 WebService
===================================

### Example controller and modules
*Authors* and *Aphorisms* outline custom services – they are only for example purposes!   
Take a look at:  
 - [custom.php](app/config/custom.php) 
 - [AuthorsController.php](app/webservice/controller/AuthorsController.php) 
 - [AuthorsAphorismsController.php](app/webservice/controller/AuthorsAphorismsController.php) 
 - [AuthorsModel.php](app/webservice/models/AuthorsModel.php)  
 - [AuthorsAphorismsModel.php](app/webservice/models/AuthorsAphorismsModel.php)  

for a detailed description and insight of how to add your own services consisting of controllers and models.  

**Note:**  
These example outputs are made with following configurations in [config.php](app/config/config.php)  
```php
$GLOBALS['CONFIG']['APPLICATION']['FLAT_HIERARCHY'] = false;  
$GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_RESOURCE_LOCATION'] = true;  
$GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_WEBSERVICE_STATS'] = true;  
```


### Example actions

**Method OPTIONS**  
Service: retrieve headers on any resource  
to check out allowed methods 

**Method GET**  
Service: retrieve info about a resource  
Resource: /authors/info  

```javascript
// @jsonbody:
// no body sent
//
// @return: 200 header OK
// @return: json
{
  "status": "success",
  "scope": "any",
  "webservice": {
    "start": "2017-04-26T16:29:23+0200",
    "stop": "2017-04-26T16:29:24+0200",
    "elapsed": 0.306019067764,
    "resource": "http://localhost/authors/info",
    "method": "GET"
  },
  "data": {
    "info": "This ressource manages the Authors entries.",
    "resources": [
      "authors",
      "authors/{id}",
      "authors/info"
    ],
    "fields": {
      "id": {
        "type": "int",
        "autoIncrement": true,
        "primaryKey": true,
        "omit": [
          "post"
        ]
      },
      "authorname": {
        "type": "varchar",
        "unique": true,
        "required": true
      }
    }
  }
}
```

**Method POST**  
Service: Add new author  
Resource: /authors  

```javascript
// Required data:
// authorname (varchar)
// @jsonbody:
{
	"authorname":"Steve Wosniak"
}
// 
// @return: 201 header created
// @return: new location resource (let's assume id = 99)
{
  "status": "success",
  "scope": "resource",
  "webservice": {
    "start": "2017-04-26T16:31:13+0200",
    "stop": "2017-04-26T16:31:13+0200",
    "elapsed": 0.30345413069,
    "resource": "http://localhost/authors",
    "method": "POST"
  },
  "data": {
    "location": [
      "http://localhost/authors/99"
    ]
  }
}

```

**Method PUT**  
Service: Edit author  
Resource: /authors/99  

```javascript
// Data:
// authorname (varchar)
// 
// authors/99 points to a valid resource (see above)
// @jsonbody:
{
	"authorname":"Steve Wozniak"
}
// 
// @return: 204 header
// @return: no content

```

**Method POST**  
Service: Add new aphorism  
Resource: /authors/99/aphorisms  

```javascript
// Required data:
// aphorism (varchar)
//
// authors/99 points to a valid parent resource (see above)
// @jsonbody:
{
    "aphorism":"Never trust a computer you can't throw in a window"
}
// 
// @return: 201 header created
// @return: new location resource (let's assume id = 1)
{
  "status": "success",
  "scope": "resource",
  "webservice": {
    "start": "2017-04-26T16:37:23+0200",
    "stop": "2017-04-26T16:37:23+0200",
    "elapsed": 0.30345416069,
    "resource": "http://localhost/authors/99/aphorisms",
    "method": "POST"
  },
  "data": {
    "location": [
      "http://localhost/authors/99/aphorisms/1"
    ]
  }
}

```

**Method: PUT**  
Service: Edit aphorism  
Resource: authors/99/aphorisms/1  

```javascript
// Data:
// aphorism (varchar)
//
// authors/99 points to a valid parent resource (see above)
// aphorisms/1 points to a valid resource (see above)
// @jsonbody:
{
    "aphorism":"Never trust a computer you can't throw out a window."
}
// 
// @return: 204 header
// @return: no content

```

**Method POST**  
Service: Add new aphorism  
Resource: /authors/99/aphorisms  

```javascript
// Required data:
// aphorism (varchar)
//
// authors/99 points to a valid parent resource (see above)
// @jsonbody:
{
    "aphorism":"If you love what you do and are willing to do what it takes, it's within your reach. […]"
}
// 
// @return: 201 header created
// @return: new location resource (let's assume id = 2)
{
  "status": "success",
  "scope": "resource",
  "webservice": {
    "start": "2017-04-26T16:44:55+0200",
    "stop": "2017-04-26T16:44:55+0200",
    "elapsed": 0.32362346069,
    "resource": "http://localhost/authors/99/aphorisms",
    "method": "POST"
  },
  "data": {
    "location": [
      "http://localhost/authors/99/aphorisms/2"
    ]
  }
}

```

**Method GET**  
Service: Get authors aphorisms  
Resource: /authors/99/aphorisms  

```javascript
// authors/99 points to a valid parent resource (see above)
// @jsonbody:
// no body sent
//
// @return: 200 header OK
// @return: json collection of aphorisms
{
  "status": "success",
  "scope": "collection",
  "webservice": {
    "start": "2017-04-26T16:45:15+0200",
    "stop": "2017-04-26T16:45:16+0200",
    "elapsed": 0.339831113815,
    "resource": "http://localhost/authors/99/aphorisms",
    "method": "GET"
  },
  "data": {
    "aphorisms": [
      {
        "id": 1,
        "authorsId": 1,
        "created": "2017-04-25T10:49:10+0200",
        "changed": "2017-04-25T10:49:10+0200",
        "aphorism": "Never trust a computer you can't throw out a window.",
        "location": [
          "http://localhost/authors/99/aphorisms/1"
        ]
      },
      {
        "id": 8,
        "authorsId": 1,
        "created": "2017-04-26T16:37:23+0200",
        "changed": "2017-04-26T16:37:23+0200",
        "aphorism": "If you love what you do and are willing to do what it takes, it's within your reach. […]",
        "location": [
          "http://localhost/authors/99/aphorisms/2"
        ]
      }
    ]
  }
}
```

**Method DELETE**  
Service: Delete aphorism  
Resource: /authors/99/aphorisms/2  

```javascript
// authors/99 points to a valid parent resource (see above)
// aphorisms/2 points to a valid resource (see above)
// 
// @jsonbody:
// no body sent
//
// @return: 204 header
// @return: no content
```

**Method DELETE**  
Service: Delete author  
Resource: /authors/99  

```javascript
// authors/99 points to a valid parent resource (see above)
// deletes also all related aphorisms
// 
// @jsonbody:
// no body sent
//
// @return: 204 header
// @return: no content
```

### Learn how to add custom controllers.  
***…more to come!***