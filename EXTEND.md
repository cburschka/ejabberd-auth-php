# Plugin creation

There are currently two supported ways to bridge with a CMS: HTTP (via REST), and direct database access (via PDO).
Alternatively, plugins may create an implementation from scratch, but these two base classes should cover most needs.

- some phpbb plugin/module/extension (I don't know the phpBB contrib architecture at all, sadly) that accepts a `POST` request with a password and returns `HTTP 200` or `HTTP 403` depending on whether the password is right. Then the URL of that plugin can be set in the `config.yml`.

## REST

The first solution requires a REST endpoint which supports a particular query format.

This is the best solution if your target CMS provides an extendable REST API or uses a complex authentication
mechanism that is not easy to duplicate. It is also required if your target CMS's database server cannot be
directly accessed from the ejabberd server.

### Server side

The REST API must support the following requests:

```
POST /endpoint
{
  "command": "isuser",
  "user":    "{$username}",
  "domain":  "{$domain}"
}

POST /endpoint
{
  "command":  "auth",
  "user":     "{$username}",
  "domain":   "{$domain}",
  "password": "{$password}"
}
```

The endpoint must return a response of `{"result": true}` with status code 200 if and only if the credentials are valid.

### Plugin class

For reusability, you may hard-code your endpoint's path into a subclass of `HttpBridge`, though this is optional
(you can also use `HttpBridge` directly, and give it the full URL including the endpoint).

```php
namespace \Ermarian\EjabberdAuth\Plugin;

use \Ermarian\EjabberdAuth\Bridge;

class MyBridge extends HttpBridge {
  /* no initial slash */
  protected static $endpoint = 'my/endpoint';
}
```

### YAML file

The plugin class is then specified in the config file (see `config.sample.yml`) as follows:

```yaml
bridges:
  plugin1:
    class: '\Ermarian\EjabberdAuth\Plugin\MyBridge'
    config:
      url: 'http://example.com'
    hosts:
      - '*'
#or
  plugin2:
    class: '\Ermarian\EjabberdAuth\Bridge\HttpBridge'
    config:
      url: 'http://example.com/my/endpoint'
    hosts:
      - '*'
```

## Database

The second method directly accesses the target system's database to check the credentials manually.

This is a good solution if your target CMS has a fairly simple authentication mechanism, and its database
server is exposed to the ejabberd server.

[The code for this is not yet finished, and you may need to modify the DatabaseBridge class for your needs.]

### Plugin class

You will need to create a subclass of `\Ermarian\EjabberdAuth\Bridge\DatabaseBridge` that implements four required methods:

* `getUserQuery`
* `getPasswordQuery`
* `checkPassword`
* `static create`

The first three specify how to query the database and verify a password against a hash; the static `create()` function
takes a config array (from the YAML) and sets up the database connection. It basically looks something like this:

```php
namespace \Ermarian\EjabberdAuth\Plugin;

use \Ermarian\EjabberdAuth\Bridge\DatabaseBridge;

class MyBridge extends DatabaseBridge {
  /**
   * Create a plugin instance.
   *
   * @param array $config
   *   The config array in the YAML file.
   */
  public static function create(array $config) {
    return new static(
      new \PDO($config['host'], $config['user'], $config['password'] /* etc */);
    );
  }

  public function getUserQuery(): \PDOStatement {
    // Create a user query.
  }
  
  public function getPasswordQuery(): \PDOStatement {
    // Create a password query.
  }
  
  public function checkPassword($username, $password, array $result) {
    // Check the username/password against the row record returned by the password query.
    // This is where you need to implement the hashing mechanism used by the CMS.
  }
}
```

### YAML file

```yaml
bridges:
  plugin1:
    class: '\xzy\yourclass'
    config:
      host: ''
      user: ''
      password: ''
      database: ''
      // (plus any other configurable stuff)
    hosts:
      - '*'
```

