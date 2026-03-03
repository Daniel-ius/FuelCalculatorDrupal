
# Fuel Calculator

## Features

-  Calculate fuel consumption based on distance, efficiency, and price
-  REST API with full CRUD operations (GET, POST, PATCH, DELETE)
-  Custom FuelCalculation entity with field storage
-  Views integration with exposed filters for calculation history
-  Web form interface for manual calculations

## Installation
### Manual Installation

1. Download module to `modules/custom/fuel_calculator/`
2. Enable: **Extend** → Check "Fuel Calculator" → **Install**
3. Or via Drush: `drush en fuel_calculator -y`

## Configuration

### Permissions

Go to **People → Permissions** and assign:

- `access fuel calculator api` - Access REST API
- `create fuel calculation entities` - Create calculations
- `view fuel calculation entities` - View calculations
- `edit fuel calculation entities` - Edit calculations
- `delete fuel calculation entities` - Delete calculations

## API Reference

### List all calculations

GET /api/v1/fuel-calculations?_format=json
Authorization: Basic {base64(username:password)}

### Create calculation

POST /api/v1/fuel-calculations?_format=json
Content-Type: application/json
Authorization: Basic {base64(username:password)}

{
"distance": 150.5,
"efficiency": 7.5,
"price": 1.45
}

**Response (201 Created):**

{
"id": 1,
"uuid": "abc-def-ghi",
"distance": 150.5,
"efficiency": 7.5,
"price": 1.45,
"fuel_spent": 11.29,
"fuel_cost": 16.37,
"user_id": 2,
"ip_address": "127.0.0.1",
"created": 1733832000
}

## Usage

### Web Interface

Navigate to `/fuel-calculator`:

1. Enter **Distance** (km)
2. Enter **Fuel Efficiency** (L/100km)
3. Enter **Fuel Price** (€/L)
4. Click **Calculate**

**Example:**

Distance: 150.5 km
Efficiency: 7.5 L/100km
Price: 1.45 €/L

Results:
→ Fuel Spent: 11.29 L
→ Total Cost: 16.37 €

### Url Parameters

`/fuel-calculator?distance=250&efficiency=7.5&price=1.45`

distance
efficiency
price

### View History

`/fuel-calculator/history`


### Settings for default values

`/admin/config/system/fuel-calculator`


### Run test in docker

`docker exec -it fuelcalc-php vendor/bin/phpunit modules/custom/FuelCalculator/tests/src/Unit`
