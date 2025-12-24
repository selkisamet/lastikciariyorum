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
        // HUB SEO: Use getBySlugWithH2 to decode h2_sections
        $city = $this->cityModel->getBySlugWithH2($citySlug);

        if (!$city) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $districts = $this->districtModel->getByCity($city['id']);
        $articles = $this->articleModel->getByCity($city['id']);
        $companies = $this->companyModel->getByCity($city['id']);

        // HUB SEO: Separate H1, meta title, meta description, and page description
        $h1 = !empty($city['h1'])
            ? $city['h1']
            : $city['name'] . ' Lastik Tamircileri';

        $pageTitle = !empty($city['meta_title'])
            ? $city['meta_title']
            : $h1;

        $metaDescription = !empty($city['meta_description'])
            ? $city['meta_description']
            : $city['name'] . ' ilinde güvenilir lastik tamir hizmetleri.';

        $pageDescription = !empty($city['page_description'])
            ? $city['page_description']
            : $metaDescription;

        $this->view('city.show', [
            'city' => $city,
            'districts' => $districts,
            'articles' => $articles,
            'companies' => $companies,
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

        // HUB SEO: Use getBySlugWithH2 to decode h2_sections
        $district = $this->districtModel->getBySlugWithH2($city['id'], $districtSlug);

        if (!$district) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $articles = $this->articleModel->getByDistrict($district['id']);
        $companies = $this->companyModel->getByDistrict($district['id']);

        // HUB SEO: Separate H1, meta title, meta description, and page description
        $h1 = !empty($district['h1'])
            ? $district['h1']
            : $district['name'] . ' Lastik Tamircileri';

        $pageTitle = !empty($district['meta_title'])
            ? $district['meta_title']
            : $h1;

        $metaDescription = !empty($district['meta_description'])
            ? $district['meta_description']
            : $city['name'] . ' ili ' . $district['name'] . ' ilçesinde güvenilir lastik tamir hizmetleri.';

        $pageDescription = !empty($district['page_description'])
            ? $district['page_description']
            : $metaDescription;

        $this->view('city.district', [
            'city' => $city,
            'district' => $district,
            'articles' => $articles,
            'companies' => $companies,
            'h1' => $h1,
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'pageDescription' => $pageDescription,
        ]);
    }
}
