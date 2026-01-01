<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/ViewLog.php';
require_once __DIR__ . '/../models/Redirect.php';

class ArticleController extends Controller
{
    private $cityModel;
    private $districtModel;
    private $articleModel;
    private $companyModel;
    private $viewLogModel;
    private $redirectModel;

    public function __construct()
    {
        $this->cityModel = new City();
        $this->districtModel = new District();
        $this->articleModel = new Article();
        $this->companyModel = new Company();
        $this->viewLogModel = new ViewLog();
        $this->redirectModel = new Redirect();
    }

    public function show($citySlug, $districtSlug, $articleSlug)
    {
        // HUB Architecture: Check for redirect first
        // Old article URLs should redirect to HUB pages (301 permanent)
        try {
            if ($districtSlug) {
                $oldUrl = "/{$citySlug}/{$districtSlug}/{$articleSlug}";
            } else {
                $oldUrl = "/{$citySlug}/{$articleSlug}";
            }

            $redirect = $this->redirectModel->findRedirect($oldUrl);

            if ($redirect) {
                // 301 Permanent Redirect to preserve SEO value
                header("Location: {$redirect['new_url']}", true, $redirect['redirect_type'] ?? 301);
                exit;
            }
        } catch (PDOException $e) {
            // Redirect table doesn't exist yet - skip redirect check
            // This is expected if create_redirects_table.sql hasn't been run yet
        }

        // No redirect found - check if article exists
        $city = $this->cityModel->findBySlug($citySlug);

        if (!$city) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $district = null;
        $districtId = null;

        if ($districtSlug) {
            $district = $this->districtModel->findBySlug($city['id'], $districtSlug);

            if (!$district) {
                http_response_code(404);
                require_once __DIR__ . '/../views/errors/404.php';
                return;
            }

            $districtId = $district['id'];
        }

        $article = $this->articleModel->findBySlug($articleSlug, $city['id'], $districtId);

        if (!$article) {
            // Article not found and no redirect - 404
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
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
