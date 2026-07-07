# Laravel Smart Task API Specification

Dokumen ini berisi spesifikasi lengkap API untuk project **Laravel Smart Task**. Semua endpoint API menggunakan format data JSON untuk request dan response.

---

## 📌 Informasi Umum

- **Base URL:** `/api`
- **Headers Wajib:**
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Autentikasi:** Menggunakan token bearer dari Laravel Sanctum (`Authorization: Bearer <token>`) pada endpoint yang membutuhkan autentikasi (Protected).

---

## 🔑 Autentikasi & Akun

### 1. Registrasi User Baru
Mendaftarkan akun baru ke sistem.

- **URL:** `/api/register`
- **Method:** `POST`
- **Autentikasi:** Tidak (Public)
- **Request Body (JSON):**
  | Parameter | Tipe | Aturan Validasi | Deskripsi |
  | :--- | :--- | :--- | :--- |
  | `name` | String | `required`, `max:255` | Nama lengkap user. |
  | `email` | String | `required`, `email`, `max:255`, `unique:users,email` | Alamat email unik. |
  | `password` | String | `required`, `min:8` | Kata sandi minimal 8 karakter. |
  | `role` | String | `required`, `max:255` | Role user (contoh: `developer`, `manager`, `admin`). |

- **Response:**
  - **Status:** `201 Created`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Registrasi berhasil, silakan login.",
      "access_token": "1|laravel_sanctum_token_string...",
      "token_type": "Bearer Token",
      "user": {
        "id": 2,
        "name": "John Doe",
        "email": "johndoe@example.com",
        "role": "developer",
        "created_at": "2026-07-06T14:00:00.000000Z",
        "updated_at": "2026-07-06T14:00:00.000000Z"
      }
    }
    ```

---

### 2. Login User
Melakukan autentikasi untuk mendapatkan token.

- **URL:** `/api/login`
- **Method:** `POST`
- **Autentikasi:** Tidak (Public)
- **Request Body (JSON):**
  | Parameter | Tipe | Aturan Validasi | Deskripsi |
  | :--- | :--- | :--- | :--- |
  | `email` | String | `required`, `email`, `exists:users,email` | Email terdaftar. |
  | `password` | String | `required`, `min:6` | Password akun. |

- **Response (Sukses):**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Login berhasil, silakan simpan token Anda.",
      "access_token": "1|laravel_sanctum_token_string...",
      "token_type": "Bearer",
      "user": {
        "id": 1,
        "name": "Developer CostumeRent",
        "email": "developer@costumerent.com",
        "role": "developer",
        "created_at": "2026-07-06T07:13:40.000000Z",
        "updated_at": "2026-07-06T07:13:40.000000Z"
      }
    }
    ```

- **Response (Gagal - Kredensial Salah):**
  - **Status:** `401 Unauthorized`
  - **Body:**
    ```json
    {
      "success": false,
      "message": "Email atau password salah.",
      "access_token": null,
      "token_type": null,
      "user": null
    }
    ```

---

### 3. Logout User
Menghapus current access token yang sedang digunakan.

- **URL:** `/api/logout`
- **Method:** `POST`
- **Autentikasi:** Ya (Protected)

- **Response:**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Logout berhasil, token telah dihapus."
    }
    ```

---

### 4. Detail User Terautentikasi (Dua Pilihan Endpoint)

#### Opsi A: Detail Profil (Me)
- **URL:** `/api/me`
- **Method:** `GET`
- **Autentikasi:** Ya (Protected)
- **Response:**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Data user berhasil diambil",
      "data": {
        "id": 1,
        "name": "Developer CostumeRent",
        "email": "developer@costumerent.com",
        "role": "developer",
        "created_at": "2026-07-06T07:13:40.000000Z",
        "updated_at": "2026-07-06T07:13:40.000000Z"
      }
    }
    ```

#### Opsi B: Root API User
- **URL:** `/api`
- **Method:** `GET`
- **Autentikasi:** Ya (Protected)
- **Response:**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "id": 1,
      "name": "Developer CostumeRent",
      "email": "developer@costumerent.com",
      "role": "developer",
      "created_at": "2026-07-06T07:13:40.000000Z",
      "updated_at": "2026-07-06T07:13:40.000000Z"
    }
    ```

---

## 📁 Manajemen Project (Projects)

Semua endpoint Project di bawah ini dilindungi oleh middleware `auth:sanctum` dan hanya mengizinkan akses ke data yang dibuat oleh user bersangkutan (`user_id` mencocokkan ID user yang sedang login).

### 1. List Projects
Mendapatkan semua project milik user yang sedang aktif.

- **URL:** `/api/projects`
- **Method:** `GET`
- **Autentikasi:** Ya (Protected)
- **Response:**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Data project berhasil diambil",
      "data": [
        {
          "id": 1,
          "user_id": 1,
          "name": "CostumeRent Platform",
          "description": "Aplikasi persewaan kostum berbasis IoT dan otomasi pembayaran.",
          "status": "active",
          "created_at": "2026-07-06T07:13:40.000000Z",
          "updated_at": "2026-07-06T07:13:40.000000Z"
        }
      ]
    }
    ```

---

### 2. Create Project
Membuat project baru. `user_id` otomatis diisi dengan ID user yang sedang aktif.

- **URL:** `/api/projects`
- **Method:** `POST`
- **Autentikasi:** Ya (Protected)
- **Request Body (JSON):**
  | Parameter | Tipe | Aturan Validasi | Deskripsi |
  | :--- | :--- | :--- | :--- |
  | `name` | String | `required`, `max:255` | Nama project. |
  | `description` | String | `required`, `max:255` | Deskripsi singkat project. |
  | `status` | String | `required`, `in:active,archived,completed` | Status project. |

- **Response:**
  - **Status:** `201 Created`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Data project berhasil ditambahkan",
      "data": {
        "id": 2,
        "user_id": 1,
        "name": "Smart Office System",
        "description": "Pengembangan otomasi smart office.",
        "status": "active",
        "created_at": "2026-07-06T14:10:00.000000Z",
        "updated_at": "2026-07-06T14:10:00.000000Z"
      }
    }
    ```

---

### 3. Detail Project
Mengambil informasi detail project tertentu berdasarkan ID.

- **URL:** `/api/projects/{id}`
- **Method:** `GET`
- **Autentikasi:** Ya (Protected)

- **Response (Sukses):**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Data project berhasil diambil",
      "data": {
        "id": 1,
        "user_id": 1,
        "name": "CostumeRent Platform",
        "description": "Aplikasi persewaan kostum berbasis IoT.",
        "status": "active",
        "created_at": "2026-07-06T07:13:40.000000Z",
        "updated_at": "2026-07-06T07:13:40.000000Z"
      }
    }
    ```

- **Response (Gagal - Bukan project milik user):**
  - **Status:** `403 Forbidden`
  - **Body:**
    ```json
    {
      "success": false,
      "message": "Anda tidak memiliki akses untuk melihat project ini"
    }
    ```

---

### 4. Update Project
Memperbarui informasi project tertentu berdasarkan ID.

- **URL:** `/api/projects/{id}`
- **Method:** `PUT` / `PATCH`
- **Autentikasi:** Ya (Protected)
- **Request Body (JSON):**
  | Parameter | Tipe | Aturan Validasi | Deskripsi |
  | :--- | :--- | :--- | :--- |
  | `name` | String | `sometimes`, `required`, `max:255` | Nama project baru. |
  | `description` | String | `nullable` | Deskripsi project baru (dapat dikosongkan). |
  | `status` | String | `required`, `in:active,archived,completed` | Status project baru. |

- **Response (Sukses):**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Data project berhasil diupdate",
      "data": {
        "id": 1,
        "user_id": 1,
        "name": "CostumeRent Platform v2",
        "description": "Deskripsi terupdate.",
        "status": "completed",
        "created_at": "2026-07-06T07:13:40.000000Z",
        "updated_at": "2026-07-06T14:15:00.000000Z"
      }
    }
    ```

- **Response (Gagal - Bukan project milik user):**
  - **Status:** `403 Forbidden`
  - **Body:**
    ```json
    {
      "success": false,
      "message": "Anda tidak memiliki akses untuk mengedit project ini"
    }
    ```

---

### 5. Delete Project
Menghapus project tertentu beserta relasi datanya (cascade).

- **URL:** `/api/projects/{id}`
- **Method:** `DELETE`
- **Autentikasi:** Ya (Protected)

- **Response (Sukses):**
  - **Status:** `200 OK`
  - **Body:**
    ```json
    {
      "success": true,
      "message": "Data project berhasil dihapus",
      "data": true
    }
    ```

- **Response (Gagal - Bukan project milik user):**
  - **Status:** `403 Forbidden`
  - **Body:**
    ```json
    {
      "success": false,
      "message": "Anda tidak memiliki akses untuk mengedit project ini"
    }
    ```

---

## 📋 Manajemen Tugas (Tasks)

Semua endpoint Task di bawah ini dilindungi oleh middleware `auth:sanctum` dan hanya dapat diakses oleh pembuat task bersangkutan (`user_id` mencocokkan ID user yang sedang login).

> 💡 **Catatan Penting:** Pada Controller Task di project ini, semua endpoint respon sukses (GET, POST, PUT, DELETE) mengembalikan status HTTP code **`201 Created`** secara konsisten.

### 1. List Tasks
Mendapatkan semua task milik user yang sedang aktif.

- **URL:** `/api/tasks`
- **Method:** `GET`
- **Autentikasi:** Ya (Protected)
- **Response:**
  - **Status:** `201 Created`
  - **Body:**
    ```json
    {
      "status": true,
      "message": "Data berhasil ditampilkan",
      "data": [
        {
          "id": 1,
          "project_id": 1,
          "user_id": 1,
          "title": "Analisis Kebutuhan Sistem & ERD CostumeRent",
          "description": "Deskripsi pengerjaan untuk modul: Analisis Kebutuhan Sistem & ERD CostumeRent",
          "status": "todo",
          "priority": "high",
          "due_date": "2026-07-08T07:13:40.000000Z",
          "estimate_hours": 4,
          "assigned_to": 1,
          "created_at": "2026-07-06T07:13:40.000000Z",
          "updated_at": "2026-07-06T07:13:40.000000Z"
        }
      ]
    }
    ```

---

### 2. Create Task
Membuat task baru di bawah project tertentu.

- **URL:** `/api/tasks`
- **Method:** `POST`
- **Autentikasi:** Ya (Protected)
- **Request Body (JSON):**
  | Parameter | Tipe | Aturan Validasi | Deskripsi |
  | :--- | :--- | :--- | :--- |
  | `project_id` | Integer | `required`, `exists:projects,id` | ID project yang sudah ada. |
  | `title` | String | `required`, `max:255` | Judul/nama task. |
  | `description`| String | `nullable` | Keterangan/deskripsi task. |
  | `status` | String | `required`, `in:todo,in_progress,review,done,blocked` | Status pengerjaan task. |
  | `priority` | String | `required`, `in:low,medium,high,urgent` | Tingkat prioritas task. |
  | `due_date` | Date/Time| `required`, `date` | Batas waktu penyelesaian task (format: YYYY-MM-DD). |
  | `estimate_hours`| Integer | `required`, `integer` | Estimasi durasi pengerjaan dalam jam. |
  | `assigned_to`| Integer | `nullable`, `exists:users,id` | ID user penerima tugas (dapat dikosongkan). |

- **Response:**
  - **Status:** `201 Created`
  - **Body:**
    ```json
    {
      "status": true,
      "message": "Data berhasil ditambahkan",
      "data": {
        "id": 11,
        "project_id": 1,
        "user_id": 1,
        "title": "Fitur Otentikasi Dua Faktor (2FA)",
        "description": "Menambahkan lapisan keamanan 2FA menggunakan OTP email.",
        "status": "todo",
        "priority": "medium",
        "due_date": "2026-07-15",
        "estimate_hours": 8,
        "assigned_to": 1,
        "created_at": "2026-07-06T14:20:00.000000Z",
        "updated_at": "2026-07-06T14:20:00.000000Z"
      }
    }
    ```

---

### 3. Detail Task
Mengambil informasi detail task tertentu berdasarkan ID.

- **URL:** `/api/tasks/{id}`
- **Method:** `GET`
- **Autentikasi:** Ya (Protected)

- **Response (Sukses):**
  - **Status:** `201 Created`
  - **Body:**
    ```json
    {
      "status": true,
      "message": "Data berhasil ditampilkan",
      "data": {
        "id": 1,
        "project_id": 1,
        "user_id": 1,
        "title": "Analisis Kebutuhan Sistem & ERD CostumeRent",
        "description": "Deskripsi pengerjaan untuk modul: Analisis Kebutuhan Sistem & ERD CostumeRent",
        "status": "todo",
        "priority": "high",
        "due_date": "2026-07-08T07:13:40.000000Z",
        "estimate_hours": 4,
        "assigned_to": 1,
        "created_at": "2026-07-06T07:13:40.000000Z",
        "updated_at": "2026-07-06T07:13:40.000000Z"
      }
    }
    ```

- **Response (Gagal - Bukan task milik user):**
  - **Status:** `403 Forbidden`
  - **Body:**
    ```json
    {
      "status": false,
      "message": "Anda tidak memiliki akses untuk melihat ini"
    }
    ```

---

### 4. Update Task
Memperbarui informasi task tertentu berdasarkan ID.

- **URL:** `/api/tasks/{id}`
- **Method:** `PUT` / `PATCH`
- **Autentikasi:** Ya (Protected)
- **Request Body (JSON):**
  *(Menerima parameter yang sama dengan request Create Task)*
  | Parameter | Tipe | Aturan Validasi | Deskripsi |
  | :--- | :--- | :--- | :--- |
  | `project_id` | Integer | `required`, `exists:projects,id` | ID project terkait. |
  | `title` | String | `required`, `max:255` | Judul/nama task. |
  | `description`| String | `nullable` | Deskripsi task. |
  | `status` | String | `required`, `in:todo,in_progress,review,done,blocked` | Status pengerjaan task. |
  | `priority` | String | `required`, `in:low,medium,high,urgent` | Prioritas task. |
  | `due_date` | Date/Time| `required`, `date` | Batas waktu penyelesaian. |
  | `estimate_hours`| Integer | `required`, `integer` | Estimasi durasi pengerjaan. |
  | `assigned_to`| Integer | `nullable`, `exists:users,id` | ID user penerima tugas. |

- **Response (Sukses):**
  - **Status:** `201 Created`
  - **Body:**
    ```json
    {
      "status": true,
      "message": "Data berhasil diupdate",
      "data": {
        "id": 1,
        "project_id": 1,
        "user_id": 1,
        "title": "Analisis Kebutuhan Sistem & ERD CostumeRent - Final",
        "description": "Deskripsi terupdate.",
        "status": "in_progress",
        "priority": "high",
        "due_date": "2026-07-08",
        "estimate_hours": 6,
        "assigned_to": 1,
        "created_at": "2026-07-06T07:13:40.000000Z",
        "updated_at": "2026-07-06T14:30:00.000000Z"
      }
    }
    ```

- **Response (Gagal - Bukan task milik user):**
  - **Status:** `403 Forbidden`
  - **Body:**
    ```json
    {
      "status": false,
      "message": "Anda tidak memiliki akses untuk mengubah ini"
    }
    ```

---

### 5. Delete Task
Menghapus task tertentu berdasarkan ID.

- **URL:** `/api/tasks/{id}`
- **Method:** `DELETE`
- **Autentikasi:** Ya (Protected)

- **Response (Sukses):**
  - **Status:** `201 Created`
  - **Body:**
    ```json
    {
      "status": true,
      "message": "Data berhasil dihapus",
      "data": {
        "id": 1,
        "project_id": 1,
        "user_id": 1,
        "title": "Analisis Kebutuhan Sistem & ERD CostumeRent",
        "description": "Deskripsi pengerjaan untuk modul: Analisis Kebutuhan Sistem & ERD CostumeRent",
        "status": "todo",
        "priority": "high",
        "due_date": "2026-07-08T07:13:40.000000Z",
        "estimate_hours": 4,
        "assigned_to": 1,
        "created_at": "2026-07-06T07:13:40.000000Z",
        "updated_at": "2026-07-06T07:13:40.000000Z"
      }
    }
    ```

- **Response (Gagal - Bukan task milik user):**
  - **Status:** `403 Forbidden`
  - **Body:**
    ```json
    {
      "status": false,
      "message": "Anda tidak memiliki akses untuk menghapus ini"
    }
    ```
