# Lastik Arıyorum

Lastik Arıyorum, kullanıcıların lastik arayışlarını kolaylaştıran ve lastik firmalarını bir araya getiren modern bir web platformudur.

## Özellikler

- **Firma Yönetimi**: Lastik firmalarının kayıt ve profil yönetimi
- **Şehir/İlçe Bazlı Arama**: Coğrafi konuma göre firma listelemeleri
- **Admin Paneli**: Kapsamlı yönetim ve moderasyon araçları
- **Makale Sistemi**: İçerik yönetimi ve blog yazıları
- **Kullanıcı Kimlik Doğrulama**: Güvenli giriş ve kayıt sistemi
- **API Entegrasyonu**: RESTful API desteği
- **GDPR Uyumlu**: Kullanıcı izinleri ve veri silme talepleri
- **SEO Optimizasyonu**: Dinamik sitemap ve SEO dostu URL yapısı

## Teknolojiler

- **Backend**: PHP 8.x
- **Veritabanı**: MySQL
- **Paket Yöneticisi**: Composer
- **Routing**: Özel Router sınıfı
- **Mimari**: MVC (Model-View-Controller)
- **Excel İşlemleri**: PHPSpreadsheet
- **Dependency Injection**: PSR-4 autoloading

## Kurulum

### Gereksinimler

- PHP >= 8.0
- MySQL >= 5.7
- Composer
- Apache/Nginx web sunucusu
- mod_rewrite etkin

### Adımlar

1. Projeyi klonlayın:
```bash
git clone https://github.com/selkisamet/lastikciariyorum.git
cd lastikciariyorum
```

2. Composer bağımlılıklarını yükleyin:
```bash
composer install
```

3. Environment (.env) dosyasını oluşturun:
   - `.env.example` dosyasını `.env` olarak kopyalayın:
     ```bash
     cp .env.example .env
     ```
   - `.env` dosyasını açıp kendi ayarlarınızı yapın:
     - Veritabanı bilgilerinizi girin
     - Mail SMTP bilgilerinizi girin
     - Uygulama URL'sini ayarlayın

4. Veritabanını import edin:
   - SQL dosyasını MySQL'e import edin

5. Web sunucunuzu yapılandırın:
   - Document root olarak `public/` klasörünü ayarlayın
   - `.htaccess` dosyasının çalıştığından emin olun

6. Dizin izinlerini ayarlayın:
```bash
chmod -R 755 public/
chmod -R 777 public/uploads/
```

## Proje Yapısı

```
lastikciariyorum/
├── .env                # Environment değişkenleri (GİT'E EKLEME!)
├── .env.example        # Environment örnek dosyası
├── config/             # Konfigürasyon dosyaları
│   ├── config.php      # Ana konfigürasyon (env() kullanır)
│   ├── database.php    # Veritabanı bağlantısı (env() kullanır)
│   └── mail.php        # E-posta ayarları (env() kullanır)
├── controllers/        # Controller sınıfları
│   ├── AdminController.php
│   ├── ArticleController.php
│   ├── AuthController.php
│   ├── CityController.php
│   ├── CompanyController.php
│   ├── HomeController.php
│   └── PageController.php
├── core/              # Çekirdek sınıflar
│   ├── Controller.php # Base controller
│   ├── Database.php   # Veritabanı katmanı
│   ├── Model.php      # Base model
│   ├── Router.php     # Routing sistemi
│   ├── Mail.php       # E-posta servisi
│   └── helpers.php    # Yardımcı fonksiyonlar
├── models/            # Model sınıfları
│   ├── Article.php
│   ├── City.php
│   ├── Company.php
│   ├── User.php
│   └── ...
├── views/             # View dosyaları
│   ├── layouts/      # Layout şablonları
│   ├── home/         # Ana sayfa görünümleri
│   ├── admin/        # Admin panel görünümleri
│   └── ...
├── public/            # Public assets
│   ├── index.php     # Giriş noktası
│   ├── css/          # CSS dosyaları
│   ├── js/           # JavaScript dosyaları
│   ├── images/       # Resim dosyaları
│   └── uploads/      # Kullanıcı yüklemeleri
├── database/          # Veritabanı migrations
├── vendor/            # Composer bağımlılıkları
└── composer.json      # Composer konfigürasyonu
```

## Konfigürasyon

### Environment Değişkenleri (.env)

Proje tüm hassas bilgileri ve ortam ayarlarını `.env` dosyasından okur. Bu dosya git'e commit edilmez.

**`.env` dosyasında bulunan ayarlar:**

```env
# Uygulama
APP_NAME="lastikciariyorum"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.lastikciariyorum.com

# Veritabanı
DB_HOST=localhost
DB_NAME=lastikciariyorum
DB_USER=root
DB_PASS=your-password

# Mail (SMTP)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=info@example.com
MAIL_PASSWORD=your-mail-password
```

**Kullanım:**

Config dosyalarında iki helper fonksiyon kullanılır:

```php
// config/database.php
return [
    // envRequired() - Zorunlu değerler, yoksa hata fırlatır
    'host' => envRequired('DB_HOST'),
    'dbname' => envRequired('DB_NAME'),

    // env() - Opsiyonel değerler, yoksa default değer döner
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];
```

**Güvenlik:**
- **envRequired()**: Kritik değerler için kullanılır. Değer `.env` dosyasında yoksa veya boşsa uygulama hata verir ve çalışmaz.
- **env()**: Opsiyonel değerler için kullanılır. Default değerler sadece zararsız ayarlar için (charset, port vs.)
- Bu sayede `.env` dosyası olmadan veya eksik bilgilerle uygulama çalışmaz, güvenlik açığı oluşmaz.

## Kullanım

### Routing

Router sınıfı ile rota tanımlamaları:

```php
$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->post('/login', [AuthController::class, 'login']);

$router->run();
```

### Controller Oluşturma

```php
class CompanyController extends Controller
{
    public function index()
    {
        $companies = $this->model('Company')->all();
        $this->view('company/index', ['companies' => $companies]);
    }
}
```

### Model Kullanımı

```php
class Company extends Model
{
    protected $table = 'companies';

    public function findByCity($cityId)
    {
        return $this->where('city_id', $cityId)->get();
    }
}
```

## API Endpoints

API dokümantasyonu için `/public/api/` dizinine bakınız.

## Güvenlik

- SQL Injection koruması
- XSS koruması
- CSRF token doğrulama
- Güvenli şifre hashleme
- Prepared statements
- Input validasyonu

## Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## Lisans

Bu proje özel bir projedir. Tüm hakları saklıdır.

## İletişim

Proje Sahibi - [@kullaniciadi](https://github.com/kullaniciadi)

Proje Linki: [https://github.com/kullaniciadi/lastikciariyorum](https://github.com/kullaniciadi/lastikciariyorum)

## Teşekkürler

- [PHPSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) - Excel işlemleri için
- [Composer](https://getcomposer.org/) - Paket yönetimi için
