import sys
import pyperclip
from PIL import Image
import io
import qrcode
from pathlib import Path
from contextlib import contextmanager
import os
import shutil
import subprocess

import time

if sys.platform == 'win32':
    import win32clipboard

@contextmanager
def clipboard_context():
    """
    Context manager for safe clipboard operations.

    Note: This uses win32clipboard which is Windows-specific.
    For cross-platform clipboard operations, use copy_text_to_clipboard() instead.
    """
    if sys.platform != 'win32':
        raise NotImplementedError("clipboard_context is only supported on Windows")

    win32clipboard.OpenClipboard()
    try:
        yield
    finally:
        win32clipboard.CloseClipboard()


def copy_image_to_clipboard(image_path):
    """
    Copy image to clipboard.

    Supported platforms:
    - Windows: uses win32clipboard
    - Linux:
        - Wayland: uses wl-copy (wl-clipboard)
        - X11: uses xclip

    Notes:
    - This requires a desktop session with clipboard support.
    - On Linux, install either wl-clipboard (Wayland) or xclip (X11).
    """

    image_path = Path(image_path)
    if not image_path.exists():
        raise FileNotFoundError(image_path)

    if sys.platform == 'win32':
        with Image.open(image_path) as image:
            output = io.BytesIO()
            image.convert("RGB").save(output, "BMP")
            data = output.getvalue()[14:]  # remove BMP header

        with clipboard_context():
            win32clipboard.EmptyClipboard()
            win32clipboard.SetClipboardData(win32clipboard.CF_DIB, data)
        return

    if sys.platform.startswith('linux'):
        with Image.open(image_path) as image:
            output = io.BytesIO()
            image.save(output, format="PNG")
            png_bytes = output.getvalue()

        # Prefer Wayland if available
        if os.environ.get("WAYLAND_DISPLAY") and shutil.which("wl-copy"):
            subprocess.run(
                ["wl-copy", "--type", "image/png"],
                input=png_bytes,
                check=True,
            )
            return

        # X11 fallback
        if os.environ.get("DISPLAY") and shutil.which("xclip"):
            subprocess.run(
                ["xclip", "-selection", "clipboard", "-t", "image/png", "-i"],
                input=png_bytes,
                check=True,
            )
            return

        missing = []
        if os.environ.get("WAYLAND_DISPLAY") and not shutil.which("wl-copy"):
            missing.append("wl-copy (package: wl-clipboard)")
        if os.environ.get("DISPLAY") and not shutil.which("xclip"):
            missing.append("xclip")

        hint = ""
        if missing:
            hint = " Missing: " + ", ".join(missing) + "."

        raise RuntimeError(
            "Cannot copy image to clipboard on Linux." + hint +
            " Install wl-clipboard (Wayland) or xclip (X11), and ensure you have an active desktop session."
        )

    raise NotImplementedError(f"copy_image_to_clipboard is not supported on platform: {sys.platform}")

def copy_text_to_clipboard(text: str):
    """
    Copy text to clipboard using pyperclip (cross-platform).

    This function works on Windows, Linux, and macOS.
    """
    pyperclip.copy(text)




def generate_qr_code(
    data: str,
    output_dir: Path | str = "temp",
    prefix: str = "qr_code",
) -> Path:
    """
    Generate QR code image from given data.

    Optimized to avoid redundant operations and properly handle resources.

    :param data: Data to encode in QR
    :param output_dir: Directory to save QR image
    :param prefix: Filename prefix
    :return: Path to generated QR image
    """

    if not data:
        raise ValueError("QR data must not be empty")

    output_dir = Path(output_dir)
    output_dir.mkdir(parents=True, exist_ok=True)

    # Use balanced error correction for reliability without size overhead
    qr = qrcode.QRCode(
        version=None,  # auto
        error_correction=qrcode.constants.ERROR_CORRECT_Q,
        box_size=10,
        border=4,
    )

    qr.add_data(data)
    qr.make(fit=True)

    img = qr.make_image(
        fill_color="black",
        back_color="white"
    )

    filename = f"{prefix}_{int(time.time() * 1000)}.png"
    output_path = output_dir / filename

    img.save(output_path)

    return output_path


def clear_cache_dir(cache_dir: Path | str = "temp"):
    """
    Clear all files in cache directory efficiently.

    :param cache_dir: Path to cache directory
    """
    cache_dir = Path(cache_dir)
    if not cache_dir.exists():
        return

    # Use generator expression for memory efficiency
    for file in (f for f in cache_dir.iterdir() if f.is_file()):
        try:
            file.unlink()
        except OSError as e:
            # Use logging if available, otherwise print
            print(f"Failed to delete {file}: {e}")
