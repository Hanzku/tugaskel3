-- Pilih database aplikasi terlebih dahulu. Tidak mengisi data contoh atau mengubah tabel lama.
CREATE TABLE IF NOT EXISTS guru (
  id_guru VARCHAR(20) NOT NULL,
  nama_guru VARCHAR(100) NOT NULL,
  jenis_kelamin CHAR(1) NULL,
  kd_pelajaran VARCHAR(20) NULL,
  nip VARCHAR(18) NULL,
  nuptk VARCHAR(16) NULL,
  email VARCHAR(254) NULL,
  telepon VARCHAR(30) NULL,
  alamat VARCHAR(500) NULL,
  jabatan VARCHAR(100) NULL,
  kelas VARCHAR(100) NULL,
  keterangan VARCHAR(1000) NULL,
  PRIMARY KEY (id_guru),
  UNIQUE KEY uq_guru_nip (nip),
  UNIQUE KEY uq_guru_nuptk (nuptk),
  KEY idx_guru_nama (nama_guru),
  CONSTRAINT chk_guru_jenis_kelamin CHECK (jenis_kelamin IS NULL OR jenis_kelamin IN ('L', 'P')),
  CONSTRAINT fk_guru_pelajaran FOREIGN KEY (kd_pelajaran) REFERENCES pelajaran (kd_pelajaran) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;