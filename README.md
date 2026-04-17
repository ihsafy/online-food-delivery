# 🍕 FoodExpress — Online Food Delivery System

A full-featured **Online Food Delivery System** built with **PHP** and **MySQL** — no frameworks used. This project includes customer ordering, session-based cart, dummy payment system, real-time rider chat, delivery tracking, and a complete admin dashboard.

Built as a university/college project to demonstrate full-stack web development using core PHP, MySQL, HTML, CSS, and session management.

---

## 🌟 Features

### 👤 Customer Panel
- Browse food menu with search functionality
- Add items to session-based shopping cart
- Update quantity, remove items, clear cart
- Enter delivery address during checkout
- Place orders and make dummy payments (Visa Card / bKash / Nagad)
- Track order status with visual progress tracker (Pending → Paid → Accepted → Picked Up → On the Way → Delivered)
- View order history with status filters
- Chat with assigned delivery rider

### 🏍️ Rider Panel
- View assigned deliveries with priority sorting
- Accept orders and update delivery status step-by-step
- Chat with customers using quick reply suggestions
- View delivery address and order items
- Track earnings and delivery statistics

### 🔑 Admin (Owner) Panel
- System-wide dashboard with revenue, orders, users, and payment statistics
- Order status distribution and payment method charts (CSS-based)
- Add, edit, delete, and toggle food item availability
- View all orders with customer, rider, payment, and delivery address info
- Assign or reassign riders to orders
- Search and filter food items

### 🔒 Security Features
- Password hashing with `password_hash()` and `password_verify()`
- SQL injection prevention using prepared statements (`mysqli_prepare`)
- Session-based authentication with role-based access control
- Session timeout after 30 minutes of inactivity
- Session regeneration on login to prevent session fixation
- XSS prevention with `htmlspecialchars()` output escaping

---

## 🛠️ Tech Stack

| Technology | Usage |
|---|---|
| **PHP** | Backend logic, session management, authentication |
| **MySQL** | Database — users, food, orders, payments, messages |
| **HTML5** | Page structure and semantic markup |
| **CSS3** | Styling, animations, responsive design |
| **XAMPP** | Local development server (Apache + MySQL) |

> ⚠️ **No JavaScript, no frameworks, no external libraries** — pure PHP + MySQL + HTML + CSS only.

---

## 📁 Project Structure

online-food-delivery/ ├── config/ │ └── db.php ├── assets/css/ │ ├── style.css │ ├── auth.css │ └── dashboard.css ├── includes/ │ ├── header.php │ ├── footer.php │ └── auth_check.php ├── auth/ │ ├── login.php │ └── register.php ├── customer/ │ ├── dashboard.php │ ├── menu.php │ ├── cart.php │ ├── order.php │ ├── orders.php │ ├── payment.php │ └── chat.php ├── rider/ │ ├── dashboard.php │ ├── deliveries.php │ └── chat.php ├── admin/ │ ├── dashboard.php │ ├── manage_food.php │ ├── add_food.php │ ├── edit_food.php │ ├── delete_food.php │ └── orders.php ├── index.php ├── logout.php └── README.md



---

## 🗄️ Database Schema

| Table | Description |
|---|---|
| `users` | All user accounts (customer, rider, owner) |
| `food` | Food menu items with name, price, description, status |
| `orders` | Customer orders with status, total, delivery address |
| `order_items` | Individual food items in each order |
| `payments` | Payment records (Visa Card, bKash, Nagad) |
| `messages` | Chat messages between customer and rider |

### ER Relationships

users (1) ──→ (N) orders [customer places orders] users (1) ──→ (N) orders [rider delivers orders] orders (1) ──→ (N) order_items [order contains items] food (1) ──→ (N) order_items [food appears in orders] orders (1) ──→ (1) payments [order has one payment] orders (1) ──→ (N) messages [order has chat messages] users (1) ──→ (N) messages [user sends messages]



---

## 🚀 Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) installed (Apache + MySQL)

### Steps

1. **Clone the repository**
```bash
git clone https://github.com/YOUR_USERNAME/online-food-delivery.git
Move to XAMPP htdocs


Copy the folder to: C:\xampp\htdocs\online-food-delivery\
Start XAMPP
Open XAMPP Control Panel
Start Apache and MySQL
Create Database
Open http://localhost/phpmyadmin
Click SQL tab
Paste and run the SQL below
Visit the website


http://localhost/online-food-delivery/
Database SQL
sql


CREATE DATABASE online_food_delivery;
USE online_food_delivery;
CREATE TABLE users ( id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, role ENUM('customer', 'rider', 'owner') NOT NULL DEFAULT 'customer', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ) ENGINE=InnoDB;
CREATE TABLE food ( id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, price DECIMAL(10,2) NOT NULL, description TEXT, status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ) ENGINE=InnoDB;
CREATE TABLE orders ( id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, rider_id INT DEFAULT NULL, status ENUM('Pending', 'Paid', 'Accepted', 'Picked Up', 'On the Way', 'Delivered') NOT NULL DEFAULT 'Pending', total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00, delivery_address TEXT NOT NULL DEFAULT '', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE SET NULL ) ENGINE=InnoDB;
CREATE TABLE order_items ( id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, food_id INT NOT NULL, quantity INT NOT NULL DEFAULT 1, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE, FOREIGN KEY (food_id) REFERENCES food(id) ON DELETE CASCADE ) ENGINE=InnoDB;
CREATE TABLE payments ( id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, payment_method ENUM('Visa Card', 'bKash', 'Nagad') NOT NULL, transaction_id VARCHAR(100) NOT NULL, payment_status ENUM('Pending', 'Completed') NOT NULL DEFAULT 'Completed', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ) ENGINE=InnoDB;
CREATE TABLE messages ( id INT AUTO_INCREMENT PRIMARY KEY, sender_id INT NOT NULL, receiver_id INT NOT NULL, order_id INT NOT NULL, message TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ) ENGINE=InnoDB;
INSERT INTO users (name, email, password, role) VALUES ('Admin Owner', 'admin@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'owner'), ('Rider One', 'rider@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'rider'), ('Rider Two', 'rider2@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'rider'), ('John Customer', 'customer@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'customer');
INSERT INTO food (name, price, description, status) VALUES ('Chicken Burger', 150.00, 'Juicy grilled chicken patty with fresh lettuce and mayo sauce.', 'available'), ('Beef Pizza', 350.00, 'Wood-fired pizza with premium beef and mozzarella cheese.', 'available'), ('Pasta Carbonara', 280.00, 'Creamy Italian pasta with crispy bacon and parmesan.', 'available'), ('Veggie Wrap', 120.00, 'Healthy whole wheat wrap with fresh vegetables and hummus.', 'available'), ('Fried Rice', 180.00, 'Special fried rice with egg, vegetables, and spices.', 'available'), ('Fish and Chips', 250.00, 'Crispy golden-battered fish with thick-cut fries.', 'available'), ('Chocolate Cake', 200.00, 'Rich chocolate cake with dark chocolate ganache.', 'available'), ('Mango Smoothie', 100.00, 'Fresh mango blended with yogurt and honey.', 'available'), ('Grilled Sandwich', 130.00, 'Toasted sandwich with grilled chicken and cheese.', 'available'), ('Chicken Biryani', 220.00, 'Aromatic basmati rice with tender chicken and spices.', 'available'), ('Caesar Salad', 160.00, 'Romaine lettuce with caesar dressing and grilled chicken.', 'available'), ('Ice Cream Sundae', 140.00, 'Premium ice cream with chocolate sauce and whipped cream.', 'available');



---

## 🔑 Demo Credentials

| Role | Email | Password |
|---|---|---|
| **Admin** | admin@food.com | admin123 |
| **Rider 1** | rider@food.com | rider123 |
| **Rider 2** | rider2@food.com | rider123 |
| **Customer** | customer@food.com | customer123 |

---

## 🔄 Order Flow

Customer Admin Rider │ │ │ ├── Browse Menu │ │ ├── Add to Cart │ │ ├── Enter Address │ │ ├── Place Order ──────────→│ │ │ (Status: Pending) │ │ ├── Make Payment │ │ │ (Status: Paid) ──────→│── Assign Rider ────────→│ │ │ ├── Accept Order │ │ │ (Status: Accepted) │←── Chat ─────────────────────────────────────────→│ │ │ ├── Pick Up Food │ │ │ (Status: Picked Up) │ │ ├── On the Way │ │ │ (Status: On the Way) │ │ ├── Deliver │ (Status: Delivered) ←──────────────────────────│ └── Order Complete ✅ │ │



---

## 💡 Key Design Decisions

- **No JavaScript** — All interactions use PHP form submissions and page reloads
- **No Images** — All visuals use CSS styling and HTML emoji characters
- **No Frameworks** — Pure PHP, no Laravel/CodeIgniter/Bootstrap
- **Session-based Cart** — Cart stored in `$_SESSION` array, not in database
- **Dummy Payments** — All payments succeed regardless of input (for demonstration)
- **Auto-refresh Chat** — Uses `<meta http-equiv="refresh">` for message updates every 15 seconds
- **Role-based Access** — Automatic folder-based role detection in `auth_check.php`

---

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to fork and submit a pull request.

---

## ⭐ Show Your Support

Give a ⭐ if this project helped you!

---

**Built with ❤️ using PHP & MySQL**