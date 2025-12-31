# SEO HUB Architecture - Deployment Guide

## 📋 Genel Bakış

Bu deployment guide, lastikciariyorum.com projesinde yapılan SEO HUB mimarisine geçiş işlemlerini adım adım açıklar.

**Değişiklik Özeti:**
- ✅ İl sayfaları → HUB (firma listesi YOK, sadece içerik + ilçe linkleri)
- ✅ İlçe sayfaları → TEK güçlü sayfa (uzun makale + firmalar sidebar'da)
- ✅ Eski URL'ler → 301 redirect sistemi
- ✅ Çoklu anahtar kelime ile AI makale üretimi
- ✅ Database constraint ile duplicate önleme

---

## 🔧 Teknik Değişiklikler

### Değiştirilen Dosyalar

#### Backend Models & Controllers
1. `models/Redirect.php` - **YENİ** - URL redirect yönetimi
2. `models/Article.php` - HUB metodları eklendi
3. `controllers/CityController.php` - Firma listesi kaldırıldı, HUB article eklendi
4. `controllers/ArticleController.php` - Redirect kontrolü eklendi
5. `controllers/AdminController.php` - Multi-keyword + duplicate check
6. `services/AIService.php` - Çoklu keyword prompt desteği

#### Frontend Views
7. `views/city/show.php` - Firma listesi kaldırıldı, HUB article gösterimi
8. `views/city/district.php` - Çoklu makale yerine TEK makale
9. `views/admin/ai-article-generator.php` - Template/manuel keyword seçenekleri

#### Database
10. `database/create_redirects_table.sql` - **YENİ** - Redirect tablosu
11. `database/migration_cleanup.php` - **YENİ** - Duplicate temizleme scripti
12. `database/add_unique_constraints.sql` - **YENİ** - Unique constraints + triggers

---

## 🚀 Deployment Adımları

### Adım 1: Veritabanı Yedekleme (ÖNEMLİ!)

```bash
# Önce tam yedek alın
mysqldump -u sametsel_lastikci_root -p sametsel_lastikciariyorum > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Adım 2: Redirects Tablosunu Oluştur

```bash
mysql -u sametsel_lastikci_root -p sametsel_lastikciariyorum < database/create_redirects_table.sql
```

**Alternatif (phpMyAdmin ile):**
1. phpMyAdmin'e giriş yapın
2. `database/create_redirects_table.sql` dosyasının içeriğini kopyalayın
3. SQL sekmesine yapıştırıp çalıştırın

**Beklenen Çıktı:**
```
Table 'url_redirects' created successfully
```

### Adım 3: Duplicate Makaleleri Temizle

**⚠️ ÖNEMLİ:** Bu script mevcut duplicate makaleleri temizler ve en iyi makaleyi korur.

```bash
cd c:\xampp\htdocs\lastikciariyorum
php database/migration_cleanup.php
```

**Script Ne Yapar:**
1. Aynı il/ilçeye ait birden fazla makaleyi tespit eder
2. En iyi makaleyi seçer (Kriterler: en uzun içerik → en çok görüntülenen → en yeni)
3. Diğer makaleleri siler
4. Silinen makale URL'lerini `url_redirects` tablosuna ekler (301 redirect)

**Beklenen Çıktı Örneği:**
```
===================================================================
  SEO Migration Cleanup - Duplicate Articles
===================================================================

Adım 1: Duplicate makaleleri tespit et...
  - İl seviyesi duplicate: 3 şehir
  - İlçe seviyesi duplicate: 12 ilçe

Adım 2: En iyi makaleleri seç ve diğerlerini temizle...

  İl: İstanbul (2 makale)
    ✓ Korunan: İstanbul Lastik Servisleri (1250 karakter, 145 görüntülenme)
      → Redirect: /istanbul/eski-makale-slug → /istanbul
      ✗ Silinen: İstanbul'da Lastikçiler

  İlçe: Sultanbeyli, İstanbul (3 makale)
    ✓ Korunan: Sultanbeyli Lastikçi (1800 karakter, 89 görüntülenme)
      → Redirect: /istanbul/sultanbeyli/eski-1 → /istanbul/sultanbeyli
      → Redirect: /istanbul/sultanbeyli/eski-2 → /istanbul/sultanbeyli
      ✗ Silinen: Sultanbeyli 7/24 Lastikçi
      ✗ Silinen: Sultanbeyli Mobil Lastik

===================================================================
  ÖZET
===================================================================

  Toplam Duplicate Grup:  15
  Korunan Makale:         15
  Silinen Makale:         23
  Oluşturulan Redirect:   23

✓ Migration tamamlandı!

Sonraki adım: database/add_unique_constraints.sql dosyasını çalıştırın.
```

### Adım 4: Unique Constraints Ekle

**⚠️ DİKKAT:** Bu script yalnızca Adım 3 tamamlandıktan sonra çalıştırılmalıdır!

```bash
mysql -u sametsel_lastikci_root -p sametsel_lastikciariyorum < database/add_unique_constraints.sql
```

**Alternatif (phpMyAdmin ile):**
1. `database/add_unique_constraints.sql` dosyasının içeriğini kopyalayın
2. phpMyAdmin SQL sekmesinde çalıştırın

**Script Ne Yapar:**
1. İlçe seviyesinde unique constraint ekler: `(city_id, district_id)`
2. İl seviyesinde duplicate önlemek için 2 trigger oluşturur:
   - `prevent_duplicate_city_articles` (INSERT)
   - `prevent_duplicate_city_articles_update` (UPDATE)

**Beklenen Çıktı:**
```
Constraint 'unique_district_article' added
Trigger 'prevent_duplicate_city_articles' created
Trigger 'prevent_duplicate_city_articles_update' created
```

---

## ✅ Test Checklist

Deployment sonrası aşağıdaki testleri yapın:

### 1. Database Testleri

```sql
-- Duplicate kontrol (0 sonuç dönmeli)
SELECT city_id, district_id, COUNT(*) as count
FROM articles
GROUP BY city_id, district_id
HAVING count > 1;

-- Redirects tablosu kontrolü
SELECT COUNT(*) as redirect_count FROM url_redirects;

-- Constraint kontrolü
SHOW CREATE TABLE articles;
```

### 2. Frontend Testleri

#### İl Sayfası Testi
1. Bir il sayfasını açın (örn: `/istanbul`)
2. **Kontrol Et:**
   - ✅ Firma listesi YOK (sidebar kapalı)
   - ✅ İlçe grid listesi görünüyor
   - ✅ HUB article içeriği görünüyor (varsa)
   - ✅ H2 sections görünüyor (district/cities tablosundan)

#### İlçe Sayfası Testi
1. Bir ilçe sayfasını açın (örn: `/istanbul/sultanbeyli`)
2. **Kontrol Et:**
   - ✅ Sidebar'da firmalar görünüyor
   - ✅ TEK HUB article görünüyor (varsa)
   - ✅ Birden fazla makale listesi YOK
   - ✅ H2 sections görünüyor

#### Redirect Testi
1. Eski makale URL'sini açın (migration cleanup'tan)
2. **Beklenen:** 301 redirect ile yeni URL'ye yönlendirme
3. **Kontrol Et:**
   - Developer tools → Network → Status: 301
   - Location header doğru URL'i gösteriyor

### 3. Admin Panel Testleri

#### AI Makale Üretici
1. Admin panel → AI Makale Üret
2. **Kontrol Et:**
   - ✅ "Standart Şablon" seçeneği var
   - ✅ "Manuel Giriş" seçeneği var
   - ✅ Manuel seçilince textarea görünüyor
   - ✅ Kelime sayısı 1500 default

#### Duplicate Önleme
1. Var olan bir ilçe için yeni makale oluşturmayı deneyin
2. **Beklenen:** Hata mesajı:
   ```
   Bu ilçe için zaten bir HUB makalesi var.
   Her ilçe için sadece bir makale oluşturulabilir.
   Mevcut makaleyi düzenlemek isterseniz buraya tıklayın.
   ```

#### Çoklu Keyword AI Üretimi
1. AI Makale Üret → Standart Şablon seç
2. Bir ilçe seç → Makale Üret
3. **Kontrol Et:**
   - ✅ Oluşturulan makalede 5 farklı keyword var:
     - {location} lastikçi
     - {location} 7/24 lastikçi
     - {location} mobil lastikçi
     - {location} açık lastikçi
     - {location} lastik tamiri
   - ✅ Her keyword için ayrı H2 bölümü var
   - ✅ İçerik 1500+ kelime

---

## 🔄 Rollback (Geri Alma) Planı

Bir sorun olursa bu adımları izleyin:

### 1. Veritabanını Geri Yükle

```bash
# Yedek dosyanızı kullanın
mysql -u sametsel_lastikci_root -p sametsel_lastikciariyorum < backup_YYYYMMDD_HHMMSS.sql
```

### 2. Kod Değişikliklerini Geri Al

```bash
# Git üzerinden önceki commit'e dön
git log --oneline  # Commit ID'yi bul
git revert <commit-id>

# VEYA direkt reset (dikkatli kullanın)
git reset --hard <previous-commit-id>
```

### 3. Dosyaları Manuel Geri Yükle

Değiştirilen dosyaları git history'den geri getirin:
- `controllers/CityController.php`
- `controllers/ArticleController.php`
- `views/city/show.php`
- `views/city/district.php`

---

## 📊 Performans ve SEO İyileştirmeleri

### Önceki Durum (Sorunlar)
- ❌ Aynı ilçeye 3-5 makale (keyword cannibalization)
- ❌ İl sayfalarında firma listesi (thin content)
- ❌ Duplicate intent makaleler
- ❌ Google "low value content" uyarıları

### Yeni Durum (Çözümler)
- ✅ Her ilçe için TEK uzun makale (1500-2000 kelime)
- ✅ Çoklu keyword tek makalede (natural SEO)
- ✅ İl sayfaları content HUB (firmalar kaldırıldı)
- ✅ 301 redirects ile SEO değeri korundu
- ✅ Database constraints ile duplicate önlendi

### Beklenen SEO Kazanımlar
1. **Keyword Cannibalization Önlendi:** Aynı keywords için tek authoritative sayfa
2. **Content Depth Arttı:** 400 kelime → 1500-2000 kelime
3. **User Intent Coverage:** Çoklu keywords tek sayfada (7/24, mobil, açık, vs.)
4. **AdSense Uyumluluğu:** Thin/duplicate content riski ortadan kalktı
5. **Internal Linking:** İl → İlçe → Firmalar (net hiyerarşi)

---

## 🎯 Sonraki Adımlar (Opsiyonel)

### 1. Mevcut Makaleleri Güncelle

Eski makaleler kısa olabilir (400-800 kelime). Bunları AI ile genişletin:

```bash
# Admin panel → AI Makale Üret
# Mevcut makale varsa → Update mode
# Standart şablon kullan → 1500 kelime
```

### 2. Google Search Console İzleme

- 2-4 hafta sonra GSC'de performans kontrolü
- Cannibalization raporları (same query, multiple URLs)
- Coverage issues (duplicate content)

### 3. Sayfa Hızı Optimizasyonu

HUB sayfalar daha uzun → lazy loading önemli:
- İlçe grid'de lazy load
- HUB article images lazy load
- CSS critical path optimization

### 4. Schema Markup Ekle

```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{HUB Article Title}",
  "articleBody": "{content}",
  "about": {
    "@type": "Service",
    "serviceType": "Lastik Tamiri"
  },
  "areaServed": {
    "@type": "City",
    "name": "{city_name}"
  }
}
```

---

## 📞 Sorun Giderme

### Sorun 1: Migration cleanup duplicate bulamıyor

**Çözüm:**
```bash
# Manuel kontrol
php -r "
require 'config/database.php';
\$db = Database::getInstance()->getConnection();
\$sql = 'SELECT city_id, district_id, COUNT(*) as cnt FROM articles GROUP BY city_id, district_id HAVING cnt > 1';
\$result = \$db->query(\$sql);
print_r(\$result->fetchAll(PDO::FETCH_ASSOC));
"
```

### Sorun 2: Constraint ekleme hatası

**Hata:** `Duplicate entry for key 'unique_district_article'`

**Çözüm:** Adım 3'ü tekrar çalıştırın (migration cleanup)

### Sorun 3: AI makale üretimi çalışmıyor

**Kontroller:**
1. API key doğru mu? → `/admin/ai-ayarlar`
2. API limit aşıldı mı? → Anthropic console
3. Model adı doğru mu? → `claude-sonnet-4-20250514`

### Sorun 4: Redirect çalışmıyor

**Kontrol:**
```sql
SELECT * FROM url_redirects WHERE old_url = '/istanbul/sultanbeyli/eski-slug';
```

**Çözüm:** Manuel redirect ekle:
```sql
INSERT INTO url_redirects (old_url, new_url, redirect_type)
VALUES ('/eski-url', '/yeni-url', 301);
```

---

## 📝 Değişiklik Logu

### Version 2.0 - SEO HUB Architecture (2025-12-31)

#### Added
- ✅ Redirect model ve database table
- ✅ Multi-keyword AI article generation (template + manual)
- ✅ HUB article methods (getCityHubArticle, getDistrictHubArticle)
- ✅ Duplicate prevention (constraints + triggers)
- ✅ Migration cleanup script

#### Changed
- ✅ City pages: Companies removed, HUB article added
- ✅ District pages: Multiple articles → Single HUB article
- ✅ AI Service: Single keyword → Multi-keyword support
- ✅ AdminController: Duplicate check before article creation
- ✅ ArticleController: 301 redirect logic for old URLs

#### Removed
- ✅ Company listings from city pages
- ✅ Multiple article support per district

---

## ✨ Başarı Kriterleri

Deployment başarılıysa:

1. ✅ Her il için SADECE 1 sayfa (content HUB, firma yok)
2. ✅ Her ilçe için SADECE 1 HUB sayfası (uzun makale + firmalar)
3. ✅ Eski makale URL'leri → 301 redirect
4. ✅ AI ile çoklu keyword makale üretimi çalışıyor
5. ✅ Database duplicate önleme aktif
6. ✅ Hiçbir duplicate content yok
7. ✅ AdSense uyumlu (thin content yok)

---

**Deployment Tarihi:** 2025-12-31
**Versiyon:** 2.0.0 - HUB Architecture
**Hazırlayan:** Claude Code (Anthropic)

**ÖNEMLI NOT:** Bu deployment production veritabanını değiştirir. Mutlaka yedek alın!
