# Twilio API Mock Server

Hệ thống giả lập API của Twilio để test hệ thống. Hỗ trợ gửi SMS đến số điện thoại Mỹ với request/response format giống hệt Twilio.

## Tính năng

- ✅ Giả lập Twilio Messages API (POST, GET, DELETE)
- ✅ Authentication với API Key hoặc Account SID/Auth Token
- ✅ Response format giống hệt Twilio
- ✅ Hỗ trợ số điện thoại Mỹ (format: +1XXXXXXXXXX)
- ✅ Pagination và filtering
- ✅ Tính toán số segments tự động

## Cài đặt

1. Cài đặt dependencies:
```bash
composer install
npm install
```

2. Tạo file `.env` từ `.env.example`:
```bash
cp .env.example .env
php artisan key:generate
```

3. Cấu hình Twilio credentials trong `.env`:
```env
TWILIO_ACCOUNT_SID=ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_API_KEY=your_api_key
TWILIO_API_SECRET=your_api_secret
```

4. Chạy migration:
```bash
php artisan migrate
```

5. Khởi động server:
```bash
php artisan serve
```

## Sử dụng

### Base URL
```
http://localhost:8000/api
```

### Authentication

Sử dụng HTTP Basic Authentication với:
- **Username**: API Key hoặc Account SID
- **Password**: API Secret hoặc Auth Token

### Gửi SMS

**POST** `/api/2010-04-01/Accounts/{AccountSid}/Messages.json`

```bash
curl -X POST http://localhost:8000/api/2010-04-01/Accounts/ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa/Messages.json \
  -u YOUR_API_KEY:YOUR_API_SECRET \
  -d "To=+12025551234" \
  -d "From=+12025555678" \
  -d "Body=Hello from Twilio Mock!"
```

**Response:**
```json
{
  "account_sid": "ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
  "api_version": "2010-04-01",
  "body": "Hello from Twilio Mock!",
  "date_created": "Mon, 18 Nov 2024 12:00:00 +0000",
  "date_sent": "Mon, 18 Nov 2024 12:00:00 +0000",
  "date_updated": "Mon, 18 Nov 2024 12:00:00 +0000",
  "direction": "outbound-api",
  "error_code": null,
  "error_message": null,
  "from": "+12025555678",
  "messaging_service_sid": null,
  "num_media": "0",
  "num_segments": "1",
  "price": "-0.00750",
  "price_unit": "USD",
  "sid": "SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "status": "sent",
  "subresource_uris": {
    "media": "/2010-04-01/Accounts/ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa/Messages/SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx/Media.json",
    "feedback": "/2010-04-01/Accounts/ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa/Messages/SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx/Feedback.json"
  },
  "to": "+12025551234",
  "uri": "/2010-04-01/Accounts/ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa/Messages/SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.json"
}
```

### Lấy danh sách Messages

**GET** `/api/2010-04-01/Accounts/{AccountSid}/Messages.json`

```bash
curl -X GET "http://localhost:8000/api/2010-04-01/Accounts/ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa/Messages.json?PageSize=50&Page=0" \
  -u YOUR_API_KEY:YOUR_API_SECRET
```

### Lấy thông tin Message cụ thể

**GET** `/api/2010-04-01/Accounts/{AccountSid}/Messages/{MessageSid}.json`

```bash
curl -X GET http://localhost:8000/api/2010-04-01/Accounts/ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa/Messages/SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.json \
  -u YOUR_API_KEY:YOUR_API_SECRET
```

### Xóa Message

**DELETE** `/api/2010-04-01/Accounts/{AccountSid}/Messages/{MessageSid}.json`

```bash
curl -X DELETE http://localhost:8000/api/2010-04-01/Accounts/ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa/Messages/SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.json \
  -u YOUR_API_KEY:YOUR_API_SECRET
```

## Lưu ý

- Số điện thoại Mỹ phải có format: `+1XXXXXXXXXX` (11 ký tự, bắt đầu bằng +1)
- Response format giống hệt Twilio API
- Tất cả messages được lưu trong database để có thể query lại

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
