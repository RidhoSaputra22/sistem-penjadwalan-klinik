from whatsapp_bot.bot import WhatsAppBot
from pathlib import Path
import whatsapp_bot.utils as utils

message = """Halo *Andi Pratama*,

Terima kasih telah melakukan reservasi lapangan di *Arena Futsal Maju Jaya*.

Berikut detail reservasi Anda:

🧾 Nama: Andi Pratama
📞 No. HP: 628123456789
🏟 Lapangan: Futsal A
📅 Tanggal: 22 Desember 2025
⏰ Jam: 19.00 – 20.00 WIB
💰 Total Biaya: Rp 150.000
💳 Status Pembayaran: LUNAS

Mohon simpan pesan ini sebagai bukti reservasi.

Jika terdapat perubahan atau pertanyaan, silakan hubungi kami melalui WhatsApp ini.

Hormat kami,
Arena Futsal Maju Jaya
"""


def main(bot: WhatsAppBot, phone_number: str, message: str, qr_path: Path = None):
    """
    Send message with optional QR code attachment.
    
    :param bot: WhatsAppBot instance
    :param phone_number: Recipient phone number
    :param message: Message to send
    :param qr_path: Optional pre-generated QR code path
    """
    bot.open_chat(phone_number)
    bot.paste_message(message)
    
    if qr_path and qr_path.exists():
        bot.attach_image(qr_path)
        bot.send_message(with_attachments=True)
    else:
        bot.send_message()

if __name__ == "__main__":
    # Initialize bot only when needed
    bot = WhatsAppBot(debug=False)
    
    # Generate QR code once (reusable for all messages)
    qr_path = utils.generate_qr_code(
        data="https://www.example.com", 
        output_dir="temp",
        prefix="qr_code"
    )
    print(f"QR code generated: {qr_path}")
    
    # Example usage
    exit = ''
    while True:
        phone_number = input("Enter phone number (with country code, e.g., 08123456789): ").strip()
        phone_number = '62' + phone_number.lstrip('0')
        if exit == 'q':
            bot.close()        
            break
        try:
            # Pass qr_path to reuse the same QR code (or None to skip attachment)
            main(bot, phone_number, message, qr_path=qr_path)
        except Exception as e:
            print(f"Failed to send message: {e}")
        exit = input("Exit/Continue (q/ENTER): ").strip().lower()
