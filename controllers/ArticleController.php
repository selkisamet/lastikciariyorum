<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/ViewLog.php';

class ArticleController extends Controller
{
    private $cityModel;
    private $districtModel;
    private $articleModel;
    private $companyModel;
    private $viewLogModel;

    public function __construct()
    {
        $this->cityModel = new City();
        $this->districtModel = new District();
        $this->articleModel = new Article();
        $this->companyModel = new Company();
        $this->viewLogModel = new ViewLog();
    }

    public function show($citySlug, $districtSlug, $articleSlug)
    {
        $city = $this->cityModel->findBySlug($citySlug);

        if (!$city) {
            http_response_code(404);
            echo "İl bulunamadı";
            return;
        }

        $district = null;
        $districtId = null;

        if ($districtSlug) {
            $district = $this->districtModel->findBySlug($city['id'], $districtSlug);

            if (!$district) {
                http_response_code(404);
                echo "İlçe bulunamadı";
                return;
            }

            $districtId = $district['id'];
        }

        $article = $this->articleModel->findBySlug($articleSlug, $city['id'], $districtId);

        if (!$article) {
            http_response_code(404);
            echo "Makale bulunamadı";
            return;
        }

        // IP bazlı güvenli görüntülenme sayacı
        $ipAddress = ViewLog::getClientIp();
        $userAgent = ViewLog::getUserAgent();

        // Son 24 saat içinde aynı IP'den görüntülenme yoksa say
        if (!$this->viewLogModel->hasRecentView('article', $article['id'], $ipAddress, 24)) {
            // Görüntülenme kaydını oluştur
            $this->viewLogModel->logView('article', $article['id'], $ipAddress, $userAgent);
            // Sayacı artır
            $this->articleModel->incrementViewCount($article['id']);
        }

        // Get companies for sidebar
        if ($districtId) {
            $companies = $this->companyModel->getByDistrict($districtId, 10);
        } else {
            $companies = $this->companyModel->getByCity($city['id'], 10);
        }

        $this->view('article.show', [
            'city' => $city,
            'district' => $district,
            'article' => $article,
            'companies' => $companies,
            'pageTitle' => !empty($article['meta_title']) ? $article['meta_title'] : $article['title'],
            'metaDescription' => !empty($article['meta_description']) ? $article['meta_description'] : $article['excerpt'],
        ]);
    }
}
