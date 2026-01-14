# WhatsApp Selenium Bot

Bot otomatisasi WhatsApp Web menggunakan **Python + Selenium + ChromeDriver**  
Mendukung:

- Persistent login (QR hanya sekali)
- Kirim pesan teks (Unicode & emoji aman)
- Kirim gambar via clipboard
- Siap untuk broadcast

---

## 1. Persyaratan Sistem

Pastikan sistem Anda memenuhi kebutuhan berikut:

### Software Wajib

- Python **>= 3.10**
- Google Chrome (disarankan versi terbaru)
- Git (opsional)

### Sistem Operasi

- Windows ✅ (tested)
- Linux / macOS ⚠️ (perlu penyesuaian shortcut paste)

---

## 2. Clone Project

```bash
git clone https://github.com/username/whatsapp-bot.git
cd whatsapp-bot
```

---

## 3. Buat Virtual Environment (Disarankan)

```bash
python -m venv venv
```

Aktifkan virtual environment:

**Windows**

```bash
venv\Scripts\activate
```

**Linux / macOS**

```bash
source venv/bin/activate
```

---

## 4. Install Dependency

Jika menggunakan `setup.py`:

```bash
pip install .
```

Atau untuk development mode:

```bash
pip install -e .
```

Jika manual:

```bash
pip install selenium webdriver-manager qrcode[pil] pillow pyperclip
```

---

## 5. Struktur Project

```
whatsapp-bot/
├── whatsapp_bot/        # Source code (package)
├── scripts/             # Entry point
├── chrome_profile/      # Session WhatsApp (auto-created)
├── temp/                # File sementara (QR, image)
├── data/                # File input (nomor, dll)
├── setup.py
└── README.md
```

⚠️ Folder berikut **tidak boleh dihapus saat runtime**:

- `chrome_profile/`
- `temp/`

---

## 6. Login WhatsApp (QR Code)

Saat pertama kali dijalankan:

```bash
python scripts/run_bot.py
```

- Chrome akan terbuka otomatis
- Scan QR Code menggunakan WhatsApp di HP
- Session akan tersimpan di `chrome_profile/`
- Login **tidak perlu diulang**

---

## 7. Contoh Penggunaan Dasar

### Kirim Pesan Teks

```python
from whatsapp_bot.bot import WhatsAppBot

bot = WhatsAppBot(debug=True)

bot.open_chat("628123456789")
bot.type_message("Halo, ini pesan dari bot")
bot.send_message()
```

---

### Kirim Pesan + Gambar

```python
from pathlib import Path
from whatsapp_bot.bot import WhatsAppBot

bot = WhatsAppBot()

bot.open_chat("628123456789")
bot.attach_image(Path("temp/invoice.png"))
bot.type_message("Berikut invoice reservasi Anda")
bot.send_message()
```

---

## 8. Catatan Penting (WAJIB DIBACA)

### Emoji & Unicode

ChromeDriver **tidak mendukung Unicode non-BMP** via `send_keys`.

✅ Bot ini **menggunakan clipboard paste**, sehingga:

- Emoji aman
- Format pesan tidak rusak

### Nomor Telepon

- Gunakan format internasional
- Contoh: `628xxxxxxxxxx`
- Jangan gunakan `+62`

---

## 9. Mode Logging

```python
WhatsAppBot(debug=True)   # Development (verbose log)
WhatsAppBot(debug=False)  # Production (minimal log)
```

---

## 10. Troubleshooting

### ❌ ChromeDriver only supports characters in the BMP

**Solusi**: Jangan pakai `send_keys(text)`, gunakan clipboard paste (sudah di-handle bot).

### ❌ QR tidak muncul

- Pastikan koneksi internet stabil
- Hapus folder `chrome_profile/` lalu jalankan ulang

---

## License

MIT License
