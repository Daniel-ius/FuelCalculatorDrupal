
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

### Enable REST Endpoints

- drush config:set rest.settings bc_entity.fuel_calculation.GET.json.basic_auth.enabled true -y
- drush config:set rest.settings bc_entity.fuel_calculation.POST.json.basic_auth.enabled true -y
- drush cr


## API Reference

### List all calculations

GET /api/v1/fuel-calculations?_format=json
Authorization: Basic {base64(username:password)}

### Get single calculation

GET /api/v1/fuel-calculations/{id}?_format=json
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


### Update calculation

PATCH /api/v1/fuel-calculations/{id}?_format=json
Content-Type: application/json
Authorization: Basic {base64(username:password)}

{
"price": 1.60
}


### Delete calculation

DELETE /api/v1/fuel-calculations/{id}?_format=json
Authorization: Basic {base64(username:password)}


**Response (200 OK):**

{
"message": "Deleted"
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


### View History

Navigate to `/fuel-calculation-history` to see all calculations with date filters.

### cURL Examples

**Create calculation:**

curl -X POST "https://example.com/api/v1/fuel-calculations?_format=json"
-H "Content-Type: application/json"
-H "Authorization: Basic $(echo -n 'user:pass' | base64)"
-d '{
"distance": 200,
"efficiency": 8.0,
"price": 1.50
}'

**Get all calculations:**

curl -X GET "https://example.com/api/v1/fuel-calculations?_format=json"
-H "Authorization: Basic $(echo -n 'user:pass' | base64)"


**Update calculation:**

curl -X PATCH "https://example.com/api/v1/fuel-calculations/1?_format=json"
-H "Content-Type: application/json"
-H "Authorization: Basic $(echo -n 'user:pass' | base64)"
-d '{"price": 1.65}'
