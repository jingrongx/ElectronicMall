-- 插入示例分类
INSERT INTO categories (name, description) VALUES 
('实验室设备', '各类实验室常用设备和仪器'),
('测量仪器', '精密测量和检测仪器'),
('分析仪器', '化学分析和物理分析仪器'),
('医疗设备', '医疗诊断和治疗设备');

-- 插入示例商品
INSERT INTO products (name, description, price, stock, image, category_id) VALUES 
('高精度电子天平', '0.001g精度，最大称量620g，带防风罩，RS232接口', 4999.00, 10, '/assets/images/products/balance.jpg', 1),
('数字示波器', '100MHz带宽，4通道，大屏显示，USB接口', 3299.00, 5, '/assets/images/products/oscilloscope.jpg', 2),
('紫外分光光度计', '190-1100nm波长范围，带扫描功能，高精度', 12999.00, 3, '/assets/images/products/spectrophotometer.jpg', 3),
('生化分析仪', '全自动，60测试/小时，触摸屏操作', 29999.00, 2, '/assets/images/products/analyzer.jpg', 4),
('精密pH计', '±0.01pH精度，自动温补，防水设计', 899.00, 15, '/assets/images/products/ph-meter.jpg', 2),
('超净工作台', '双人单面，99.99%过滤效率，低噪音设计', 7999.00, 4, '/assets/images/products/clean-bench.jpg', 1),
('电热恒温水浴锅', '数显控温，4孔，RT+5~100℃', 1299.00, 8, '/assets/images/products/water-bath.jpg', 1),
('荧光显微镜', '40X-1000X放大，LED照明，USB相机', 15999.00, 3, '/assets/images/products/microscope.jpg', 3);