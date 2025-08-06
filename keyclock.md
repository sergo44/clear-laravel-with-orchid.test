# Устанавливаем keycloack

```bash
docker run -p 8080:8080 -e KEYCLOAK_ADMIN=admin  -e KEYCLOAK_ADMIN_PASSWORD=admin quay.io/keycloak/keycloak:latest start-dev
```

Создаем RELAM laravel
Создаем клиента с возможностью авторизации  laravel-app

add to AppServiceProvider  (boot)
```php 
Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
    $event->extendSocialite('keycloak', \SocialiteProviders\Keycloak\Provider::class);
});
```

php artisan event:list | grep SocialiteWasCalled
php artisan make:controller Auth/KeycloakController
php artisan orchid:screen KeycloakLoginScreen
