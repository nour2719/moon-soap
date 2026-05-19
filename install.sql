-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS soap_store;
USE soap_store;

-- جدول المستخدمين
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المنتجات
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100),
    skin_type VARCHAR(100),
    ingredients TEXT,
    image VARCHAR(255),
    stock INT DEFAULT 10,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الطلبات
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100),
    payment_method VARCHAR(50),
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- جدول تفاصيل الطلب
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    product_name VARCHAR(255),
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- جدول المراجعات والتقييمات
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_name VARCHAR(100),
    user_email VARCHAR(100),
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- إضافة مستخدم Admin (كلمة المرور: admin123)
INSERT INTO users (username, email, password, full_name, is_admin) 
VALUES ('admin', 'admin@soapstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير المتجر', TRUE);

-- إضافة منتجات تجريبية
INSERT INTO products (name, description, price, category, skin_type, ingredients, image, featured, stock) VALUES
('صابونة اللافندر الطبيعي', 'صابونة طبيعية برائحة اللافندر المهدئة، مناسبة للبشرة الجافة', 45.00, 'طبيعي', 'للبشرة الجافة', 'زيت اللافندر، زيت جوز الهند، زيت الزيتون', 'lavender.jpg', 1, 10),
('صابونة الفحم النشط', 'صابونة لتنقية البشرة الدهنية وإزالة الشوائب', 55.00, 'عضوي', 'للبشرة الدهنية', 'الفحم النشط، زيت شجرة الشاي، زيت جوز الهند', 'charcoal.jpg', 1, 15),
('صابونة العسل والشوفان', 'صابونة مغذية للبشرة الحساسة مع تقشير لطيف', 50.00, 'طبيعي', 'للبشرة الحساسة', 'عسل طبيعي، شوفان، زيت اللوز', 'honey-oats.jpg', 0, 8),
('صابونة الورد الفاخر', 'صابونة برائحة الورد الفاخرة، تهدئ البشرة وترطبها', 65.00, 'فاخر', 'لجميع أنواع البشرة', 'ماء الورد، زيت الأركان، جلسرين طبيعي', 'rose.jpg', 1, 12),
('صابونة النعناع المنعش', 'صابونة منعشة للبشرة الدهنية، تزيل اللمعان', 40.00, 'طبيعي', 'للبشرة الدهنية', 'زيت النعناع، زيت الجوجوبا، طين أخضر', 'mint.jpg', 0, 20),
('صابونة الحليب والعسل', 'صابونة مغذية للبشرة الجافة، تترك البشرة ناعمة', 48.00, 'عضوي', 'للبشرة الجافة', 'حليب الماعز، عسل، زبدة الشيا', 'milk-honey.jpg', 0, 7),
('صابونة الكركم للتفتيح', 'صابونة طبيعية لتفتيح البشرة وتوحيد لونها', 58.00, 'طبيعي', 'لجميع أنواع البشرة', 'كركم، زيت الزيتون، عسل', 'turmeric.jpg', 1, 9),
('صابونة البحر الميت', 'صابونة غنية بالمعادن لتنقية البشرة بعمق', 70.00, 'فاخر', 'للبشرة الدهنية', 'ملح البحر الميت، طين البحر الميت، زيت الأفوكادو', 'dead-sea.jpg', 0, 5);