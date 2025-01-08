-- 修改数据库字符集
ALTER DATABASE equipment_mall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 先删除所有表
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

-- 重新创建表
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入示例数据
INSERT INTO categories (name, description) VALUES 
('实验室设备', '各类实验室常用设备和仪器'),
('测量仪器', '精密测量和检测仪器'),
('分析仪器', '化学分析和物理分析仪器'),
('医疗设备', '医疗诊断和治疗设备');

INSERT INTO products (name, description, price, stock, image, category_id) VALUES 
('高精度电子天平', '0.001g精度，最大称量620g，带防风罩，RS232接口', 4999.00, 10, '/assets/images/products/balance.jpg', 1),
('数字示波器', '100MHz带宽，4通道，大屏显示，USB接口', 3299.00, 5, '/assets/images/products/oscilloscope.jpg', 2),
('紫外分光光度计', '190-1100nm波长范围，带扫描功能，高精度', 12999.00, 3, '/assets/images/products/spectrophotometer.jpg', 3),
('生化分析仪', '全自动，60测试/小时，触摸屏操作', 29999.00, 2, '/assets/images/products/analyzer.jpg', 4),
('精密pH计', '±0.01pH精度，自动温补，防水设计', 899.00, 15, '/assets/images/products/ph-meter.jpg', 2),
('超净工作台', '双人单面，99.99%过滤效率，低噪音设计', 7999.00, 4, '/assets/images/products/clean-bench.jpg', 1),
('电热恒温水浴锅', '数显控温，4孔，RT+5~100℃', 1299.00, 8, '/assets/images/products/water-bath.jpg', 1),
('荧光显微镜', '40X-1000X放大，LED照明，USB相机', 15999.00, 3, '/assets/images/products/microscope.jpg', 3);

-- 插入管理员账号
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); 