# Lingo

Lingo is a package for Laravel that provides a layer of abstraction about the complexity of integrating with any rest service. so it is possible to encapsulate http communication concepts in a much closer object-oriented design and thus improve developer experience.

## Installation

Use the package manager [composer](https://getcomposer.org/) to install Lingo.

```bash
composer require galafeno/lingo
```

## Usage

Use the command `make:lingo` to initialize a new Lingo inside your application.

```
    php artisan make:lingo AwesomeApi --base_url=https://awesome.api/v1
```
That command creates a class called `AwesomeApiLingo` within the folder `App\Lingos`.

To model the API you should add the endpoints inside the `sync` section into the `AwesomeApiLingo` class.

```php
    protected $sync = [
        'base_url' => 'https://awesome.api/v1',
        'commands' => [
            'getMovie' => [
                'verb' => 'get',
                'url' => "/movies/{:?}"
            ],
        ]
    ];
```

After that, you can use your Lingo inside your application like so:

```php
    $movie = Lingo::awesomeApi()->command('getMovie',1)->send();

    echo $movie->name; // Batman v Superman: Dawn of Justice
```

The `command` method should receive at least one parameter (the command name). In that case it received 2 parameters because the `getMovie` command has a binding value (marked with the `{:?}` wildcard). 

#### Custom Headers

Lingo was designed to handle json data type, so it will always append the `Content-Type: application/json` and `Accept: application/json` into default headers. To define another headers, create a `headers` property into your Lingo Class

```php
    protected $headers = [
        'DomainId' => 15,
        'SafeMode' => 'Unguarded'
    ];
```

That will produce the follow headers in every request:

```json
{
    "Content-Type": "application/json",
    "Accept": "application/json",
    "DomainId": "15",
    "SafeMode": "Unguarded"
}
```

You can also define extra headers in runtime like so:

```php
    Lingo::awesomeApi()
    ->command('getMovie',1)
    ->withHeaders(['Scope' => 'readOnly'])
    ->send();
```

#### Query parameters

To define query parameters, create a `params` property into your Lingo Class

```php
    protected $params = [
        'start' => '2020-02-02',
        'end' => '2020-04-04'
    ];
```

That will insert the follow query parameters in every request:

```json
    GET https://awesome.api/v1/movies/1?start=2020-02-02&end=2020-04-04
```

You can also define extra query parameters in runtime like so:

```php
    Lingo::awesomeApi()
    ->command('getMovie',1)
    ->withParams(['user_id' => 1])
    ->send();
```


#### Body Parameters

To define body parameters, create a `data` property into your Lingo Class

```php
    protected $data = [
        'username' => 'superuser',
        'email' => 'superuser@super.user'
    ];
```

That will insert the follow body parameters in every request:

```json
    POST https://awesome.api/v1/movies
    {
        "username": "superuser",
        "email": "superuser@super.user"
    }
```

You can also define extra body parameters in runtime like so:

```php
    Lingo::awesomeApi()
    ->command('getMovie',1)
    ->withData(['user_id' => 1])
    ->send();
```

#### Handling Authentication

In real world applications rest services implements some type of authentication scheme. If you are dealing with a simple aproach like a signature or key into request headers you should simply use the `withHeaders` command to attach your auth configuration into Lingo. But some services utilizes especifics schemes. To handle it you should define an `auth` configuration within your lingo class. currently, this package suporte `apiKeys` and `oauth2` auth methods.

##### apiKeys

That method will attach the key configuration as a query parameter.

```php
    protected $sync = [
        'base_url' => 'https://protected.rest/v1',
        'auth' => [
            'apiKeys' => [
                'my-key' => 'my-secret'
            ]
        ]
        'commands' => [
            ...
        ]
    ];
```

```json
    GET https://protected.rest/v1/someurl?my-key=my-secret
```

##### oauth2

That method will handle the oauth2 flow to retrieve the jwt and cache it using your application cache configuration.

```php
    protected $sync = [
        'base_url' => 'https://protected.rest/v1',
        'auth' => [
            'oauth2' => [
                'grant_type' => 'client_credentials',
                'client_id' => 'my-client-id',
                'client_secret' => 'my-client-secret'
            ]
        ]
        'commands' => [
            ...
        ]
    ];
```

#### Mockup mode

While testing your application you must define `APP_ENV=testing` in your `.env` to activate Lingo mockup mode. While using this mode the package will bypass any real request by a mocked static data. You should define that data within your command configuration section using the `shouldReturn` key.

```php
    protected $sync = [
        'base_url' => 'https://service.rest/v1',
        'commands' => [
            'getMovie' => [
                'verb' => 'get',
                'url' => "/movies/{:?}",
                'shouldReturn' => [
                    'id' => 1
                    'name' => 'static name',
                    'genre' => 'drama',
                    'length' => 97
                ]
            ],
        ]
    ];

    ...

    $movie = Lingo::awesomeApi()->command('getMovie',1)->send();
    echo $movie->name; // static name
```

#### Async Communication

If your application utilize AWS infrastructure you may configure assincronous message witihin your Lingo class. To do so, you must configure your `async` section.

```php
    protected $async = [
        'queue' => 'myqueue',
        'commands' => [
            'sendEmail' => [
                'action' => 'SendEmail'
            ]
        ]
    ];
```

To send a assincronous you have to use the `async` method.

```php
Lingo::awesomeApi()->command('sendEmail')
    ->withData($data)
    ->async()
    ->send();
```

That will push to your sqs queue the payload:

```php
    [
        'action' => 'sendEmail',
        'data' => $data
    ]
```

## Roadmap

- [ ] unit and feature tests
- [ ] export `sync` section to a swagger file
- [ ] accepts a closure into `shouldReturn` section, so you can deal with dinamically generated mockup data
- [ ] add a polymorphic option into `make:lingo`. it will be nice create, for instance a `PaymentLingo` that is a interface and a `StripeLingo` as a concrete classe that implements `PaymentLingo`
- [ ] to allow a sync command trigger a closure or a class method instead of a http request.
- [ ] custom exceptions
- [ ] a built-in log system


## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)


