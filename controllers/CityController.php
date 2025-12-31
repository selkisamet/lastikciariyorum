<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Company.php';

class CityController extends Controller
{
    private $cityModel;
    private $districtModel;
    private $articleModel;
    private $companyModel;

    public function __construct()
    {
        $this->cityModel = new City();
        $this->districtModel = new District();
        $this->articleModel = new Article();
        $this->companyModel = new Company();
    }

    public function show($citySlug)
    {
        // Simple city lookup (no H2 sections - removed)
        $city = $this->cityModel->findBySlug($citySlug);

        if (!$city) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $districts = $this->districtModel->getByCity($city['id']);

        // HUB Architecture: Get single HUB article for city (no companies on city page)
        $hubArticle = $this->articleModel->getCityHubArticle($city['id']);
        $companies = []; // Empty: Companies removed from city pages per SEO plan

        // ALL meta from article (or fallback if no article)
        if ($hubArticle) {
            $h1 = $hubArticle['title'];
            $pageTitle = $hubArticle['meta_title'] ?? $hubArticle['title'];
            $metaDescription = $hubArticle['meta_description'] ?? $hubArticle['excerpt'];
            $pageDescription = $hubArticle['page_description'] ?? $hubArticle['excerpt'];
        } else {
            // Fallback if no article exists
            $h1 = $city['name'] . ' Lastik Tamircileri';
            $pageTitle = $h1;
            $metaDescription = $city['name'] . ' ilinde güvenilir lastik tamir hizmetleri.';
            $pageDescription = $metaDescription;
        }

        $this->view('city.show', [
            'city' => $city,
            'districts' => $districts,
            'hubArticle' => $hubArticle, // Single HUB article for city
            'companies' => $companies, // Empty array
            'h1' => $h1,
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'pageDescription' => $pageDescription,
        ]);
    }

    public function showDistrict($citySlug, $districtSlug)
    {
        $city = $this->cityModel->findBySlug($citySlug);

        if (!$city) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Simple district lookup (no H2 sections - removed)
        $district = $this->districtModel->findBySlug($city['id'], $districtSlug);

        if (!$district) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        // HUB Architecture: Get single HUB article for district
        $hubArticle = $this->articleModel->getDistrictHubArticle($district['id']);
        $companies = $this->companyModel->getByDistrict($district['id']); // Companies remain on district pages

        // ALL meta from article (or fallback if no article)
        if ($hubArticle) {
            $h1 = $hubArticle['title'];
            $pageTitle = $hubArticle['meta_title'] ?? $hubArticle['title'];
            $metaDescription = $hubArticle['meta_description'] ?? $hubArticle['excerpt'];
            $pageDescription = $hubArticle['page_description'] ?? $hubArticle['excerpt'];
        } else {
            // Fallback if no article exists
            $h1 = $district['name'] . ' Lastik Tamircileri';
            $pageTitle = $h1;
            $metaDescription = $city['name'] . ' ili ' . $district['name'] . ' ilçesinde güvenilir lastik tamir hizmetleri.';
            $pageDescription = $metaDescription;
        }

        $this->view('city.district', [
            'city' => $city,
            'district' => $district,
            'hubArticle' => $hubArticle, // Single HUB article for district
            'companies' => $companies,
            'h1' => $h1,
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'pageDescription' => $pageDescription,
        ]);
    }
}
