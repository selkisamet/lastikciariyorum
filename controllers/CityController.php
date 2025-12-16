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
        $city = $this->cityModel->findBySlug($citySlug);

        if (!$city) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $districts = $this->districtModel->getByCity($city['id']);
        $articles = $this->articleModel->getByCity($city['id']);
        $companies = $this->companyModel->getByCity($city['id']);

        $this->view('city.show', [
            'city' => $city,
            'districts' => $districts,
            'articles' => $articles,
            'companies' => $companies,
            'pageTitle' => $city['meta_title'] ?? $city['name'] . ' Lastik Tamircileri',
            'metaDescription' => $city['meta_description'] ?? $city['name'] . ' ilinde güvenilir lastik tamir hizmetleri.',
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

        $district = $this->districtModel->findBySlug($city['id'], $districtSlug);

        if (!$district) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $articles = $this->articleModel->getByDistrict($district['id']);
        $companies = $this->companyModel->getByDistrict($district['id']);

        $this->view('city.district', [
            'city' => $city,
            'district' => $district,
            'articles' => $articles,
            'companies' => $companies,
            'pageTitle' => $district['meta_title'] ?? $district['name'] . ' Lastik Tamircileri',
            'metaDescription' => $district['meta_description'] ?? $district['name'] . ' ilçesinde güvenilir lastik tamir hizmetleri.',
        ]);
    }
}
