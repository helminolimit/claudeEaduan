# ErrorException - Internal Server Error

**Error:** Attempt to read property "name" on null

| Key | Value |
|-----|-------|
| PHP | 8.4.20 |
| Laravel | 13.4.0 |
| Host | localhost:8000 |

---

## Stack Trace

| # | Location |
|---|----------|
| 0 | resources\views\livewire\complaints\index.blade.php:52 |
| 1 | vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:37 |
| 2 | vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:38 |
| 3 | vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76 |
| 4 | vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:16 |
| 5 | vendor\laravel\framework\src\Illuminate\View\View.php:208 |
| 6 | vendor\laravel\framework\src\Illuminate\View\View.php:191 |
| 7 | vendor\laravel\framework\src\Illuminate\View\View.php:160 |
| 8 | vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:410 |
| 9 | vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:461 |
| 10 | vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:402 |
| 11 | vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:81 |
| 12 | vendor\livewire\livewire\src\LivewireManager.php:102 |
| 13 | vendor\livewire\livewire\src\Features\SupportPageComponents\HandlesPageComponents.php:19 |
| 14 | vendor\livewire\livewire\src\Features\SupportPageComponents\SupportPageComponents.php:118 |
| 15 | vendor\livewire\livewire\src\Features\SupportPageComponents\HandlesPageComponents.php:14 |
| 16 | vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:36 |
| 17 | vendor\laravel\framework\src\Illuminate\Container\Util.php:43 |
| 18 | vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:96 |
| 19 | vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:35 |
| 20 | vendor\laravel\framework\src\Illuminate\Container\Container.php:799 |
| 21 | vendor\livewire\livewire\src\Mechanisms\HandleRouting\LivewirePageController.php:15 |
| 22 | vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php:46 |
| 23 | vendor\laravel\framework\src\Illuminate\Routing\Route.php:269 |
| 24 | vendor\laravel\framework\src\Illuminate\Routing\Route.php:215 |
| 25 | vendor\laravel\framework\src\Illuminate\Routing\Router.php:822 |
| 26 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180 |
| 27 | vendor\laravel\framework\src\Illuminate\Auth\Middleware\EnsureEmailIsVerified.php:41 |
| 28 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 29 | vendor\laravel\boost\src\Middleware\InjectBoost.php:22 |
| 30 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 31 | vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:52 |
| 32 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 33 | vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63 |
| 34 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 35 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestForgery.php:104 |
| 36 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 37 | vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48 |
| 38 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 39 | vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120 |
| 40 | vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63 |
| 41 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 42 | vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36 |
| 43 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 44 | vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74 |
| 45 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 46 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137 |
| 47 | vendor\laravel\framework\src\Illuminate\Routing\Router.php:821 |
| 48 | vendor\laravel\framework\src\Illuminate\Routing\Router.php:800 |
| 49 | vendor\laravel\framework\src\Illuminate\Routing\Router.php:764 |
| 50 | vendor\laravel\framework\src\Illuminate\Routing\Router.php:753 |
| 51 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200 |
| 52 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180 |
| 53 | vendor\livewire\livewire\src\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware.php:19 |
| 54 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 55 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21 |
| 56 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31 |
| 57 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 58 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21 |
| 59 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51 |
| 60 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 61 | vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27 |
| 62 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 63 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109 |
| 64 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 65 | vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61 |
| 66 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 67 | vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58 |
| 68 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 69 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22 |
| 70 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 71 | vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:28 |
| 72 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219 |
| 73 | vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137 |
| 74 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175 |
| 75 | vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144 |
| 76 | vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220 |
| 77 | public\index.php:20 |
| 78 | vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23 |

---

## Request

**Method:** `GET /complaints`

### Headers

| Header | Value |
|--------|-------|
| host | localhost:8000 |
| connection | keep-alive |
| sec-ch-ua-platform | "Windows" |
| user-agent | Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 |
| sec-ch-ua | "Brave";v="147", "Not.A/Brand";v="8", "Chromium";v="147" |
| x-livewire-navigate | 1 |
| sec-ch-ua-mobile | ?0 |
| accept | */* |
| sec-gpc | 1 |
| accept-language | en-US,en;q=0.6 |
| sec-fetch-site | same-origin |
| sec-fetch-mode | cors |
| sec-fetch-dest | empty |
| referer | http://localhost:8000/categories |
| accept-encoding | gzip, deflate, br, zstd |

---

## Route Context

| Key | Value |
|-----|-------|
| controller | Livewire\Mechanisms\HandleRouting\LivewirePageController |
| route name | complaints.index |
| middleware | web, auth, verified |

---

## Route Parameters

No route parameter data available.

---

## Database Queries

| # | Driver | Query | Time |
|---|--------|-------|------|
| 1 | mysql | `select * from sessions where id = 'lFLgq6QLsgYiBaN4mB7IO2ZYpCXw7nzZ6nRDtHoh' limit 1` | 26.24 ms |
| 2 | mysql | `select * from users where id = 22 limit 1` | 0.9 ms |
| 3 | mysql | `select count(*) as aggregate from complaints where complaints.deleted_at is null` | 2.93 ms |
| 4 | mysql | `select * from complaints where complaints.deleted_at is null order by created_at desc limit 10 offset 0` | 0.56 ms |
| 5 | mysql | `select * from users where users.id in (5, 7, 11, 13, 16, 18, 20)` | 0.6 ms |
| 6 | mysql | `select * from categories where categories.id in (1, 2, 5, 6, 7) and categories.deleted_at is null` | 0.5 ms |
| 7 | mysql | `select * from users where users.id in (2, 3, 4)` | 0.34 ms |
