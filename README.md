# Tile Expert Test App (Symfony)

## Запуск

1. Настройте переменные в `.env` (корень репо):
   - `NGINX_PORT` (по умолчанию 8081)
   - `DB_PORT` (по умолчанию 3307)
   - `MANTICORE_PORT` (9306), `MANTICORE_HTTP_PORT` (9308)
2. Поднять контейнеры:
   - `docker-compose up -d --build`
3. Логи: `docker-compose logs -f --tail=200`, Остановка: `docker-compose down -v`.

MariaDB инициализируется из `dump.sql`.

## Manticore Setup
После запуска контейнеров заполните индекс вручную (если нужно):
```bash
docker-compose exec php bash -lc "php bin/console app:manticore:seed"
```

## API Documentation

### Swagger
- UI: `http://localhost:8081/api/docs`

### Postman Collection
Импортируйте файл `TileExpert.postman_collection.json` в Postman для тестирования API.

**Переменные коллекции:**
- `baseUrl`: `http://localhost:8081`
- `factory`: `cobsa`
- `collection`: `manual`
- `article`: `manu7530bcbm-manualbaltic7-5x30`
- `page`: `1`
- `per_page`: `10`
- `group`: `day`
- `orderId`: `1`
- `query`: `test`

## Эндпоинты

### 1. Price Scraping
- GET `/api/price?factory=...&collection=...&article=...`
  - Ответ: `{ "price": 38.99, "factory": "...", "collection": "...", "article": "..." }`
  - Парсит цену с tile.expert с улучшенными CSS-селекторами

### 2. Orders Management
- GET `/api/orders/{id}` — получить один заказ
- GET `/api/orders/aggregate?page=1&per_page=10&group=day|month|year` — агрегирование с пагинацией

### 3. SOAP Service
- GET `/api/soap/wsdl` — WSDL схема
- POST `/api/soap` — SOAP сервер для создания заказов
- Пример SOAP запроса:
```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <CreateOrder xmlns="http://tile.expert/api/soap">
            <name>Test Order</name>
            <email>test@example.com</email>
            <status>1</status>
        </CreateOrder>
    </soap:Body>
</soap:Envelope>
```

### 4. Search
- GET `/api/search?q=...&page=1&per_page=10` — поиск через Manticore (index `orders`)

## Тестирование

### Автоматические тесты (PHPUnit)

Запуск всех тестов:
```bash
docker-compose exec php vendor/bin/phpunit --testdox
```

Запуск конкретного теста:
```bash
docker-compose exec php vendor/bin/phpunit tests/PriceControllerTest.php
```

Запуск интеграционных тестов:
```bash
docker-compose exec php vendor/bin/phpunit tests/Integration/
```

### Тестирование через Postman

1. Импортируйте коллекцию:
   - Откройте Postman
   - File → Import
   - Выберите `TileExpert.postman_collection.json`

2. Настройте переменные (если нужно):
   - `baseUrl`: `http://localhost:8081`
   - Другие переменные уже настроены с примерами

3. Запускайте запросы:
   - **Price** - получить цену плитки
   - **Orders - aggregate** - группировка заказов
   - **Orders - get by id** - получить заказ
   - **SOAP - WSDL** - получить WSDL схему
   - **SOAP - create order** - создать заказ через SOAP
   - **Search - Manticore** - поиск через Manticore

### Тестирование через cURL

#### 1. Получить цену плитки
```bash
curl "http://localhost:8081/api/price?factory=cobsa&collection=manual&article=manu7530bcbm-manualbaltic7-5x30"
```

#### 2. Агрегация заказов
```bash
curl "http://localhost:8081/api/orders/aggregate?page=1&per_page=10&group=day"
```

#### 3. Получить заказ по ID
```bash
curl "http://localhost:8081/api/orders/1"
```

#### 4. Создать заказ через SOAP
```bash
curl -X POST http://localhost:8081/api/soap \
  -H "Content-Type: text/xml" \
  -d '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <CreateOrder xmlns="http://tile.expert/api/soap">
      <name>Test Order</name>
      <email>test@example.com</email>
      <status>1</status>
    </CreateOrder>
  </soap:Body>
</soap:Envelope>'
```

#### 5. Поиск через Manticore
```bash
curl "http://localhost:8081/api/search?q=test&page=1&per_page=10"
```

#### 6. Получить WSDL схему
```bash
curl "http://localhost:8081/api/soap/wsdl"
```

### Тестирование через Swagger

1. Откройте браузер: `http://localhost:8081/api/docs`
2. Изучите доступные эндпоинты
3. Нажмите "Try it out" для любого эндпоинта
4. Заполните параметры и нажмите "Execute"

### Проверка работоспособности

Проверка статуса контейнеров:
```bash
docker-compose ps
```

Проверка логов:
```bash
docker-compose logs -f --tail=50
```

Проверка конкретного сервиса:
```bash
docker-compose logs php
docker-compose logs manticore
```

## Замечания по БД и улучшения
- См. `db-improvements.md`. Улучшенный дамп: `dump_improved.sql`.


