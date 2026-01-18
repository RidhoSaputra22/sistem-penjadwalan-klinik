from flask import Flask, request, jsonify, g
from whatsapp_bot.bot import WhatsAppBot
from pathlib import Path
import traceback
import os
import sys
from datetime import datetime
import time
import uuid

# Ensure project root (WhatsAppBot/) is on sys.path when running as a script.
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from Database.conn import Database

# ==========================
# CONFIG
# ==========================
DEBUG = True
PROJECT_ROOT = Path(__file__).resolve().parents[2]
IMAGE_BASE_PATH = PROJECT_ROOT / "public" / "images"
MONOPIC_ADDRESS = "Ruko Griya, Jl. Goa Ria Muthmainnah No.2, Sudiang, Kec. Biringkanaya, Kota Makassar, Sulawesi Selatan 90242"


app = Flask(__name__)

_bot: WhatsAppBot | None = None


def get_bot() -> WhatsAppBot:
    global _bot
    if _bot is None:
        _bot = WhatsAppBot(
            debug=True,
            timeout=30,
        )
    return _bot

# ==========================
# TEMPLATE
# ==========================
MESSAGE_TEMPLATE = """Halo {{nama_klien}},

Terima kasih telah melakukan reservasi di Klinik Goaria.
Reservasi Anda berhasil dibuat dan saat ini telah tercatat dalam sistem kami.

📅 Tanggal: {{tanggal}}
⏰ Waktu: {{waktu}}
📸 Layanan/Paket: {{nama_paket}}
📸 Kode Booking: {{kode_booking}}
📍 Lokasi: {{lokasi}}
Tim kami akan melakukan konfirmasi lanjutan sesuai jadwal yang telah Anda pilih. Mohon pastikan data reservasi sudah sesuai. Jika terdapat pertanyaan atau perubahan, jangan ragu untuk menghubungi kami melalui WhatsApp ini.

Terima kasih atas kepercayaan Anda.
Klinik Goaria
"""

# ==========================
# DEBUG HELPERS
# ==========================
def log(message: str, level: str = "INFO"):
    if DEBUG:
        req_id = getattr(g, "request_id", "NO-REQ")
        print(f"[{level}] [{req_id}] {message}")


@app.before_request
def before_request():
    g.request_id = str(uuid.uuid4())[:8]
    g.start_time = time.time()
    log(f"Incoming {request.method} {request.path}", "START")


@app.after_request
def after_request(response):
    elapsed = time.time() - g.start_time
    log(f"Completed in {elapsed:.2f}s | HTTP {response.status_code}", "END")
    return response


def normalize_phone(phone: str) -> str:
    phone = phone.strip()
    if phone.startswith("0"):
        return "62" + phone[1:]
    if phone.startswith("+"):
        return phone[1:]
    return phone

def get_user_by_email(user_email: str) -> dict | None:
    log(f"DB: Fetching user {user_email}", "DB")
    conn = Database.get_connection()
    cursor = None

    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
                SELECT name, phone
            FROM users
            WHERE email = %s
        """, (user_email,))
        result = cursor.fetchone()
        log(f"DB: User result → {result}", "DB")
        return result

    finally:
        if cursor:
            cursor.close()
        conn.close()


def get_booking_by_code(booking_code: str) -> dict | None:
    log(f"DB: Fetching booking {booking_code}", "DB")
    conn = Database.get_connection()
    cursor = None

    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
                SELECT scheduled_date, code
            FROM appointments
            WHERE code = %s && status = 'confirmed'
        """, (booking_code,))
        result = cursor.fetchone()
        log(f"DB: Booking result → {result}", "DB")
        return result

    finally:
        if cursor:
            cursor.close()
        conn.close()


def get_paket_by_slug(paket_slug: str) -> dict | None:
    log(f"DB: Fetching paket {paket_slug}", "DB")
    conn = Database.get_connection()
    cursor = None

    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT name
            FROM services
            WHERE slug = %s
        """, (paket_slug,))
        result = cursor.fetchone()
        log(f"DB: Paket result → {result}", "DB")
        return result

    finally:
        if cursor:
            cursor.close()
        conn.close()


# ==========================
# MESSAGE RENDERING
# ==========================
def render_message_template(message_template: str, booking: dict, paket: dict, user: dict) -> str: # establish database connection
    log("Rendering message template", "debug")
    rendered_message = message_template
    rendered_message = rendered_message.replace("{{nama_klien}}", user['name'])
    rendered_message = rendered_message.replace("{{tanggal}}", booking['scheduled_date'].strftime("%d-%m-%Y"))
    rendered_message = rendered_message.replace("{{waktu}}", f"{booking['scheduled_date'].strftime('%H:%M')}")
    rendered_message = rendered_message.replace("{{nama_paket}}", str(paket['name']))
    rendered_message = rendered_message.replace("{{kode_booking}}", f"{booking['code']}")
    rendered_message = rendered_message.replace("{{lokasi}}", MONOPIC_ADDRESS)
    return rendered_message



# ==========================
# ROUTES
# ==========================
@app.route("/send", methods=["POST"])
def send_whatsapp():
    # json payload: {
    #   "user_email": string,
    #   "booking_code": string,
    #   "paket_slug": string
    # }
    try:
        data = request.get_json(force=True)
        log(f"Payload → {data}", "PAYLOAD")

        user_email = data.get("user_email")
        booking_code = data.get("booking_code")
        paket_slug = data.get("paket_slug")



        user = get_user_by_email(user_email)
        booking = get_booking_by_code(booking_code)
        paket = get_paket_by_slug(paket_slug)

        if not user or not booking or not paket:
            log("Data validation failed", "ERROR")
            return jsonify({
                "status": "error",
                "message": "User / Booking / Paket not found"
            }), 404

        message = render_message_template(message_template=MESSAGE_TEMPLATE, user=user, booking=booking, paket=paket)

        phone_number = normalize_phone(user["phone"])
        log(f"Normalized phone → {phone_number}", "WA")

        logo_path = IMAGE_BASE_PATH / "logo.png"
        if not logo_path.is_file():
            raise FileNotFoundError(f"Logo image not found at {logo_path}")


        # ==========================
        # SEND WHATSAPP
        # ==========================
        bot = get_bot()
        log("Opening WhatsApp chat", "WA")
        bot.open_chat(phone_number)

        log("Pasting message", "WA")
        bot.paste_message(message)

        log("Attaching QR image", "WA")
        bot.attach_image(logo_path)

        log("Sending message", "WA")
        bot.send_message(with_attachments=True)

        log("WhatsApp message sent successfully", "SUCCESS")

        return jsonify({
            "status": "success",
            "phone": phone_number,
            "request_id": g.request_id
        })

    except Exception as e:
        log(f"Unhandled exception → {e}", "FATAL")
        traceback.print_exc()
        return jsonify({
            "status": "error",
            "request_id": g.request_id,
            "message": str(e)
        }), 500


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


# ==========================
# MAIN
# ==========================
if __name__ == "__main__":
    app.run(
        host="0.0.0.0",
        port=5000,
        debug=False
    )
