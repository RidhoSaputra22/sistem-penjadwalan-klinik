from setuptools import setup, find_packages
from pathlib import Path

BASE_DIR = Path(__file__).parent

long_description = (BASE_DIR / "README.md").read_text(encoding="utf-8") \
    if (BASE_DIR / "README.md").exists() else ""

setup(
    name="whatsapp-selenium-bot",
    version="1.0.0",
    description="WhatsApp Web automation bot using Selenium and ChromeDriver",
    long_description=long_description,
    long_description_content_type="text/markdown",
    author="Ridho Saputra",
    license="MIT",

    packages=find_packages(exclude=("tests",)),
    include_package_data=True,

    python_requires=">=3.10",

    install_requires=[
        "selenium>=4.20.0",
        "webdriver-manager>=4.0.1",
        "qrcode[pil]>=7.4.2",
        "pillow>=10.0.0",
        "pyperclip>=1.8.2",
        "pywin32",
        "flask>=2.3.0",
        "flask-cors>=3.0.10",
        "mysql-connector-python>=8.0.33",
        "python-dotenv>=1.0.0",
    ],

    extras_require={
        "dev": [
            "black",
            "flake8",
            "mypy",
        ]
    },

    classifiers=[
        "Programming Language :: Python :: 3",
        "Operating System :: OS Independent",
        "License :: OSI Approved :: MIT License",
    ],

    keywords=[
        "whatsapp",
        "selenium",
        "automation",
        "bot",
        "whatsapp-web",
    ],
)
