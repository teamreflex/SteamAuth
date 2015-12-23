## SteamAuth

Provides Steam OAuth authentication. Includes Laravel Service Provider and Facade.

### Use with Laravel

To use the Service Provider and Facade, make sure you add the following in your `config/app.php`:

```php
'providers' => [
    ...
    Reflex\SteamAuth\Laravel\SteamAuthServiceProvider::class
],

...

'aliases' => [
    ...
    'SteamAuth' => Reflex\SteamAuth\Laravel\SteamAuth::class
],
```
