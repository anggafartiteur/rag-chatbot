-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 06:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rag_chatbot`
--

-- --------------------------------------------------------

--
-- Table structure for table `auto_ingest_schedule`
--

CREATE TABLE `auto_ingest_schedule` (
  `id` int(11) NOT NULL,
  `enabled` tinyint(1) DEFAULT 0,
  `interval_unit` enum('minutes','hours','days') DEFAULT 'hours',
  `interval_value` int(11) DEFAULT 24,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auto_ingest_schedule`
--

INSERT INTO `auto_ingest_schedule` (`id`, `enabled`, `interval_unit`, `interval_value`, `last_run`, `next_run`) VALUES
(1, 0, 'hours', 24, '2026-05-04 03:49:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bot_settings`
--

CREATE TABLE `bot_settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bot_settings`
--

INSERT INTO `bot_settings` (`key`, `value`, `updated_at`) VALUES
('bot_name', '', '2026-05-06 03:29:43'),
('business_info', '', '2026-05-06 03:29:43'),
('closing', 'Untuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.', '2026-05-06 03:29:43'),
('email_from_name', 'Kemindo Chatbot', '2026-05-06 02:43:01'),
('format', '', '2026-05-06 03:29:43'),
('language', '', '2026-05-06 03:29:43'),
('length', '', '2026-05-06 03:29:43'),
('out_of_topic', '', '2026-05-06 03:29:43'),
('persona', 'Ketika mengumpulkan informasi customer, tanyakan semua data berikut sekaligus dalam satu pesan: nama lengkap, nama perusahaan, nomor WhatsApp, dan alamat email, serta kebutuhan spesifik mereka.', '2026-05-06 03:29:43'),
('rate_limit_chat_max', '20', '2026-05-18 04:25:16'),
('rate_limit_chat_window', '10', '2026-05-18 04:25:16'),
('rate_limit_ingest_max', '5', '2026-05-18 04:25:16'),
('rate_limit_ingest_window', '10', '2026-05-18 04:25:16'),
('smtp_host', '', '2026-05-06 02:43:01'),
('smtp_pass', '', '2026-05-06 02:43:01'),
('smtp_port', '465', '2026-05-06 02:43:01'),
('smtp_secure', 'ssl', '2026-05-06 02:43:01'),
('smtp_user', 'chatbot@kemindogroup.com', '2026-05-06 02:43:01'),
('tone', '', '2026-05-06 03:29:43'),
('topic_limit', '', '2026-05-06 03:29:43'),
('unknown', 'honest', '2026-05-06 03:29:43');

-- --------------------------------------------------------

--
-- Table structure for table `email_recipients`
--

CREATE TABLE `email_recipients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_recipients`
--

INSERT INTO `email_recipients` (`id`, `name`, `email`, `active`, `created_at`) VALUES
(1, 'Awan', 'angga.setiawan@kemindogroup.com', 1, '2026-05-06 02:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `ingest_log`
--

CREATE TABLE `ingest_log` (
  `id` int(11) NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `chunks` int(11) DEFAULT 0,
  `status` enum('success','error') DEFAULT 'success',
  `message` text DEFAULT NULL,
  `ran_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingest_log`
--

INSERT INTO `ingest_log` (`id`, `source`, `chunks`, `status`, `message`, `ran_at`) VALUES
(1, 'Folder Knowledge', 8, 'success', NULL, '2026-05-04 01:42:34'),
(2, 'Folder Knowledge', 8, 'success', NULL, '2026-05-04 02:11:39'),
(3, 'Folder Knowledge', 8, 'success', NULL, '2026-05-04 02:16:20'),
(4, 'Folder Knowledge', 8, 'success', NULL, '2026-05-04 02:16:34'),
(5, 'Folder Knowledge', 8, 'success', NULL, '2026-05-04 02:17:10'),
(6, 'Folder Knowledge', 8, 'success', NULL, '2026-05-04 02:19:43'),
(7, 'Folder Knowledge', 21, 'success', NULL, '2026-05-04 03:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `ingest_sources`
--

CREATE TABLE `ingest_sources` (
  `id` int(11) NOT NULL,
  `type` enum('file','mysql','url') NOT NULL,
  `label` varchar(255) NOT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`config`)),
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingest_sources`
--

INSERT INTO `ingest_sources` (`id`, `type`, `label`, `config`, `enabled`, `created_at`) VALUES
(1, 'file', 'Folder Knowledge', '{\"path\": \"./knowledge\"}', 1, '2026-05-03 05:50:42');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `perusahaan` varchar(255) DEFAULT NULL,
  `intensi` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `raw_history` longtext DEFAULT NULL,
  `status` enum('new','contacted','closed') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `nama`, `whatsapp`, `email`, `perusahaan`, `intensi`, `summary`, `raw_history`, `status`, `created_at`, `updated_at`) VALUES
(3, NULL, NULL, NULL, NULL, NULL, '```json\n{\n  \"nama\": null,\n  \"whatsapp\": null,\n  \"email\": null,\n  \"perusahaan\": null,\n  \"intensi\": null,\n  \"summary\": null\n}\n```', '[{\"role\":\"user\",\"content\":\"halo\"},{\"role\":\"assistant\",\"content\":\"Halo! 👋 Selamat datang di Kemindo Group! \\n\\nSaya adalah asisten AI yang siap membantu Anda. Kami menyediakan berbagai produk berkualitas tinggi, antara lain:\\n\\n**Mining Chemicals & Consumables:**\\nActivated Carbon, Bentonite, Caustic Soda, Copper Sulphate, Hydrochloric Acid, Sulphuric Acid, dan banyak lagi.\\n\\n**Green Energy & Agriculture Products:**\\nPalm Kernel Shell (PKS), Coconut Shell, Palm Kernel Meal, Crude Palm Oil (CPO), dan produk lainnya.\\n\\n**Quicklime & Hydrated Lime:**\\nDiproduksi di pabrik kami di Sulawesi Utara.\\n\\n---\\n\\nAda yang bisa saya bantu? Apakah Anda mencari informasi tentang produk tertentu, atau ada pertanyaan spesifik mengenai kebutuhan bisnis Anda?\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"}]', 'new', '2026-05-06 09:05:54', '2026-05-06 09:05:54'),
(4, NULL, NULL, NULL, NULL, 'Purchase sulfuric acid (H₂SO₄)', '```json\n{\n  \"nama\": null,\n  \"whatsapp\": null,\n  \"email\": null,\n  \"perusahaan\": null,\n  \"intensi\": \"Purchase sulfuric acid (H₂SO₄)\",\n  \"summary\": null\n}\n```', '[{\"role\":\"user\",\"content\":\"halo\"},{\"role\":\"assistant\",\"content\":\"Halo! 👋 Selamat datang di Kemindo Group! \\n\\nSaya adalah asisten AI yang siap membantu Anda. Kami menyediakan berbagai produk berkualitas tinggi, antara lain:\\n\\n**Mining Chemicals & Consumables:**\\nActivated Carbon, Bentonite, Caustic Soda, Copper Sulphate, Hydrochloric Acid, Sulphuric Acid, dan banyak lagi.\\n\\n**Green Energy & Agriculture Products:**\\nPalm Kernel Shell (PKS), Coconut Shell, Palm Kernel Meal, Crude Palm Oil (CPO), dan produk lainnya.\\n\\n**Quicklime & Hydrated Lime:**\\nDiproduksi di pabrik kami di Sulawesi Utara.\\n\\n---\\n\\nAda yang bisa saya bantu? Apakah Anda mencari informasi tentang produk tertentu, atau ada pertanyaan spesifik mengenai kebutuhan bisnis Anda?\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya mau beli asam sulfat\"},{\"role\":\"assistant\",\"content\":\"Baik! 👍 Kami memiliki **Sulfuric Acid (H₂SO₄)** dengan kualitas tinggi yang cocok untuk berbagai industri.\\n\\n**Spesifikasi Produk:**\\n- **Purity:** Min 98.00%\\n- **Specific Gravity:** Min 1.84\\n- **Kemasan:** Drums, Intermediate Bulk Containers (IBCs), dan ISO Tank\\n- **Kategori Industri:** Chemical, Mining, Nickel, Gold Mine\\n\\nSulfuric Acid kami diproduksi dan dikemas di bawah standar kualitas ketat untuk memastikan keamanan dan efektivitas maksimal dalam aplikasi industri Anda.\\n\\n---\\n\\nUntuk melanjutkan proses dan mendiskusikan harga, ketersediaan, serta detail pemesanan Anda, saya perlu mengumpulkan informasi berikut:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa jumlah\\/volume yang Anda butuhkan?**\\n\\nInformasi ini akan diteruskan ke tim sales kami untuk penawaran terbaik. 😊\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya mau beli asam sulfat\"},{\"role\":\"assistant\",\"content\":\"Baik! 👍 Kami memiliki **Sulfuric Acid (H₂SO₄)** dengan kualitas tinggi yang cocok untuk berbagai industri.\\n\\n**Spesifikasi Produk:**\\n- **Purity:** Min 98.00%\\n- **Specific Gravity:** Min 1.84\\n- **Kemasan:** Drums, Intermediate Bulk Containers (IBCs), dan ISO Tank\\n- **Kategori Industri:** Chemical, Mining, Nickel, Gold Mine\\n\\nSulfuric Acid kami diproduksi dan dikemas di bawah standar kualitas ketat untuk memastikan keamanan dan efektivitas maksimal dalam aplikasi industri Anda.\\n\\n---\\n\\nUntuk melanjutkan proses dan mendiskusikan **harga, ketersediaan, serta detail pemesanan**, saya perlu informasi dari Anda:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa volume\\/jumlah yang Anda butuhkan?**\\n\\nInformasi ini akan diteruskan ke tim sales Kemindo untuk memberikan penawaran terbaik. 😊\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"}]', 'new', '2026-05-06 09:11:17', '2026-05-06 09:11:17'),
(5, 'Angga', '08989597867', 'anggafartiteur@gmail.com', 'PT Kemindo', 'Purchase 1 ton of Sulfuric Acid (H2SO4) with delivery required within 2 weeks', 'Angga from PT Kemindo is interested in purchasing 1 ton of Sulfuric Acid (H2SO4) with a delivery timeline of 2 weeks. He has provided contact details via WhatsApp (08989597867) and email (anggafartiteur@gmail.com). The sales team should reach out to discuss pricing, delivery logistics, and confirm specifications for this order.', '[{\"role\":\"user\",\"content\":\"halo\"},{\"role\":\"assistant\",\"content\":\"Halo! 👋 Selamat datang di Kemindo Group! \\n\\nSaya adalah asisten AI yang siap membantu Anda. Kami menyediakan berbagai produk berkualitas tinggi, antara lain:\\n\\n**Mining Chemicals & Consumables:**\\nActivated Carbon, Bentonite, Caustic Soda, Copper Sulphate, Hydrochloric Acid, Sulphuric Acid, dan banyak lagi.\\n\\n**Green Energy & Agriculture Products:**\\nPalm Kernel Shell (PKS), Coconut Shell, Palm Kernel Meal, Crude Palm Oil (CPO), dan produk lainnya.\\n\\n**Quicklime & Hydrated Lime:**\\nDiproduksi di pabrik kami di Sulawesi Utara.\\n\\n---\\n\\nAda yang bisa saya bantu? Apakah Anda mencari informasi tentang produk tertentu, atau ada pertanyaan spesifik mengenai kebutuhan bisnis Anda?\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya mau beli asam sulfat\"},{\"role\":\"assistant\",\"content\":\"Baik! 👍 Kami memiliki **Sulfuric Acid (H₂SO₄)** dengan kualitas tinggi yang cocok untuk berbagai industri.\\n\\n**Spesifikasi Produk:**\\n- **Purity:** Min 98.00%\\n- **Specific Gravity:** Min 1.84\\n- **Kemasan:** Drums, Intermediate Bulk Containers (IBCs), dan ISO Tank\\n- **Kategori Industri:** Chemical, Mining, Nickel, Gold Mine\\n\\nSulfuric Acid kami diproduksi dan dikemas di bawah standar kualitas ketat untuk memastikan keamanan dan efektivitas maksimal dalam aplikasi industri Anda.\\n\\n---\\n\\nUntuk melanjutkan proses dan mendiskusikan harga, ketersediaan, serta detail pemesanan Anda, saya perlu mengumpulkan informasi berikut:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa jumlah\\/volume yang Anda butuhkan?**\\n\\nInformasi ini akan diteruskan ke tim sales kami untuk penawaran terbaik. 😊\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya mau beli asam sulfat\"},{\"role\":\"assistant\",\"content\":\"Baik! 👍 Kami memiliki **Sulfuric Acid (H₂SO₄)** dengan kualitas tinggi yang cocok untuk berbagai industri.\\n\\n**Spesifikasi Produk:**\\n- **Purity:** Min 98.00%\\n- **Specific Gravity:** Min 1.84\\n- **Kemasan:** Drums, Intermediate Bulk Containers (IBCs), dan ISO Tank\\n- **Kategori Industri:** Chemical, Mining, Nickel, Gold Mine\\n\\nSulfuric Acid kami diproduksi dan dikemas di bawah standar kualitas ketat untuk memastikan keamanan dan efektivitas maksimal dalam aplikasi industri Anda.\\n\\n---\\n\\nUntuk melanjutkan proses dan mendiskusikan **harga, ketersediaan, serta detail pemesanan**, saya perlu informasi dari Anda:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa volume\\/jumlah yang Anda butuhkan?**\\n\\nInformasi ini akan diteruskan ke tim sales Kemindo untuk memberikan penawaran terbaik. 😊\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya angga dari pt kemindo mau beli asam sulfat 1 ton untuk dikirim 2 minggu kedepan, nomor wa 08989597867 email di anggafartiteur@gmail.com\"},{\"role\":\"assistant\",\"content\":\"Terima kasih atas informasinya, Angga! 😊\\n\\nSaya catat data Anda:\\n- **Nama:** Angga\\n- **Perusahaan:** PT Kemindo\\n- **Nomor WhatsApp:** 08989597867\\n- **Email:** anggafartiteur@gmail.com\\n- **Kebutuhan:** Sulfuric Acid (H₂SO₄) - 1 ton\\n- **Timeline Pengiriman:** 2 minggu ke depan\\n\\n---\\n\\n✅ **Informasi Anda sudah saya catat dan akan diteruskan ke tim sales Kemindo.**\\n\\nTim sales kami akan segera menghubungi Anda melalui WhatsApp atau email untuk:\\n- Konfirmasi harga dan penawaran terbaik\\n- Detail pengiriman dan logistik\\n- Spesifikasi teknis sesuai kebutuhan Anda\\n- Proses pemesanan dan pembayaran\\n\\n**Kontak Tim Sales Kemindo:**\\n- 📧 **Email Sales:** Sales@kemindogroup.com\\n- 📞 **Telepon:** +62-21-22510445 \\/ 22510446 \\/ 22510447\\n\\nTerima kasih telah memilih Kemindo Group! 🙏\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya angga dari pt kemindo mau beli asam sulfat 1 ton untuk dikirim 2 minggu kedepan, nomor wa 08989597867 email di anggafartiteur@gmail.com\"},{\"role\":\"assistant\",\"content\":\"Terima kasih atas informasinya, Angga! 😊\\n\\nSaya catat data Anda:\\n- **Nama:** Angga\\n- **Perusahaan:** PT Kemindo\\n- **Nomor WhatsApp:** 08989597867\\n- **Email:** anggafartiteur@gmail.com\\n- **Kebutuhan:** Sulfuric Acid (H₂SO₄) - 1 ton\\n- **Timeline Pengiriman:** 2 minggu ke depan\\n\\n---\\n\\n✅ **Informasi Anda sudah saya catat dan akan diteruskan ke tim sales Kemindo.**\\n\\nTim sales kami akan segera menghubungi Anda melalui WhatsApp atau email untuk:\\n- Konfirmasi harga dan penawaran terbaik\\n- Detail pengiriman dan logistik\\n- Spesifikasi teknis sesuai kebutuhan Anda\\n- Proses pemesanan dan pembayaran\\n\\n**Kontak Tim Sales Kemindo:**\\n- 📧 **Email Sales:** Sales@kemindogroup.com\\n- 📞 **Telepon:** +62-21-22510445 \\/ 22510446 \\/ 22510447\\n\\nTerima kasih telah memilih Kemindo Group! 🙏\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"}]', 'new', '2026-05-06 09:20:03', '2026-05-06 09:20:03'),
(6, 'Angga', '08989597867', 'anggafartiteur@gmail.com', 'PT Kemindo', 'Purchase 1 ton of Sulfuric Acid (H₂SO₄) with delivery required within 2 weeks', 'Angga from PT Kemindo is inquiring about purchasing 1 ton of Sulfuric Acid (H₂SO₄) with a delivery timeline of 2 weeks. Contact details: 08989597867 (WhatsApp) and anggafartiteur@gmail.com. Customer requires standard specifications and logistics coordination for timely delivery.', '[{\"role\":\"user\",\"content\":\"halo\"},{\"role\":\"assistant\",\"content\":\"Halo! 👋 Selamat datang di Kemindo Group! \\n\\nSaya adalah asisten AI yang siap membantu Anda. Kami menyediakan berbagai produk berkualitas tinggi, antara lain:\\n\\n**Mining Chemicals & Consumables:**\\nActivated Carbon, Bentonite, Caustic Soda, Copper Sulphate, Hydrochloric Acid, Sulphuric Acid, dan banyak lagi.\\n\\n**Green Energy & Agriculture Products:**\\nPalm Kernel Shell (PKS), Coconut Shell, Palm Kernel Meal, Crude Palm Oil (CPO), dan produk lainnya.\\n\\n**Quicklime & Hydrated Lime:**\\nDiproduksi di pabrik kami di Sulawesi Utara.\\n\\n---\\n\\nAda yang bisa saya bantu? Apakah Anda mencari informasi tentang produk tertentu, atau ada pertanyaan spesifik mengenai kebutuhan bisnis Anda?\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya mau beli asam sulfat\"},{\"role\":\"assistant\",\"content\":\"Baik! 👍 Kami memiliki **Sulfuric Acid (H₂SO₄)** dengan kualitas tinggi yang cocok untuk berbagai industri.\\n\\n**Spesifikasi Produk:**\\n- **Purity:** Min 98.00%\\n- **Specific Gravity:** Min 1.84\\n- **Kemasan:** Drums, Intermediate Bulk Containers (IBCs), dan ISO Tank\\n- **Kategori Industri:** Chemical, Mining, Nickel, Gold Mine\\n\\nSulfuric Acid kami diproduksi dan dikemas di bawah standar kualitas ketat untuk memastikan keamanan dan efektivitas maksimal dalam aplikasi industri Anda.\\n\\n---\\n\\nUntuk melanjutkan proses dan mendiskusikan harga, ketersediaan, serta detail pemesanan Anda, saya perlu mengumpulkan informasi berikut:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa jumlah\\/volume yang Anda butuhkan?**\\n\\nInformasi ini akan diteruskan ke tim sales kami untuk penawaran terbaik. 😊\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya mau beli asam sulfat\"},{\"role\":\"assistant\",\"content\":\"Baik! 👍 Kami memiliki **Sulfuric Acid (H₂SO₄)** dengan kualitas tinggi yang cocok untuk berbagai industri.\\n\\n**Spesifikasi Produk:**\\n- **Purity:** Min 98.00%\\n- **Specific Gravity:** Min 1.84\\n- **Kemasan:** Drums, Intermediate Bulk Containers (IBCs), dan ISO Tank\\n- **Kategori Industri:** Chemical, Mining, Nickel, Gold Mine\\n\\nSulfuric Acid kami diproduksi dan dikemas di bawah standar kualitas ketat untuk memastikan keamanan dan efektivitas maksimal dalam aplikasi industri Anda.\\n\\n---\\n\\nUntuk melanjutkan proses dan mendiskusikan **harga, ketersediaan, serta detail pemesanan**, saya perlu informasi dari Anda:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa volume\\/jumlah yang Anda butuhkan?**\\n\\nInformasi ini akan diteruskan ke tim sales Kemindo untuk memberikan penawaran terbaik. 😊\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya angga dari pt kemindo mau beli asam sulfat 1 ton untuk dikirim 2 minggu kedepan, nomor wa 08989597867 email di anggafartiteur@gmail.com\"},{\"role\":\"assistant\",\"content\":\"Terima kasih atas informasinya, Angga! 😊\\n\\nSaya catat data Anda:\\n- **Nama:** Angga\\n- **Perusahaan:** PT Kemindo\\n- **Nomor WhatsApp:** 08989597867\\n- **Email:** anggafartiteur@gmail.com\\n- **Kebutuhan:** Sulfuric Acid (H₂SO₄) - 1 ton\\n- **Timeline Pengiriman:** 2 minggu ke depan\\n\\n---\\n\\n✅ **Informasi Anda sudah saya catat dan akan diteruskan ke tim sales Kemindo.**\\n\\nTim sales kami akan segera menghubungi Anda melalui WhatsApp atau email untuk:\\n- Konfirmasi harga dan penawaran terbaik\\n- Detail pengiriman dan logistik\\n- Spesifikasi teknis sesuai kebutuhan Anda\\n- Proses pemesanan dan pembayaran\\n\\n**Kontak Tim Sales Kemindo:**\\n- 📧 **Email Sales:** Sales@kemindogroup.com\\n- 📞 **Telepon:** +62-21-22510445 \\/ 22510446 \\/ 22510447\\n\\nTerima kasih telah memilih Kemindo Group! 🙏\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"saya angga dari pt kemindo mau beli asam sulfat 1 ton untuk dikirim 2 minggu kedepan, nomor wa 08989597867 email di anggafartiteur@gmail.com\"},{\"role\":\"assistant\",\"content\":\"Terima kasih atas informasinya, Angga! 😊\\n\\nSaya catat data Anda:\\n- **Nama:** Angga\\n- **Perusahaan:** PT Kemindo\\n- **Nomor WhatsApp:** 08989597867\\n- **Email:** anggafartiteur@gmail.com\\n- **Kebutuhan:** Sulfuric Acid (H₂SO₄) - 1 ton\\n- **Timeline Pengiriman:** 2 minggu ke depan\\n\\n---\\n\\n✅ **Informasi Anda sudah saya catat dan akan diteruskan ke tim sales Kemindo.**\\n\\nTim sales kami akan segera menghubungi Anda melalui WhatsApp atau email untuk:\\n- Konfirmasi harga dan penawaran terbaik\\n- Detail pengiriman dan logistik\\n- Spesifikasi teknis sesuai kebutuhan Anda\\n- Proses pemesanan dan pembayaran\\n\\n**Kontak Tim Sales Kemindo:**\\n- 📧 **Email Sales:** Sales@kemindogroup.com\\n- 📞 **Telepon:** +62-21-22510445 \\/ 22510446 \\/ 22510447\\n\\nTerima kasih telah memilih Kemindo Group! 🙏\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"baik terima kasih\"},{\"role\":\"assistant\",\"content\":\"Sama-sama, Angga! 😊\\n\\nSenang bisa membantu Anda. Tim sales Kemindo akan segera menghubungi Anda untuk melanjutkan proses pemesanan.\\n\\nJika ada pertanyaan lain atau butuh informasi produk Kemindo yang lain, jangan ragu untuk menghubungi saya kembali.\\n\\n**Salam,**\\nAsisten AI Kemindo Group 🙏\\n\\n---\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"},{\"role\":\"user\",\"content\":\"baik terima kasih\"},{\"role\":\"assistant\",\"content\":\"Sama-sama! 😊\\n\\nTerima kasih telah menghubungi Kemindo Group. Jika ada yang bisa saya bantu di masa depan, silakan hubungi saya kapan saja.\\n\\n**Semoga sukses dengan pemesanan Anda!** 🙏\\n\\n---\\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"}]', 'new', '2026-05-06 09:34:14', '2026-05-06 09:34:14'),
(7, NULL, NULL, NULL, NULL, 'Purchase aluminum powder', '```json\n{\n  \"nama\": null,\n  \"whatsapp\": null,\n  \"email\": null,\n  \"perusahaan\": null,\n  \"intensi\": \"Purchase aluminum powder\",\n  \"summary\": null\n}\n```', '[{\"role\":\"user\",\"content\":\"aku mau beli alumunium powder\"},{\"role\":\"assistant\",\"content\":\"Halo! Terima kasih minat Anda pada **Aluminum Powder** dari Kemindo. \\n\\nBerikut spesifikasi produk kami:\\n\\n| Parameter | Spesifikasi |\\n|-----------|-------------|\\n| Al₂O₃ Content | 17% Min |\\n| Ferro Oxide Fe₂O₃ | 15.0 ppm Max |\\n| Lead (Pb) | 0.2 ppm Max |\\n| Arsenic (As) | 0.005 ppb Max |\\n| pH solution at 25°C | 2 |\\n\\nProduk ini tersedia dan cocok untuk berbagai aplikasi industri.\\n\\nUntuk melanjutkan proses pembelian dan mendiskusikan harga serta detail order, saya memerlukan beberapa informasi dari Anda:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa jumlah\\/volume yang Anda butuhkan?**\\n\\nSetelah informasi terkumpul, tim sales Kemindo akan menghubungi Anda segera untuk negosiasi harga dan detail pengiriman.\\n\\n**Untuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.**\"}]', 'new', '2026-05-08 07:06:05', '2026-05-08 07:06:05'),
(8, 'Anwar', '0898989898', 'anwar@bsi.co.id', 'PT BSI', 'Purchase Aluminum Powder with Al₂O₃ Content 20% for delivery within 2 weeks', 'Anwar from PT BSI is interested in purchasing Aluminum Powder with a custom specification of Al₂O₃ Content 20% (higher than the standard 17% minimum). He requires the product within 2 weeks and has provided his contact details for follow-up. The sales team should contact him to discuss the custom specification requirements, pricing, and confirm the exact quantity and delivery timeline.', '[{\"role\":\"user\",\"content\":\"aku mau beli alumunium powder\"},{\"role\":\"assistant\",\"content\":\"Halo! Terima kasih minat Anda pada **Aluminum Powder** dari Kemindo. \\n\\nBerikut spesifikasi produk kami:\\n\\n| Parameter | Spesifikasi |\\n|-----------|-------------|\\n| Al₂O₃ Content | 17% Min |\\n| Ferro Oxide Fe₂O₃ | 15.0 ppm Max |\\n| Lead (Pb) | 0.2 ppm Max |\\n| Arsenic (As) | 0.005 ppb Max |\\n| pH solution at 25°C | 2 |\\n\\nProduk ini tersedia dan cocok untuk berbagai aplikasi industri.\\n\\nUntuk melanjutkan proses pembelian dan mendiskusikan harga serta detail order, saya memerlukan beberapa informasi dari Anda:\\n\\n1. **Nama lengkap Anda?**\\n2. **Nama perusahaan?**\\n3. **Nomor WhatsApp?**\\n4. **Alamat email?**\\n5. **Berapa jumlah\\/volume yang Anda butuhkan?**\\n\\nSetelah informasi terkumpul, tim sales Kemindo akan menghubungi Anda segera untuk negosiasi harga dan detail pengiriman.\\n\\n**Untuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.**\"},{\"role\":\"user\",\"content\":\"nama saya anwar dari pt bsi nomor telpeon 0898989898 email anwar@bsi.co.id dan saya buthu alumunium powder dengan Al₂O₃ Content 20% untuk 2 minggu lagi\"},{\"role\":\"assistant\",\"content\":\"Terima kasih informasinya, Anwar! Saya catat data Anda:\\n\\n- **Nama Lengkap:** Anwar\\n- **Perusahaan:** PT BSI\\n- **Nomor WhatsApp:** 0898989898\\n- **Email:** anwar@bsi.co.id\\n- **Kebutuhan:** Aluminum Powder dengan Al₂O₃ Content 20% untuk 2 minggu ke depan\\n\\n**Catatan penting:** Spesifikasi Aluminum Powder Kemindo standar memiliki Al₂O₃ Content **17% Min**. Kebutuhan Anda untuk 20% adalah spesifikasi khusus yang perlu didiskusikan lebih lanjut dengan tim sales kami.\\n\\n✅ **Informasi Anda telah dicatat dan akan diteruskan ke tim sales Kemindo.** Tim sales akan segera menghubungi Anda melalui WhatsApp atau email untuk:\\n- Membahas spesifikasi khusus (Al₂O₃ 20%)\\n- Negosiasi harga\\n- Konfirmasi jumlah dan timeline pengiriman\\n- Penawaran terbaik untuk kebutuhan Anda\\n\\nTerima kasih telah memilih Kemindo! 🙏\\n\\n**Untuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.**\"}]', 'new', '2026-05-08 07:07:06', '2026-05-08 07:07:06'),
(9, NULL, NULL, NULL, NULL, NULL, '```json\n{\n  \"nama\": null,\n  \"whatsapp\": null,\n  \"email\": null,\n  \"perusahaan\": null,\n  \"intensi\": null,\n  \"summary\": null\n}\n```', '[{\"role\":\"user\",\"content\":\"kamu udah makan belum?\"},{\"role\":\"assistant\",\"content\":\"Halo! 😊 \\n\\nSaya adalah asisten AI dari Kemindo Group, jadi saya tidak bisa makan. Tetapi saya di sini untuk membantu Anda!\\n\\nApakah ada yang bisa saya bantu terkait:\\n- **Produk Kemindo** (Chemical, Agriculture, Mining, Logistics, Green Energy)\\n- **Informasi perusahaan** Kemindo Group\\n- **Kebutuhan bisnis Anda** yang mungkin bisa kami sediakan\\n\\nSilakan tanyakan apa pun tentang produk atau layanan kami! \\n\\nUntuk setiap inquiry harga atau produk, wajib kumpulkan: nama lengkap, perusahaan, nomor WhatsApp, dan email sebelum meneruskan ke tim sales.\"}]', 'new', '2026-05-09 12:38:18', '2026-05-09 12:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$EsQX1g3UMWna1/0pIxQ0CufSuNjsxrLL.8gUaM03umsKjmPby41j.', '2026-05-03 05:50:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auto_ingest_schedule`
--
ALTER TABLE `auto_ingest_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bot_settings`
--
ALTER TABLE `bot_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `email_recipients`
--
ALTER TABLE `email_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ingest_log`
--
ALTER TABLE `ingest_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingest_sources`
--
ALTER TABLE `ingest_sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_key` (`key`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auto_ingest_schedule`
--
ALTER TABLE `auto_ingest_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_recipients`
--
ALTER TABLE `email_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ingest_log`
--
ALTER TABLE `ingest_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ingest_sources`
--
ALTER TABLE `ingest_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
