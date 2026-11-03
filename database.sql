CREATE DATABASE IF NOT EXISTS db_peminjaman CHARACTER SET utf8mb4;
USE db_peminjaman;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,nama VARCHAR(100),username VARCHAR(50) UNIQUE,password VARCHAR(255),role ENUM('admin','petugas','peminjam'));
CREATE TABLE kategori(id INT AUTO_INCREMENT PRIMARY KEY,nama VARCHAR(100));
CREATE TABLE alat(id INT AUTO_INCREMENT PRIMARY KEY,nama VARCHAR(100),kategori_id INT,stok INT DEFAULT 0,FOREIGN KEY(kategori_id) REFERENCES kategori(id) ON DELETE SET NULL);
CREATE TABLE peminjaman(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,alat_id INT,jumlah INT,tgl_pinjam DATE,tgl_rencana_kembali DATE,status ENUM('menunggu','dipinjam','ditolak','dikembalikan') DEFAULT 'menunggu',FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(alat_id) REFERENCES alat(id) ON DELETE CASCADE);
CREATE TABLE pengembalian(id INT AUTO_INCREMENT PRIMARY KEY,peminjaman_id INT,tgl_kembali DATE,denda INT DEFAULT 0,FOREIGN KEY(peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE);
CREATE TABLE log_aktivitas(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,aktivitas VARCHAR(255),waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
-- semua password akun contoh = "password"
INSERT INTO users(nama,username,password,role) VALUES
('Administrator','admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
('Petugas Satu','petugas','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','petugas'),
('Peminjam Satu','peminjam','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','peminjam');
INSERT INTO kategori(nama) VALUES('Elektronik'),('Perkakas');
INSERT INTO alat(nama,kategori_id,stok) VALUES('Proyektor',1,3),('Laptop',1,5),('Bor Listrik',2,2);
