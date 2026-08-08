# 🍕 FoodExpress — Online Food Delivery System

A full-featured **Online Food Delivery System** built with **Core PHP** and **MySQL**, without using any frameworks or external libraries.

The system includes customer ordering, a session-based shopping cart, a dummy payment system, rider-to-customer chat, delivery tracking, and a complete admin/owner dashboard.

FoodExpress was developed as a **university/college project** to demonstrate full-stack web development using core PHP, MySQL, HTML5, CSS3, and PHP session management.

---

## 🌟 Features

### 👤 Customer Panel

* Browse the food menu with search functionality
* Add food items to a session-based shopping cart
* Update item quantities
* Remove individual items from the cart
* Clear the entire cart
* Enter a delivery address during checkout
* Place food orders
* Make dummy payments using:

  * Visa Card
  * bKash
  * Nagad
* Track orders using a visual status progress tracker:

  * Pending
  * Paid
  * Accepted
  * Picked Up
  * On the Way
  * Delivered
* View complete order history
* Filter orders by status
* Chat with the assigned delivery rider

### 🏍️ Rider Panel

* View assigned deliveries
* Sort deliveries by priority
* Accept assigned orders
* Update delivery status step-by-step
* View customer delivery addresses
* View ordered food items
* Chat with customers
* Use quick-reply suggestions when chatting
* View delivery earnings and statistics

### 🔑 Admin / Owner Panel

* View a system-wide dashboard
* Monitor:

  * Total revenue
  * Total orders
  * Total users
  * Payment statistics
* View order status distribution
* View payment method statistics
* Manage food items:

  * Add food
  * Edit food
  * Delete food
  * Enable/disable food availability
* View all orders
* View customer information
* View assigned rider information
* View payment information
* View delivery addresses
* Assign riders to orders
* Reassign riders when necessary
* Search and filter food items

### 🔒 Security Features

* Password hashing using `password_hash()`
* Password verification using `password_verify()`
* SQL injection protection using prepared statements
* Session-based authentication
* Role-based access control
* Automatic session timeout after 30 minutes of inactivity
* Session regeneration after login to help prevent session fixation
* XSS protection using `htmlspecialchars()` when displaying user-controlled data

> **Important:** This project is intended for educational/demo purposes. The payment system is simulated and does not process real financial transactions.

---

## 🛠️ Tech Stack

| Technology | Usage                                                     |
| ---------- | --------------------------------------------------------- |
| **PHP**    | Backend logic, authentication, sessions, order processing |
| **MySQL**  | Database for users, food, orders, payments, and messages  |
| **HTML5**  | Page structure and semantic markup                        |
| **CSS3**   | Styling, animations, responsive design, and dashboards    |
| **XAMPP**  | Local development server using Apache and MySQL           |

> ⚠️ **No JavaScript, no frameworks, and no external libraries** — the project uses pure PHP, MySQL, HTML, and CSS.

---

## 📁 Project Structure

```text
online-food-delivery/
│
├── config/
│   └── db.php
│
├── assets/
│   └── css/
│       ├── style.css
│       ├── auth.css
│       └── dashboard.css
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── auth_check.php
│
├── auth/
│   ├── login.php
│   └── register.php
│
├── customer/
│   ├── dashboard.php
│   ├── menu.php
│   ├── cart.php
│   ├── order.php
│   ├── orders.php
│   ├── payment.php
│   └── chat.php
│
├── rider/
│   ├── dashboard.php
│   ├── deliveries.php
│   └── chat.php
│
├── admin/
│   ├── dashboard.php
│   ├── manage_food.php
│   ├── add_food.php
│   ├── edit_food.php
│   ├── delete_food.php
│   └── orders.php
│
├── index.php
├── logout.php
└── README.md
```

---

## 🗄️ Database Schema

FoodExpress uses a MySQL database named `online_food_delivery`.

### Database Tables

| Table         | Description                                                      |
| ------------- | ---------------------------------------------------------------- |
| `users`       | Stores customer, rider, and owner accounts                       |
| `food`        | Stores food menu items, prices, descriptions, and availability   |
| `orders`      | Stores customer orders, delivery information, status, and totals |
| `order_items` | Stores individual food items belonging to each order             |
| `payments`    | Stores payment method and transaction information                |
| `messages`    | Stores chat messages between customers and riders                |

### 🔗 Entity Relationships

```text
users (1) ──────────────→ (N) orders
   │                         │
   │                         ├── customer_id
   │                         └── rider_id
   │
   ├──────────────────────→ (N) messages
   │
   └──────────────────────→ (N) messages
                             
orders (1) ──────────────→ (N) order_items
   │                         │
   │                         └── food_id
   │
   ├──────────────────────→ (1) payments
   │
   └──────────────────────→ (N) messages

food (1) ────────────────→ (N) order_items
```

### Relationship Summary

* One user can place many orders as a customer.
* One rider can deliver many orders.
* One order can contain multiple order items.
* One food item can appear in multiple order items.
* One order has one payment record.
* One order can contain multiple chat messages.
* Users can send and receive multiple messages.

---

# 🚀 Installation & Setup

## Prerequisites

Before installing FoodExpress, make sure you have:

* [XAMPP](https://www.apachefriends.org/) installed
* Apache enabled
* MySQL enabled
* A web browser such as Chrome, Firefox, or Edge

---

## 1. Clone the Repository

Open Git Bash, Command Prompt, or a terminal and run:

```bash
git clone https://github.com/YOUR_USERNAME/online-food-delivery.git
```

Replace `YOUR_USERNAME` with your actual GitHub username.

---

## 2. Move the Project to XAMPP

Copy the project folder into the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\online-food-delivery\
```

The final structure should look like:

```text
C:\xampp\htdocs\online-food-delivery\
```

---

## 3. Start XAMPP

Open **XAMPP Control Panel**.

Start the following services:

```text
Apache
MySQL
```

Both services should show as running.

---

## 4. Create the Database

Open phpMyAdmin in your browser:

```text
http://localhost/phpmyadmin
```

Then:

1. Click the **SQL** tab.
2. Copy the SQL script provided below.
3. Paste it into the SQL editor.
4. Click **Go** / **Execute**.
5. Make sure the `online_food_delivery` database and all tables are created successfully.

---

# 🗄️ Database SQL

Run the following SQL in phpMyAdmin:

```sql
CREATE DATABASE IF NOT EXISTS online_food_delivery
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE online_food_delivery;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'rider', 'owner') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE food (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    rider_id INT DEFAULT NULL,
    status ENUM(
        'Pending',
        'Paid',
        'Accepted',
        'Picked Up',
        'On the Way',
        'Delivered'
    ) NOT NULL DEFAULT 'Pending',
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    delivery_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_orders_rider
        FOREIGN KEY (rider_id)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_items_food
        FOREIGN KEY (food_id)
        REFERENCES food(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('Visa Card', 'bKash', 'Nagad') NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    payment_status ENUM('Pending', 'Completed') NOT NULL DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    order_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_messages_sender
        FOREIGN KEY (sender_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_messages_receiver
        FOREIGN KEY (receiver_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_messages_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Demo users
INSERT INTO users (name, email, password, role)
VALUES
(
    'Admin Owner',
    'admin@food.com',
    '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy',
    'owner'
),
(
    'Rider One',
    'rider@food.com',
    '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy',
    'rider'
),
(
    'Rider Two',
    'rider2@food.com',
    '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy',
    'rider'
),
(
    'John Customer',
    'customer@food.com',
    '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy',
    'customer'
);

-- Demo food items
INSERT INTO food (name, price, description, status)
VALUES
(
    'Chicken Burger',
    150.00,
    'Juicy grilled chicken patty with fresh lettuce and mayo sauce.',
    'available'
),
(
    'Beef Pizza',
    350.00,
    'Wood-fired pizza with premium beef and mozzarella cheese.',
    'available'
),
(
    'Pasta Carbonara',
    280.00,
    'Creamy Italian pasta with crispy bacon and parmesan.',
    'available'
),
(
    'Veggie Wrap',
    120.00,
    'Healthy whole wheat wrap with fresh vegetables and hummus.',
    'available'
),
(
    'Fried Rice',
    180.00,
    'Special fried rice with egg, vegetables, and spices.',
    'available'
),
(
    'Fish and Chips',
    250.00,
    'Crispy golden-battered fish with thick-cut fries.',
    'available'
),
(
    'Chocolate Cake',
    200.00,
    'Rich chocolate cake with dark chocolate ganache.',
    'available'
),
(
    'Mango Smoothie',
    100.00,
    'Fresh mango blended with yogurt and honey.',
    'available'
),
(
    'Grilled Sandwich',
    130.00,
    'Toasted sandwich with grilled chicken and cheese.',
    'available'
),
(
    'Chicken Biryani',
    220.00,
    'Aromatic basmati rice with tender chicken and spices.',
    'available'
),
(
    'Caesar Salad',
    160.00,
    'Romaine lettuce with Caesar dressing and grilled chicken.',
    'available'
),
(
    'Ice Cream Sundae',
    140.00,
    'Premium ice cream with chocolate sauce and whipped cream.',
    'available'
);
```

> **Note:** The demo password hashes above should correspond to the demo passwords listed in the credentials section. If you change the demo passwords, generate new hashes using PHP's `password_hash()` function rather than storing plain-text passwords.

---

## 5. Configure the Database Connection

Open:

```text
config/db.php
```

Make sure the database configuration matches your local XAMPP setup.

A typical local configuration is:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "online_food_delivery";
```

If your MySQL installation uses a password for the `root` account, update the `$password` value accordingly.

---

## 6. Open the Website

After Apache and MySQL are running, open:

```text
http://localhost/online-food-delivery/
```

The FoodExpress homepage should now load.

---

# 🔑 Demo Credentials

Use the following accounts to test the different panels:

| Role              | Email               | Password      |
| ----------------- | ------------------- | ------------- |
| **Admin / Owner** | `admin@food.com`    | `admin123`    |
| **Rider 1**       | `rider@food.com`    | `rider123`    |
| **Rider 2**       | `rider2@food.com`   | `rider123`    |
| **Customer**      | `customer@food.com` | `customer123` |

> ⚠️ These credentials are intended only for local development and demonstration. Do not use them in a production deployment.

---

# 🔄 Order Flow

The overall order process works as follows:

```text
Customer                    Admin                     Rider
   │                          │                         │
   │── Browse Menu            │                         │
   │                          │                         │
   │── Add Food to Cart       │                         │
   │                          │                         │
   │── Enter Address          │                         │
   │                          │                         │
   │── Place Order ──────────→│                         │
   │                          │                         │
   │   Status: Pending        │                         │
   │                          │                         │
   │── Make Payment ─────────→│                         │
   │   Status: Paid           │                         │
   │                          │                         │
   │                          │── Assign Rider ────────→│
   │                          │                         │
   │                          │                    Accept Order
   │                          │                         │
   │                          │                 Status: Accepted
   │                          │                         │
   │←─────────────── Chat ─────────────────────────────→│
   │                          │                         │
   │                          │                    Pick Up Food
   │                          │                         │
   │                          │                 Status: Picked Up
   │                          │                         │
   │                          │                    On the Way
   │                          │                         │
   │                          │                 Status: On the Way
   │                          │                         │
   │                          │                    Deliver Order
   │←───────────────────────────────────────────────────│
   │                                                  │
   │             Status: Delivered                    │
   │                                                  │
   └────────────── Order Complete ✅ ─────────────────┘
```

---

# 📊 Order Status Lifecycle

An order progresses through the following stages:

```text
Pending
   ↓
Paid
   ↓
Accepted
   ↓
Picked Up
   ↓
On the Way
   ↓
Delivered
```

### Status Description

| Status         | Description                                              |
| -------------- | -------------------------------------------------------- |
| **Pending**    | Order has been placed but payment has not been completed |
| **Paid**       | Customer has completed the dummy payment                 |
| **Accepted**   | Rider has accepted the assigned delivery                 |
| **Picked Up**  | Rider has picked up the customer's food                  |
| **On the Way** | Rider is travelling to the customer's address            |
| **Delivered**  | Food has been delivered successfully                     |

---

# 💡 Key Design Decisions

### 1. No JavaScript

The application does not depend on JavaScript.

All interactions are handled using PHP forms, server-side processing, sessions, and page reloads.

### 2. No External Frameworks

The project uses Core PHP instead of frameworks such as:

* Laravel
* CodeIgniter
* Symfony

The frontend also does not use Bootstrap or other CSS frameworks.

### 3. No External Libraries

The application is designed to run using the standard PHP, MySQL, HTML, and CSS stack.

### 4. Session-Based Shopping Cart

The shopping cart is stored inside the PHP session:

```php
$_SESSION['cart']
```

The cart does not require a separate database table.

### 5. Dummy Payment System

The payment system is designed for demonstration purposes.

It supports:

* Visa Card
* bKash
* Nagad

No real financial transaction is performed.

### 6. Automatic Chat Refresh

The chat system does not use JavaScript or WebSockets.

Instead, the page can use:

```html
<meta http-equiv="refresh" content="15">
```

to refresh the conversation automatically every 15 seconds.

### 7. Role-Based Access Control

The application uses session information to determine whether the logged-in user is:

* Customer
* Rider
* Owner

Access to protected pages is controlled through the authentication logic in:

```text
includes/auth_check.php
```

### 8. Password Security

Passwords are stored as secure password hashes rather than plain-text passwords.

PHP's built-in functions are used:

```php
password_hash()
password_verify()
```

### 9. SQL Injection Protection

Database queries should use prepared statements instead of directly inserting user input into SQL queries.

For example:

```php
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, password, role
     FROM users
     WHERE email = ?"
);
```

### 10. XSS Protection

User-generated content should be escaped before being displayed in HTML:

```php
echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
```

---

# 🔐 Security Considerations

FoodExpress implements several basic security practices suitable for a university project:

* Password hashing
* Password verification
* Prepared SQL statements
* Session authentication
* Role-based authorization
* Session timeout
* Session regeneration after login
* HTML output escaping

For a real production system, additional protections would be recommended, including:

* CSRF tokens
* HTTPS
* Secure and HttpOnly cookies
* SameSite cookie configuration
* Rate limiting
* Stronger authorization checks
* Input validation
* Production-grade payment gateway integration
* Secure secret management
* Database backups
* Audit logging
* Server-side security configuration

---

# 🧪 Testing the Application

A simple testing flow is:

### Customer

1. Log in using the customer account.
2. Browse the food menu.
3. Add food items to the cart.
4. Change quantities.
5. Proceed to checkout.
6. Enter a delivery address.
7. Place an order.
8. Complete the dummy payment.
9. View the order status.
10. Chat with the assigned rider.

### Admin / Owner

1. Log in using the admin account.
2. Open the admin dashboard.
3. Review orders and statistics.
4. Manage food items.
5. Assign a rider to an order.
6. Monitor payment and delivery information.

### Rider

1. Log in using a rider account.
2. View assigned deliveries.
3. Accept an assigned order.
4. Update the order status.
5. Chat with the customer.
6. Mark the order as delivered.

---

# 📌 Important Notes

* This project is intended for **educational and demonstration purposes**.
* The payment system is a **dummy payment system** and does not process real payments.
* The rider chat is implemented without WebSockets or JavaScript.
* The shopping cart is stored in the PHP session.
* The project is designed to run locally using XAMPP.
* For production deployment, additional security, validation, logging, and payment infrastructure should be implemented.

---

# 📝 License

This project is open source and available under the **MIT License**.

See the [`LICENSE`](LICENSE) file for the complete license text.

---

# 🤝 Contributing

Contributions, issues, and feature requests are welcome!

To contribute:

1. Fork the repository.
2. Create a new branch.
3. Make your changes.
4. Test the application.
5. Commit your changes.
6. Push the branch to your fork.
7. Submit a pull request.

---

# ⭐ Show Your Support

If this project helped you with your university or college project, please consider giving the repository a ⭐ on GitHub.

---

## ❤️ Built With

**PHP + MySQL + HTML5 + CSS3**

Built with ❤️ as a university/college full-stack web development project.
