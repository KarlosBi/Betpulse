-- Base de datos aislada para PHPUnit (Paso 4: QA financiero).
-- Solo se ejecuta la primera vez que se crea el volumen pgdata.
SELECT 'CREATE DATABASE betpulse_testing'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'betpulse_testing')\gexec
