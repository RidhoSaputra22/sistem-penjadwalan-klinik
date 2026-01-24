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


# ==========================
# ROUTES
# ==========================
@app.route("/send", methods=["POST"])
def send_whatsapp():
    # json payload: {
    #   "phone": "string",
    #   "message": "string"
    # }
    try:
        data = request.get_json(force=True)
        log(f"Payload → {data}", "PAYLOAD")

        phone = data.get("phone")
        message = data.get("message")

        if not phone or not message:
            log("Missing 'phone' or 'message' in payload", "ERROR")
            return jsonify({
                "status": "error",
                "request_id": g.request_id,
                "message": "Missing 'phone' or 'message' in payload"
            }), 400

        phone_number = normalize_phone(phone)
        log(f"Normalized phone → {phone_number}", "WA")

        logo_path = IMAGE_BASE_PATH / "logo.jpg"
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
