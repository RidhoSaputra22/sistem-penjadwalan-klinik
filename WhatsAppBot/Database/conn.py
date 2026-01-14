import mysql.connector
from mysql.connector import pooling

import os
from dotenv import find_dotenv, load_dotenv
from pathlib import Path


class Config:
    _env_loaded = False

    @classmethod
    def load_env(cls) -> None:
        if cls._env_loaded:
            return

        explicit_path = os.getenv("LARAVEL_ENV_PATH")
        if explicit_path:
            load_dotenv(dotenv_path=explicit_path, override=False)
            cls._env_loaded = True
            return

        # Prefer searching from the current working directory so this works even
        # when this module is imported from site-packages.
        dotenv_path = find_dotenv(usecwd=True)
        if dotenv_path:
            load_dotenv(dotenv_path=dotenv_path, override=False)
            cls._env_loaded = True
            return

        # Fallback: try common relative locations from this file.
        try:
            for parent in Path(__file__).resolve().parents:
                candidate = parent / ".env"
                if candidate.exists():
                    load_dotenv(dotenv_path=candidate, override=False)
                    break
        finally:
            cls._env_loaded = True

    @classmethod
    def db_host(cls) -> str | None:
        cls.load_env()
        return os.getenv("DB_HOST")

    @classmethod
    def db_port(cls) -> int:
        cls.load_env()
        raw = os.getenv("DB_PORT")
        if not raw:
            return 3306
        try:
            return int(raw)
        except ValueError as exc:
            raise ValueError(f"Invalid DB_PORT={raw!r}; expected an integer") from exc

    @classmethod
    def db_name(cls) -> str | None:
        cls.load_env()
        return os.getenv("DB_DATABASE")

    @classmethod
    def db_user(cls) -> str | None:
        cls.load_env()
        return os.getenv("DB_USERNAME")

    @classmethod
    def db_password(cls) -> str:
        cls.load_env()
        return os.getenv("DB_PASSWORD", "")

    FLASK_HOST = "0.0.0.0"
    FLASK_PORT = 5000


class Database:
    _pool = None

    @staticmethod
    def _require(value: str | None, name: str) -> str:
        if value is None or value == "":
            raise RuntimeError(
                f"Missing required setting {name}. "
                "Ensure Laravel .env is available (or set LARAVEL_ENV_PATH / DB_* env vars)."
            )
        return value

    @classmethod
    def get_pool(cls):
        if cls._pool is None:
            host = cls._require(Config.db_host(), "DB_HOST")
            database = cls._require(Config.db_name(), "DB_DATABASE")
            user = cls._require(Config.db_user(), "DB_USERNAME")
            port = Config.db_port()
            password = Config.db_password()

            cls._pool = pooling.MySQLConnectionPool(
                pool_name="arena_pool",
                pool_size=5,
                host=host,
                port=port,
                database=database,
                user=user,
                password=password,
            )
        return cls._pool

    @classmethod
    def get_connection(cls):
        return cls.get_pool().get_connection()
