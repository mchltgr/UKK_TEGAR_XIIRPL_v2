-- Import lewat phpMyAdmin (http://localhost/phpmyadmin) -> tab Import
CREATE DATABASE IF NOT EXISTS peminjaman_alat CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE peminjaman_alat;

DROP TABLE IF EXISTS log_aktivitas, pengembalian, peminjaman, alat, kategori, users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','petugas','peminjam') NOT NULL DEFAULT 'peminjam',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE alat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kategori_id INT NOT NULL,
  nama_alat VARCHAR(150) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  kondisi ENUM('baik','rusak') NOT NULL DEFAULT 'baik',
  keterangan TEXT,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  alat_id INT NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  tgl_pinjam DATE NOT NULL,
  tgl_kembali DATE NOT NULL COMMENT 'rencana tanggal kembali',
  status ENUM('menunggu','disetujui','ditolak','dikembalikan') NOT NULL DEFAULT 'menunggu',
  petugas_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE pengembalian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id INT NOT NULL UNIQUE,
  tgl_dikembalikan DATE NOT NULL,
  kondisi ENUM('baik','rusak','hilang') NOT NULL DEFAULT 'baik',
  denda INT NOT NULL DEFAULT 0,
  FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE log_aktivitas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  aktivitas VARCHAR(255) NOT NULL,
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun contoh: admin / admin123, petugas / petugas123, peminjam / peminjam123
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin',    '$2b$12$E0r4X3vYXfZk9KTwzsx4/.vQdunM9i1VGUNVqXz4UaY/dDC8FuA5C', 'admin'),
('Petugas Gudang','petugas',  '$2b$12$bgDbvdsYhKGPZt2qtkhWmennUFq1FHUWLfqRQWhtPxPCwFsU/1try', 'petugas'),
('Budi Peminjam', 'peminjam', '$2b$12$ommBepc3/cGJw9f2qQYjuOUQlQ5Kzaf9.4sRtXjEqRhbs/RQSxZFa', 'peminjam');

INSERT INTO kategori (nama_kategori) VALUES ('Perkakas Tangan'),('Alat Ukur'),('Elektronik'),('Multimedia');

INSERT INTO alat (kategori_id, nama_alat, stok, kondisi, keterangan) VALUES
(1,'Obeng Set',10,'baik','Obeng plus dan minus lengkap'),
(1,'Tang Kombinasi',6,'baik',''),
(2,'Multimeter Digital',4,'baik','Untuk ukur tegangan, arus, hambatan'),
(2,'Jangka Sorong',5,'baik',''),
(3,'Solder Listrik',5,'baik','40 Watt'),
(4,'Proyektor',2,'baik','Resolusi XGA');
