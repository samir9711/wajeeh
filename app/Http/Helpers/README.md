# SalonIdHelper

## Overview

The `SalonIdHelper` is a singleton helper class that automatically fetches and stores the salon_id for authenticated Owner or Professional users during the request lifecycle. This allows you to access the salon_id anywhere in your code without re-querying the database.

## How It Works

1. When a request is processed, the `SetSalonIdForRequest` middleware automatically determines the salon_id based on the following priority:
   - First priority: From the `salon_id` parameter in the request (if provided)
   - Second priority: From the authenticated owner's first salon
   - Third priority: From the authenticated professional's salon_id
   - Fourth priority: From the request's salon_id parameter if the user is authenticated as admin
2. The salon_id is stored in the `SalonIdHelper` singleton, making it available throughout the request lifecycle.
3. You can access the salon_id from anywhere in your code using `SalonIdHelper::get()`.
4. Models that extend `BaseModel` automatically have their salon_id set from `SalonIdHelper` when creating or updating records.

## Implementation Details

### Components

1. **SalonIdHelper**: A singleton class that stores and provides access to the salon_id.
2. **SetSalonIdForRequest**: A middleware that sets the salon_id after successful authentication.
3. **SalonIdServiceProvider**: A service provider that registers the middleware and the helper.

### How Salon ID is Determined

- For **Owner** users: The salon_id is fetched from the first salon associated with the owner.
- For **Professional** users: The salon_id is determined by finding the salon associated with the professional's tenant.

## Usage

### Getting the Salon ID

```php
use App\Helpers\SalonIdHelper;

// Get the salon ID for the current request
$salonId = SalonIdHelper::get();

// Check if salon ID is set
if (SalonIdHelper::isSet()) {
    // Do something with the salon ID
}
```

### Example in a Controller

```php
public function example(Request $request)
{
    // Get the salon ID for the current request
    $salonId = SalonIdHelper::get();

    if (!$salonId) {
        return response()->json([
            'message' => 'No salon found for this user'
        ], 404);
    }

    // Use the salon ID to fetch salon data
    $salon = Salon::find($salonId);

    return response()->json([
        'message' => 'Salon found',
        'salon' => $salon
    ]);
}
```

## Additional Methods

- `SalonIdHelper::set($salonId)`: Manually set the salon ID (rarely needed as the middleware handles this).
- `SalonIdHelper::isSet()`: Check if the salon ID is set.
- `SalonIdHelper::clear()`: Clear the salon ID (automatically called after each request).

## Automatic Salon ID Setting in Models

Models that extend `BaseModel` automatically have their `salon_id` set from `SalonIdHelper` when:

1. Creating a new record
2. Updating an existing record

This happens in the `boot` method of `BaseModel`:

```php
protected static function boot()
{
    parent::boot();

    // Set salon_id from SalonIdHelper when creating a new record
    static::creating(function ($model) {
        if (Schema::hasColumn($model->getTable(), 'salon_id')) {
            // Always set salon_id from SalonIdHelper if it's set
            if (SalonIdHelper::isSet()) {
                $model->salon_id = SalonIdHelper::get();
            }
        }
    });

    // Set salon_id from SalonIdHelper when updating an existing record
    static::updating(function ($model) {
        if (Schema::hasColumn($model->getTable(), 'salon_id')) {
            // Always set salon_id from SalonIdHelper if it's set
            if (SalonIdHelper::isSet()) {
                $model->salon_id = SalonIdHelper::get();
            }
        }
    });
}
```

This ensures that `salon_id` is always set from `SalonIdHelper` for models that have a `salon_id` column.

## Notes

- The salon_id is automatically set for authenticated requests, so you don't need to worry about setting it manually.
- The salon_id is only available for the current request lifecycle and will be reset for each new request.
- If a user is not authenticated or doesn't have an associated salon, `SalonIdHelper::get()` will return null.
