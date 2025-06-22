# Claude Instructions

This file contains instructions for Claude AI when working with this project.

## Project Overview
This is a PHP API boilerplate demo project made by my teacher following MVC architecture with Docker setup. You should keep this setup but only change the code from boilerplate to the sauna booking, following the same structure and patterns as this demo code or examples below.

## Common Commands
- Start the project: `docker compose up`
- Stop the project: `docker compose down`
- Run composer commands: `docker compose run php composer [arguments]`

## File Structure Conventions
- Models: app/models/
- Controllers: app/controllers/
- Services: app/services/
- Entry point: app/public/index.php

## Database
- MySQL/MariaDB
- Main tables: articles, users

## Authentication
- JWT-based authentication
- Routes: /auth/register, /auth/login, /auth/me

## Important Files
- app/public/index.php - Main entry point with routes
- controllers/ArticleController.php - Article CRUD operations
- controllers/AuthController.php - Authentication
- models/ArticleModel.php - Database operations for articles
- models/User.php - User operations and validation

## Project Requirements
Remove the article code related (like models/ArticleModel.php etc..) in this project and make it for sauna booking system project for Amsterdam ID ApartHotel.

### Guest Interface (no login needed):
* Simple booking form with:
   * Guest name field
   * Date picker
   * Time picker (6am to 1am only)
   * Room number field
   * Number of people dropdown (max 4)
   * All bookings are 1 hour long
* Confirmation screen showing:
   * Booking details
   * Instructions: "1. Come to reception at your appointment time. 2. Towels and shower provided. 3. No alcohol or drugs allowed."

### Employee Interface (login required):
* Login page for staff
* Main dashboard with:
   * Big status card at top showing:
      * "Available" (green)
      * "Busy" (red) with guest name, room, time, and buttons to "Complete" or "Cancel" session
      * "Out of Order" (red) with reason
   * "Out of Order" button that asks for reason
   * Today's reservations list
   * Future reservations list with cancel option
* Employee booking form:
   * Same fields as guest but:
      * Time: any time (not limited to 6am-1am)
      * Duration: 1, 2, or 3 hours
      * People: up to 6

### Important Requirements:
* Booked time slots must not appear as available
* Use simple code following Web Development 2 lecture examples
* Follow MVC pattern for backend
* Use Vue components structure from lectures
* Use JWT authentication for employees
* Make functions short and clear
* Remove any complex or unnecessary code
* No AI-style comments
* Design should be simple and clear

## Code Patterns

### Vue Frontend Requirements:
* Use Vue 3 Options API (NOT Composition API)
* Structure: data(), methods, mounted() lifecycle
* Simple components with props and $emit
* Vue Router for navigation between guest/employee interfaces
* Axios for API calls using .then().catch() pattern
* Pinia store for JWT and user state
* localStorage for token persistence
* Load data in mounted() hooks
* Use v-for, v-if, v-model for templates

### PHP Backend Requirements:
* MVC pattern: Controllers, Services, Repositories
* RESTful endpoints:
   * POST /users/login (returns JWT)
   * GET /bookings (list all)
   * POST /bookings (create new)
   * PUT /bookings/{id} (update status)
   * DELETE /bookings/{id} (cancel)
* Use firebase/php-jwt for tokens
* Check Authorization header on employee routes
* Return JSON with proper status codes
* Composer autoloading with namespaces

### Vue Component Example:
```javascript
// Vue component structure
export default {
  name: 'BookingForm',
  data() {
    return {
      booking: {
        guestName: '',
        date: '',
        time: '',
        roomNumber: '',
        numberOfPeople: 1
      }
    }
  },
  methods: {
    submitBooking() {
      axios.post('/bookings', this.booking)
        .then(res => {
          // handle success
        })
        .catch(error => console.log(error))
    }
  },
  mounted() {
    // load any initial data
  }
}
```

## Implementation Steps

### Step 1: Project Setup & Structure
Instructions: Set up the basic project structure following Web Development 2 patterns. Use simple, clear folder organization.

Backend (PHP):
- /app
  - /controllers (BookingController.php, UserController.php)
  - /models (Booking.php, User.php)
  - /repositories (BookingRepository.php, UserRepository.php)
  - /services (BookingService.php, UserService.php)
  - composer.json (with autoloading)
  
Frontend (Vue):
- /src
  - /components (BookingForm.vue, BookingList.vue, etc.)
  - /stores (auth.js)
  - App.vue
  - main.js
  - axios-auth.js

Keep code simple. Use short variable names like from lectures (e.g., res, error, el).

#### Code patterns to follow:
```javascript
// From Lecture 1F - Vue component structure
export default {
  name: 'StockList',
  data() {
    return {
      stocks: [
        { name: "BMW", price: 61.05, previousPrice: 0, currency: "€" },
        { name: "Caterpillar", price: 146.49, previousPrice: 0, currency: "$" }
      ],
      portfolio: []
    };
  },
  methods: {
    updatePrices() {
      this.stocks.forEach((stock) => {
        stock.price += (Math.random() - 0.5) * 2;
        if (stock.price < 0) {
          stock.price = 0;
        }
      });
    }
  },
  mounted() {
    setInterval(() => {
      this.updatePrices();
    }, 1000);
  }
}
```

### Step 2: Backend Database & Models
Instructions: Create simple database tables and PHP models following MVC pattern from lectures.

```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50),
  password VARCHAR(255),
  role VARCHAR(20) DEFAULT 'employee'
);

CREATE TABLE bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  guest_name VARCHAR(100),
  room_number VARCHAR(20),
  date DATE,
  time TIME,
  duration INT DEFAULT 1,
  people INT,
  status VARCHAR(20) DEFAULT 'active',
  created_by VARCHAR(50) DEFAULT 'guest'
);

CREATE TABLE sauna_status (
  id INT PRIMARY KEY,
  status VARCHAR(20),
  reason TEXT,
  updated_at TIMESTAMP
);
```

Models should be simple classes with properties only.

#### Code patterns to follow:
```php
// From Lecture 3B-2 - MVC pattern
namespace App\Models;

class Product {
    public int $id;
    public string $name;
    public float $price;
    public int $category_id;
}

// Repository pattern
namespace App\Repositories;

class ProductRepository {
    public function getAll($offset = NULL, $limit = NULL) {
        // Simple database queries
    }
}
```

### Step 3: Backend API Endpoints
Instructions: Create REST endpoints exactly like lecture examples. Keep controller methods short.

```php
// Routes to implement:
POST   /users/login      // JWT login
GET    /bookings         // List all bookings
POST   /bookings         // Create booking
PUT    /bookings/{id}    // Update booking status
DELETE /bookings/{id}    // Cancel booking
GET    /sauna/status     // Get sauna status
PUT    /sauna/status     // Update sauna status
```

Each controller method should:
- Get data from request
- Call service method
- Return JSON response
- Use try-catch for errors

#### Code patterns to follow:
```php
// From Lecture 3B-2 - REST controller
public function getAll() {
    $offset = $_GET['offset'] ?? 0;
    $limit = $_GET['limit'] ?? 10;
    
    $products = $this->service->getAll($offset, $limit);
    $this->respond($products);
}

public function create() {
    $product = $this->createObjectFromPostedJson("App\\Models\\Product");
    $product = $this->service->insert($product);
    $this->respond($product);
}

public function update($id) {
    $product = $this->createObjectFromPostedJson("App\\Models\\Product");
    $product->id = $id;
    $this->service->update($product, $id);
    $this->respond($product);
}

public function delete($id) {
    $this->service->delete($id);
    $this->respond(true);
}
```

### Step 4: JWT Authentication
Instructions: Implement JWT exactly like Lecture 5B examples. Keep it simple.

```php
// In UserController login method:
// 1. Check username/password
// 2. Generate JWT with firebase/php-jwt
// 3. Return token

// In other controllers:
// 1. Check Authorization header
// 2. Decode JWT
// 3. Allow or deny access
```

Use same JWT structure from lectures with iss, aud, iat, exp claims.

#### Code patterns to follow:
```php
// From Lecture 5B - JWT generation
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

public function login() {
    $data = $this->createObjectFromPostedJson("App\\Models\\User");
    $user = $this->service->checkUsernamePassword($data->username, $data->password);
    
    if (!$user) {
        $this->respondWithError(401, "Invalid credentials");
        return;
    }
    
    $tokenResponse = $this->generateJwt($user);
    $this->respond($tokenResponse);
}

public function generateJwt($user) {
    $secret_key = "YOUR_SECRET_KEY";
    $issuer = "THE_ISSUER";
    $audience = "THE_AUDIENCE";
    $issuedAt = time();
    $notBefore = $issuedAt;
    $expire = $issuedAt + 600;
    
    $payload = [
        "iss" => $issuer,
        "aud" => $audience,
        "iat" => $issuedAt,
        "nbf" => $notBefore,
        "exp" => $expire,
        "data" => [
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email
        ]
    ];
    
    $jwt = JWT::encode($payload, $secret_key, 'HS256');
    return ["jwt" => $jwt];
}

// JWT validation
public function checkForJwt() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (!$authHeader) {
        $this->respondWithError(401, "No token provided");
        return false;
    }
    
    $jwt = str_replace('Bearer ', '', $authHeader);
    
    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        $this->respondWithError(401, "Invalid token");
        return false;
    }
}
```

### Step 5: Vue Guest Components
Instructions: Create guest interface components using Options API pattern from lectures.

Components needed:
- GuestBookingForm.vue - Form with date, time (6am-1am), room, people (max 4)
- BookingConfirmation.vue - Show booking details and instructions

Use:
- data() for form fields
- methods for submit
- mounted() for initial setup
- Simple v-model binding
- axios.post().then().catch() pattern 

#### Code patterns to follow:
```javascript
// From Lecture 4F - Form component with axios
export default {
  name: 'CreateProduct',
  data() {
    return {
      product: {
        name: '',
        price: 0,
        category_id: 1
      },
      categories: []
    };
  },
  methods: {
    createProduct() {
      axios.post("http://localhost/products", this.product)
        .then((res) => {
          this.$router.push("/products");
        })
        .catch((error) => console.log(error));
    }
  },
  mounted() {
    axios.get("http://localhost/categories")
      .then((res) => {
        this.categories = res.data;
      })
      .catch((error) => console.log(error));
  }
}

// Template pattern from lectures
<template>
  <table class="table">
    <tbody>
      <tr v-for="stock in stocks" :key="stock.name">
        <td>{{stock.name}}</td>
        <td>{{stock.currency}} {{stock.price}}</td>
      </tr>
    </tbody>
  </table>
</template>
```

### Step 6: Vue Employee Components
Instructions: Create employee interface following same patterns.

Components:
- EmployeeLogin.vue - Simple username/password form
- SaunaStatus.vue - Big status card (Available/Busy/Out of Order)
- EmployeeBookingForm.vue - Extended form (any time, 1-3 hours, 6 people)
- BookingsList.vue - Today's and future bookings

Keep methods short. Use props for data passing, $emit for events.

#### Code patterns to follow:
```javascript
// From Lecture 2F - Props and emit
export default {
  name: 'StockItem',
  props: {
    name: String,
    price: Number,
    someComplexObject: Object
  },
  data() {
    return {
      amount: 1
    }
  },
  methods: {
    buyStock() {
      this.$emit('buy', this.name, this.amount);
    }
  }
}

// Parent component handling emit
<StockItem :name="item.name" :price="item.price" @buy="buyItem" />

methods: {
  buyItem(name, amount) {
    alert(`Buying ${amount} of ${name}`);
  }
}
```

### Step 7: Vue Router Setup
Instructions: Configure routing exactly like Lecture 2 examples.

```javascript
// Routes:
/ - Guest booking form
/confirmation - Booking confirmation
/employee - Employee login
/employee/dashboard - Main employee view
```

Use createRouter, createWebHistory. Keep route definitions simple.

#### Code patterns to follow:
```javascript
// From Lecture 2F - Router setup
import { createRouter, createWebHistory } from 'vue-router'
import Home from './components/Home.vue';
import About from './components/About.vue';

const routes = [
  { path: '/', component: Home },
  { path: '/about', component: About },
];

const router = createRouter({
  history: createWebHistory(),
  routes
})

const app = createApp(App);
app.use(router);
app.mount('#app');

// In components
<router-link to="/">Go to Home</router-link>
<router-view></router-view>

// Programmatic navigation
this.$router.push("/products");
```

### Step 8: Pinia Store for Auth
Instructions: Create auth store following Lecture 6F patterns.

```javascript
// Store should have:
// - state: username, token
// - getters: loggedIn, getToken
// - actions: login(username, password), autoLogin()
```

Use localStorage for token. Configure axios instance with auth header.

#### Code patterns to follow:
```javascript
// From Lecture 6F - Pinia store
import { defineStore } from 'pinia'
import axios from '../axios-auth'

export const useStore = defineStore('store', {
  state: () => ({
    username: '',
    token: ''
  }),
  getters: {
    loggedIn: (state) => state.username != '',
    getToken: (state) => state.token
  },
  actions: {
    login(username, password) {
      return new Promise((resolve, reject) => {
        axios.post("/users/login", {
          username: username,
          password: password,
        })
        .then((res) => {
          this.username = res.data.username;
          this.token = res.data.jwt;
          axios.defaults.headers.common['Authorization'] = "Bearer " + res.data.jwt;
          localStorage.setItem('token', res.data.jwt);
          localStorage.setItem('username', res.data.username);
          resolve()
        })
        .catch((error) => reject(error));
      });
    },
    autoLogin() {
      const token = localStorage.getItem('token');
      const username = localStorage.getItem('username');
      if (token && username) {
        axios.defaults.headers.common["Authorization"] = "Bearer " + token;
        this.token = token;
        this.username = username;
      }
    }
  }
})
```

### Step 9: Connect Frontend to Backend
Instructions: Wire up all API calls using axios patterns from lectures.

For each component:
- Load data in mounted()
- Use axios instance with base URL
- Handle errors with console.log
- Update UI after successful calls

#### Code patterns to follow:
```javascript
// From Lecture 6F - axios-auth.js
import axios from 'axios'

const instance = axios.create({
  baseURL: 'http://localhost/'
});

export default instance;

// In components
import axios from '../axios-auth';

// Using the store in components
import { useStore } from '@/stores/store'

export default {
  setup() {
    const store = useStore();
    return { store }
  },
  methods: {
    login() {
      this.store.login(this.username, this.password)
        .then(() => {
          this.$router.replace("/products");
        })
        .catch((error) => {
          this.errorMessage = error;
        });
    }
  },
  created() {
    this.store.autoLogin();
  }
}
```

### Step 10: Business Logic Implementation
Instructions: Add validation to prevent double bookings. Keep logic simple.

Backend:
- Check if time slot is available before creating booking
- Return proper error if slot taken

Frontend:
- Disable taken time slots in picker
- Show clear error messages

#### Code patterns to follow:
```javascript
// From Lecture 4F - Update after delete
deleteProduct(id) {
  axios.delete(`http://localhost/products/${id}`)
    .then((res) => {
      this.loadProducts(); // Refresh the list
    })
    .catch((error) => console.log(error));
}

// Conditional display from Lecture 6F
<Login v-if="store.loggedIn" />
<ProductList v-if="store.loggedIn" />
```

Use these exact patterns. Keep methods short. Use simple variable names (res, error, el). No complex logic.

Remember: Follow lecture code patterns exactly. Keep functions short. No complex logic. Use simple variable names.