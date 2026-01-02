<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Company.php';

class HomeController extends Controller
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

    public function index()
    {
        $cities = $this->cityModel->getAllWithCounts();
        $districts = $this->districtModel->getAllWithCounts();
        $latestArticles = $this->articleModel->getLatest(6);

        $this->view('home.index', [
            'cities' => $cities,
            'districts' => $districts,
            'latestArticles' => $latestArticles,
            'pageTitle' => 'Ana Sayfa - Lastikciariyorum.com',
            'metaDescription' => 'Türkiye\'nin her ilinde güvenilir lastik tamircileri ve tamir servisleri. 7/24 hizmet veren lastik tamirhaneleri.',
        ]);
    }

    public function search()
    {
        header('Content-Type: application/json; charset=utf-8');

        $searchTerm = $_GET['q'] ?? '';

        // DEBUG MODE: Return detailed information
        if (isset($_GET['debug'])) {
            echo json_encode([
                'debug' => [
                    'raw_term' => $searchTerm,
                    'hex' => bin2hex($searchTerm),
                    'strlen' => strlen($searchTerm),
                    'mb_strlen' => mb_strlen($searchTerm, 'UTF-8'),
                    'encoding' => mb_detect_encoding($searchTerm, ['UTF-8', 'ISO-8859-9', 'Windows-1254'], true),
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        if (mb_strlen($searchTerm, 'UTF-8') < 2) {
            echo json_encode(['results' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $results = [];

        // İllerde ara
        $cities = $this->cityModel->search($searchTerm);
        foreach ($cities as $city) {
            $results[] = [
                'type' => 'city',
                'name' => $city['name'],
                'url' => url($city['slug']),
                'company_count' => $city['company_count']
            ];
        }

        // İlçelerde ara
        $districts = $this->districtModel->search($searchTerm);
        foreach ($districts as $district) {
            $results[] = [
                'type' => 'district',
                'name' => $district['name'],
                'city_name' => $district['city_name'],
                'url' => url($district['city_slug'] . '/' . $district['slug']),
                'company_count' => $district['company_count']
            ];
        }

        echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
    }
}
