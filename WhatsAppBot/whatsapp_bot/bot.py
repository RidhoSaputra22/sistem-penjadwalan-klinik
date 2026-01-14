import os
import time
import logging
from pathlib import Path
from functools import lru_cache

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains
from webdriver_manager.chrome import ChromeDriverManager

import whatsapp_bot.utils as utils


# Cache ChromeDriver path to avoid redundant downloads/checks
@lru_cache(maxsize=1)
def _get_chromedriver_path():
    """Get cached ChromeDriver path."""
    return ChromeDriverManager().install()


class WhatsAppBot:
    """
    WhatsApp Web Automation Bot
    
    Usage with context manager (recommended):
        with WhatsAppBot() as bot:
            bot.open_chat("628123456789")
            bot.paste_message("Hello!")
            bot.send_message()
    """

    def __init__(
        self,
        profile_dir: str = "chrome_profile",
        timeout: int = 15,
        debug: bool = True,
        chrome_binary: str = None,
    ):
        self.timeout = timeout
        self.debug = debug
        self.chrome_binary = chrome_binary

        self._setup_logger()
        self._wait_cache = None  # Cache for WebDriverWait
        self._element_cache = {}  # Cache for frequently accessed elements
        self.driver = self._init_driver(profile_dir)
        self._open_whatsapp()
        self._wait_until_ready()

    # --------------------------------------------------
    # LOGGER
    # --------------------------------------------------
    def _setup_logger(self):
        self.logger = logging.getLogger("WhatsAppBot")
        level = logging.DEBUG if self.debug else logging.WARNING
        self.logger.setLevel(level)

        if not self.logger.handlers:
            handler = logging.StreamHandler()
            formatter = logging.Formatter(
                "[%(levelname)s] %(asctime)s - %(message)s"
            )
            handler.setFormatter(formatter)
            self.logger.addHandler(handler)

    # --------------------------------------------------
    # DRIVER INITIALIZATION
    # --------------------------------------------------
    def _get_wait(self) -> WebDriverWait:
        """Get cached WebDriverWait instance."""
        if self._wait_cache is None:
            self._wait_cache = WebDriverWait(self.driver, self.timeout)
        return self._wait_cache

    def _init_driver(self, profile_dir: str) -> webdriver.Chrome:
        self.logger.debug("Initializing Chrome driver")

        options = Options()
        
        # Use provided binary path or try OS-specific default locations
        if self.chrome_binary:
            options.binary_location = self.chrome_binary
        elif os.name == 'nt':  # Windows
            # Try common Chrome installation paths on Windows
            possible_paths = [
                r"C:\Program Files\Google\Chrome\Application\chrome.exe",
                r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
                os.path.expandvars(r"%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe"),
            ]
            for path in possible_paths:
                if os.path.exists(path):
                    options.binary_location = path
                    break
        elif os.name == 'posix':  # Linux/Mac
            # Try common Chrome/Chromium locations
            possible_paths = [
                '/usr/bin/google-chrome',  # Linux
                '/usr/bin/chromium-browser',  # Linux
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',  # macOS
            ]
            for path in possible_paths:
                if os.path.exists(path):
                    options.binary_location = path
                    break

        profile_path = os.path.abspath(profile_dir)
        options.add_argument(f"--user-data-dir={profile_path}")
        options.add_argument("--disable-gpu")
        options.add_argument("--no-sandbox")
        options.add_argument("--start-maximized")

        driver = webdriver.Chrome(
            service=Service(_get_chromedriver_path()),
            options=options,
        )

        return driver

    # --------------------------------------------------
    # WHATSAPP INITIALIZATION
    # --------------------------------------------------
    def _open_whatsapp(self):
        self.logger.debug("Opening WhatsApp Web")
        self.driver.get("https://web.whatsapp.com")

    def _wait_until_ready(self):
        self.logger.debug("Waiting for WhatsApp Web readiness")

        wait = self._get_wait()

        wait.until(
            EC.any_of(
                EC.presence_of_element_located((By.ID, "pane-side")),
                EC.presence_of_element_located((By.XPATH, "//canvas")),
            )
        )

        try:
            wait.until(
                EC.presence_of_element_located((By.ID, "pane-side"))
            )
            self.logger.info("WhatsApp Web ready (logged in)")
        except Exception:
            self.logger.error("QR code not scanned in time")
            raise RuntimeError("Login timeout: QR code not scanned")

    # --------------------------------------------------
    # ELEMENT HELPERS
    # --------------------------------------------------
    def _get_message_box(self):
        self.logger.debug("Locating message input box")
        try:
            return self._get_wait().until(
                EC.element_to_be_clickable(
                    (
                        By.XPATH,
                        '//footer//div[@contenteditable="true" and @data-tab="10"]',
                    )
                )
            )
        except Exception:
            self.logger.error("Message input box not found")
            raise RuntimeError("Cannot find message input box")
    
    def _get_send_button(self):
        self.logger.debug("Locating send button")
        return self._get_wait().until(
            EC.element_to_be_clickable(
                (By.XPATH, '//div[@role="button" and (@aria-label="Send" or @aria-label="Kirim")]')
            )
        )

    # --------------------------------------------------
    # CHAT ACTIONS
    # --------------------------------------------------
    def open_chat(self, phone_number: str):
        self._validate_phone(phone_number)

        url = f"https://web.whatsapp.com/send?phone={phone_number}"
        self.logger.info(f"Opening chat: {phone_number}")
        self.driver.get(url)
        
        # Clear element cache when navigating to new chat
        self._element_cache.clear()

    def _validate_phone(self, phone: str):
        if not phone.startswith("62"):
            self.logger.error("Invalid phone number format")
            raise ValueError("Nomor harus diawali 62")

    # --------------------------------------------------
    # MESSAGE ACTIONS
    # --------------------------------------------------
    def type_message(self, message: str):
        """
        Type message into message box.
        For long messages or messages with special characters, paste_message is more efficient.
        """
        self.logger.debug(f"Typing message: {message}")
        box = self._get_message_box()
        box.send_keys(message)
        
    def paste_message(self, message: str):
        self.logger.debug(f"Pasting message: {message}")
        utils.copy_text_to_clipboard(message)
        box = self._get_message_box()
        box.click()

        ActionChains(self.driver) \
            .key_down(Keys.CONTROL) \
            .send_keys("v") \
            .key_up(Keys.CONTROL) \
            .perform()

    def send_message(self, with_attachments: bool = False):
        if with_attachments:
            self.logger.debug("Sending message with attachments")
            send_button = self._get_send_button()
            send_button.click()
        else:    
            self.logger.debug("Sending message")
            self._get_message_box().send_keys(Keys.ENTER)
            self.logger.info("Message sent")

    # --------------------------------------------------
    # IMAGE ACTIONS
    # --------------------------------------------------
    def attach_image(self, image_path: Path):
        self.logger.debug(f"Attaching image: {image_path}")

        if not image_path.exists():
            self.logger.error("Image file not found")
            raise FileNotFoundError(image_path)

        utils.copy_image_to_clipboard(image_path)

        box = self._get_message_box()
        box.click()

        ActionChains(self.driver) \
            .key_down(Keys.CONTROL) \
            .send_keys("v") \
            .key_up(Keys.CONTROL) \
            .perform()

        self.logger.info("Image attached from clipboard")

    # --------------------------------------------------
    # CLEANUP
    # --------------------------------------------------
    def close(self):
        self.logger.warning("Closing browser")
        if self.driver:
            self.driver.quit()
            self.driver = None
            # Clear cached WebDriverWait to prevent stale driver references
            self._wait_cache = None
    
    def __enter__(self):
        """Context manager entry."""
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        """Context manager exit with automatic cleanup."""
        self.close()
        return False
