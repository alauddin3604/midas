# REST API

The API is versioned under `/api/v1` and every response is JSON. Successful responses are built
with Laravel API Resources (`JsonResource`); errors share one envelope.

Send `Accept: application/json` on every request. Errors on `api/*` paths are always rendered as
JSON; elsewhere only when the request expects JSON.

## Success responses

Resources are wrapped in `data` and their attributes are flat. The shape is whatever the resource's
`toArray()` returns.

Single resource:

```json
{
    "data": {
        "id": "01jz8k3x9q4v6n2m5b7c8d9e0f",
        "name": "Joe Doe",
        "email": "joe@example.com",
        "role": "admin",
        "email_verified": true
    }
}
```

Collection:

```json
{
    "data": [
        {
            "id": "01jz8k3x9q4v6n2m5b7c8d9e0f",
            "name": "Joe Doe",
            "email": "joe@example.com",
            "role": "admin",
            "email_verified": true
        }
    ]
}
```

Paginated collections also carry Laravel's standard `links` and `meta` keys next to `data`.

## Error responses

Every error has the same two keys:

| Key | Type | Description |
|---|---|---|
| `message` | string | Human-readable summary of what went wrong. |
| `errors` | array | Field-level problems. Empty unless the error is a validation failure. |

Each entry in `errors` has:

| Key | Type | Description |
|---|---|---|
| `field` | string | The input name, using dot notation for nested input (`items.0.name`). |
| `error` | string | The first validation message for that field. |

A field appears once, even if it failed several rules.

### 422 Unprocessable Entity

Returned when input fails validation, or when a domain rule is rejected as bad input
(`InvalidPasswordResetToken`).

```json
{
    "message": "Validation failed.",
    "errors": [
        {
            "field": "email",
            "error": "The email field must be a valid email address."
        },
        {
            "field": "password",
            "error": "The password field is required."
        }
    ]
}
```

Login with wrong credentials is also a 422 on the `email` field, so clients can show it next to
the input:

```json
{
    "message": "Validation failed.",
    "errors": [
        {
            "field": "email",
            "error": "The provided credentials are incorrect."
        }
    ]
}
```

### 404 Not Found

Returned for unknown routes and missing records. The message is always generic so it never leaks
model names.

```json
{
    "message": "Not found.",
    "errors": []
}
```

### Other statuses

All use the same envelope with `errors` set to `[]`.

| Status | When | Source |
|---|---|---|
| 401 Unauthorized | Missing, invalid, expired or revoked token | `auth:sanctum` middleware |
| 403 Forbidden | Authenticated but not allowed | `Authorize` middleware, `CannotModifyOwnAccount` |
| 404 Not Found | Route or record does not exist | Routing, route model binding |
| 409 Conflict | State conflicts with the request | `EmailAlreadyRegistered` |
| 422 Unprocessable Entity | Validation failed | `ValidationException`, `InvalidPasswordResetToken` |
| 429 Too Many Requests | Rate limit hit (`register` and `login`: 5 per minute) | `throttle` middleware |

Domain exceptions get their status from each domain's `MapsDomainExceptions` map
(see [architecture](architecture.md#errors)).

## Authentication

The API is stateless and uses Sanctum personal access tokens. Log in to get a token, then send it
as a bearer token on every protected request:

```
Authorization: Bearer <token>
```

- Tokens expire after `SANCTUM_EXPIRATION` minutes (default 10080, seven days; `0` disables expiry).
- Logging out revokes only the token used for the request, so other devices stay signed in.
- Resetting a password or deleting a user revokes all of that user's tokens.
- Each login issues a new token named after the client's `User-Agent`.

## Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/register` | no | Register a user. Returns the user (201). Throttled. |
| POST | `/api/v1/login` | no | Exchange credentials for a token. Throttled. |
| POST | `/api/v1/logout` | bearer token | Revoke the current token. Returns 204. |

Login response:

```json
{
    "data": {
        "token": "1|k3Zr0o8wQ2mJ..."
    }
}
```

## Implementation notes

| Concern | Where |
|---|---|
| Envelope and the "should this be JSON?" rule | `Support\Utils\ApiErrorResponse` |
| Domain exceptions, status from each domain's map | `Support\Exceptions\DomainExceptionRenderer` |
| `ValidationException` (first message per field), authentication and framework HTTP exceptions (404, 403, 429, ...) | `Support\Exceptions\ApiExceptionRenderer` |
| Registration | `->withExceptions()` in `bootstrap/app.php` |

HTTP exceptions with a 5xx status are left to Laravel so debug pages and error reporting keep
working. Response headers such as `Retry-After` on a 429 are preserved.
