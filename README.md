# SymfProj

Backend + simple frontend demo on Symfony 6.4 for:
- registration/login with JWT
- forgot/reset password by email
- roles: `ROLE_ADMIN` and `ROLE_USER`
- user interests (`users` <-> `interests`, many-to-many)
- admin filters users by user fields and interest (dropdowns)
- admin can edit users (name/email/role/password/interests)
- Redis caching for admin user list endpoint

## 1) Stack

- PHP 8.1
- Symfony 6.4
- Doctrine ORM + Migrations
- MySQL 8
- Redis
- JWT: `lexik/jwt-authentication-bundle`
- Mailer + Mailpit (email catcher)
- Docker Compose

## 2) Run project

1. Start containers:

```bash
docker compose up -d
```

2. Install dependencies (if needed):

```bash
docker compose exec php composer install
```

3. Run migrations:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

4. Seed demo data:

```bash
docker compose exec php php bin/console app:seed-demo-data
```

Demo users:
- admin: `admin@example.com` / `admin123`
- user: `user@example.com` / `user123`

## 3) Frontend pages (very simple demo UI)

Main page is login:
- `http://127.0.0.1/` - login
- `http://127.0.0.1/register` - registration
- `http://127.0.0.1/forgot-password` - password recovery
- `http://127.0.0.1/dashboard/user` - user page
- `http://127.0.0.1/dashboard/admin` - admin page

How it works:
- frontend stores JWT in `localStorage` (`jwt_token`)
- after login UI calls `/api/me`
- by role redirects to:
  - `ROLE_ADMIN` -> `/dashboard/admin`
  - `ROLE_USER` -> `/dashboard/user`

## 4) Mail (password recovery)

- Mailpit web UI: `http://127.0.0.1:8025`
- SMTP host inside docker: `mailer:1025`
- env var: `MAILER_DSN=smtp://mailer:1025`
- reset page base url: `APP_BASE_URL=http://127.0.0.1`
- sender: `MAIL_FROM=no-reply@symfproj.local`

For real mailbox delivery (Gmail/SendGrid/etc), override `MAILER_DSN` and `MAIL_FROM` in `.env.local`.
Example:

```dotenv
MAILER_DSN=smtp://username:password@smtp.your-provider.com:587
MAIL_FROM=your-real-email@domain.com
APP_BASE_URL=https://your-domain.com
```

Flow:
1. `POST /api/auth/forgot-password` with email
2. email contains token + clickable link `/forgot-password?token=...`
3. `POST /api/auth/reset-password` with `token` + `newPassword`

## 5) API endpoints

Public:
- `POST /api/auth/register`
- `POST /api/login_check`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`

User (`ROLE_USER`):
- `GET /api/me`
- `PATCH /api/me`
- `PUT /api/me/interests`
- `GET /api/interests`

Admin (`ROLE_ADMIN`):
- `GET /api/admin/users`
- `GET /api/admin/users/{id}`
- `PATCH /api/admin/users/{id}`
- `GET /api/admin/interests`
- `POST /api/admin/interests`

## 6) Project structure (what is where)

### Core entities
- `src/Entity/User.php`
- `src/Entity/Interest.php`
- `src/Entity/PasswordReset.php`

### Data access
- `src/Repository/UserRepository.php`
- `src/Repository/InterestRepository.php`
- `src/Repository/PasswordResetRepository.php`

### DTO and mapping
- input DTO: `src/DTO/Input/User/StoreUserInputDTO.php`
- output DTO: `src/DTO/Output/User/UserOutputDTO.php`
- nested output DTO: `src/DTO/Output/Interest/InterestOutputDTO.php`
- factory mapper: `src/Factory/UserFactory.php`

### Validation and exceptions
- validator: `src/DTOValidator/UserDTOValidator.php`
- custom constraint: `src/Validator/Constraint/EntityExists.php`
- custom constraint validator: `src/Validator/Constraint/EntityExistsValidator.php`
- custom exception: `src/Exception/ValidateException.php`
- api exception subscriber: `src/EventSubscriber/ApiExceptionSubscriber.php`

### Services
- user business logic: `src/Service/UserService.php`

### API controllers
- auth: `src/Controller/AuthController.php`
- current user: `src/Controller/MeController.php`
- admin users: `src/Controller/AdminUserController.php`
- admin interests: `src/Controller/AdminInterestController.php`
- public interests: `src/Controller/InterestController.php`

### Web pages controller
- `src/Controller/PageController.php`

### Serialization response layer
- response builder: `src/ResponseBuilder/UserResponseBuilder.php`
- resource serializer: `src/Resource/UserResourse.php`

### Config
- security/jwt: `config/packages/security.yaml`, `config/routes/jwt.yaml`, `config/packages/lexik_jwt_authentication.yaml`
- doctrine: `config/packages/doctrine.yaml`
- cache(redis): `config/packages/cache.yaml`
- mailer: `config/packages/mailer.yaml`
- services: `config/services.yaml`

### Templates
- `templates/pages/login.html.twig`
- `templates/pages/register.html.twig`
- `templates/pages/forgot_password.html.twig`
- `templates/pages/user_dashboard.html.twig`
- `templates/pages/admin_dashboard.html.twig`

## 7) Interests integration details

Input:
- `StoreUserInputDTO` has `interestIds: int[]`

Output:
- `UserOutputDTO` has `interests: InterestOutputDTO[]`

Mapping:
- `UserFactory::makeUser()` maps `interestIds` -> real entities
- `UserFactory::makeStoreUserOutputDTO()` maps user interests -> output DTO array

## 8) Roles and access

User entity keeps role in attribute `role`.

Security rules:
- `/api/admin/*` only `ROLE_ADMIN`
- `/api/*` requires authenticated user (`ROLE_USER` baseline)
- login/register/forgot/reset are public

## 9) Redis caching

Cached endpoint:
- `GET /api/admin/users` (with filters)

Cache key is built from filter params + pagination.

Invalidation:
- cache is cleared on user/interest updates in service/relevant controllers.

## 10) Helpful commands

Check routes:

```bash
docker compose exec php php bin/console debug:router
```

Clear cache:

```bash
docker compose exec php php bin/console cache:clear
```

Lint container and twig:

```bash
docker compose exec php php bin/console lint:container
docker compose exec php php bin/console lint:twig templates
```
